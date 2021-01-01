<?php

namespace App\Command;

use App\Entity\Manga;
use App\Entity\Chapter;
use App\Entity\LanguageCode;
use App\Helper\TwitterHelper;
use App\Repository\MangaRepository;
use App\Repository\ChapterRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\LanguageCodeRepository;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class RefreshInfosMangaCommand extends Command
{
  protected static $defaultName = 'app:refresh-infos-manga';

  protected $manager;
  protected $mangaRepo;

  public function __construct(EntityManagerInterface $manager, MangaRepository $mangaRepo, ParameterBagInterface $params)
  {
    $this->manager = $manager;
    $this->mangaRepo = $mangaRepo;
    $this->params = $params;
    $this->twitter = new TwitterHelper($params);

    parent::__construct();
  }

  protected function configure()
  {
    $this
      ->setDescription('Met à jour les informations des mangas');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $output->writeln('<comment>~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~</comment>');
    $output->writeln('<info>' . (new \DateTime())->format('Y-m-d H:i:s') . '</info>');
    $output->writeln('<comment>=======================================</comment>');
    $output->writeln('<comment>Récupération de l\'ensemble des mangas.</comment>');

    $mangas = $this->mangaRepo->findAll();
    $countMangas = count($mangas);
    $output->writeln('<comment>=======================================</comment>');
    $output->writeln('<comment>Mise à jour de l\'ensemble des informations des mangas.</comment>');
    $output->writeln('<comment>=======================================</comment>');

    $count = 1;
    $start = microtime(true);
    foreach ($mangas as $manga) {
      $output->writeln('<comment> -- ' . $manga->getName() . ' --' . ' (' . $count . '/' . $countMangas . ') </comment>');
      $this->refreshInfos($manga->getMangaId(), $this->manager, $output);
      $count++;
      sleep(2);
    }

    $io->success('La liste des mangas a bien été mise à jour !');
    $io->note("Temps d'exécution : " . round((microtime(true) - $start) / 60, 2) . " minutes");
    return 0;
  }

  private function refreshInfos(string $mangaId, EntityManagerInterface $manager, OutputInterface $output)
  {
    /**
     * @var MangaRepository $mangaRepo
     */
    $mangaRepo = $manager->getRepository(Manga::class);

    /**
     * @var LanguageCodeRepository $langCodeRepo
     */
    $langCodeRepo = $manager->getRepository(LanguageCode::class);

    /**
     * @var ChapterRepository $chapterRepo
     */
    $chapterRepo = $manager->getRepository(Chapter::class);

    $mangadexURL =  $this->params->get('mangadex_url');
    $langCodeAllow = $langCodeRepo->findAllLangCodeArray();

    $client = HttpClient::create(['http_version' => '2.0']);
    $response = $client->request('GET', $mangadexURL . '/api/v2/manga/' . $mangaId);

    if ($response->getStatusCode() != 200) {
      $output->writeln("<error>API can't be reach for this manga</error>");

      return;
    }

    $response_json = json_decode($response->getContent());
    $manga = $response_json->data;

    $mangaDB = $mangaRepo->findOneBy(array(
      'mangaId' => $mangaId,
    ));

    $newUpload = false;

    if (!isset($mangaDB)) {
      $mangaDB = new Manga();
      $mangaDB->setName(html_entity_decode($manga->title, ENT_QUOTES, 'UTF-8'));

      $mangaDB->setMangaId($mangaId);

      $mangaDB->setLastUploaded(new \DateTime(date('Y-m-d H:i:s', $manga->lastUploaded)));
      $newUpload = true;

      $manager->persist($mangaDB);
      $manager->flush();
    } else {
      if ($mangaDB->getMangaId() != $mangaId) {
        $mangaDB->setMangaId($mangaId);
      }

      if ($mangaDB->getImage() != $manga->mainCover) {
        $mangaDB->setImage($manga->mainCover);
      }

      if (($mangaDB->getLastUploaded())->getTimestamp() < $manga->lastUploaded) {
        $mangaDB->setLastUploaded(new \DateTime(date('Y-m-d H:i:s', $manga->lastUploaded)));
        $newUpload = true;
      }

      $manager->persist($mangaDB);
      $manager->flush();
    }

    if ($newUpload) {
      $response = $client->request('GET', $mangadexURL . '/api/v2/manga/' . $mangaId . '/chapters');
      if ($response->getStatusCode() != 200) {
        $output->writeln("<error>" . $response->getStatusCode() . ' - ' . $response->getContent() . "</error>");
      }

      $response_json = json_decode($response->getContent());
      $chapters = $response_json->data->chapters;


      foreach ($chapters as $key => $chapter_json) {

        if (isset($chapter_json->chapter) && $chapter_json->chapter !== '' && array_key_exists($chapter_json->language, $langCodeAllow)) {
          $langCode = $chapter_json->language;
          $langCodeDB = $langCodeAllow[$langCode];

          $number = $chapter_json->chapter;
          $timestamp = $chapter_json->timestamp;

          $chapterDB = $chapterRepo->findOneBy(array(
            'langCode' => $langCodeDB,
            'manga' => $mangaDB,
            'number' => $number
          ));

          if (isset($chapterDB)) {
            if ($chapterDB->getDate()->getTimestamp() < $timestamp) {
              $chapterDB->setChapterId($chapter_json->id)
                ->setDate(new \DateTime(date('Y-m-d H:i:s', $timestamp)));

              $manager->persist($chapterDB);
              $manager->flush();
            }
          } else {

            $chapter = new Chapter();

            $chapter->setLangCode($langCodeDB)
              ->setManga($mangaDB)
              ->setChapterId($chapter_json->id)
              ->setNumber($number)
              ->setDate(new \DateTime(date('Y-m-d H:i:s', $timestamp)));

            $manager->persist($chapter);
            $manager->flush();

            $output->writeln($mangaDB->getName() . ' - Langue : ' . $langCodeDB->getLibelle() . ' - Chapitre n°' . $chapter->getNumber() . ' ajouté !');

            $string = $mangaDB->getName() . ' (' . $langCodeDB->getLibelle() . ') - Chapitre n°' . $chapter->getNumber() . ' sortie !' . PHP_EOL;
            $string .= 'Disponible ici ' . $mangadexURL . '/chapter/' . $chapter_json->id;

            if ($mangaDB->getTwitter()) {
              $result = $this->twitter->sendTweet($string);
            }

            if (!empty($mangaDB->getFollowMangas())) {
              foreach ($mangaDB->getFollowMangas() as $follow) {
                $follower = $follow->getUtilisateur();
                if ($follower->getNameTwitter() !== null) {
                  $this->twitter->sendDirectMessageWithScreenName($follower->getNameTwitter(), $string);
                }
              }
            }
          }
        }
      }
      $output->writeln('<info> OK </info>');
    } else {
      $output->writeln('<info> SKIP </info>');
    }
  }
}
