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

    $output->writeln('<comment>=======================================</comment>');
    $output->writeln('<comment>Mise à jour de l\'ensemble des informations des mangas.</comment>');
    $output->writeln('<comment>=======================================</comment>');

    foreach ($mangas as $manga) {
      $output->writeln('<comment> -- ' . $manga->getName() . ' -- </comment>');
      $this->refreshInfos($manga->getMangaId(), $this->manager, $output);
      $output->writeln('<info> OK </info>');
    }

    $io->success('La liste des mangas a bien été mise à jour !');

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
    $response = $client->request('GET', $mangadexURL . '/api/manga/' . $mangaId);

    if ($response->getStatusCode() != 200) {
      $output->writeln("<error>API can't be reach for this manga</error>");

      return;
    }

    $data = json_decode($response->getContent());

    $manga = $data->manga;
    $chapters = $data->chapter;

    $mangaDB = $mangaRepo->findOneBy(array(
      'mangaId' => $mangaId,
    ));

    if (!isset($mangaDB)) {
      $mangaDB = new Manga();
      $mangaDB->setName(html_entity_decode($manga->title, ENT_QUOTES, 'UTF-8'));

      $mangaDB->setMangaId($mangaId);

      $manager->persist($mangaDB);
      $manager->flush();
    } else {
      if ($mangaDB->getMangaId() != $mangaId) {
        $mangaDB->setMangaId($mangaId);
      }

      $manager->persist($mangaDB);
      $manager->flush();
    }

    foreach ($chapters as $chapter_id => $values) {

      if (isset($values->chapter) && $values->chapter !== '' && array_key_exists($values->lang_code, $langCodeAllow)) {
        $langCode = $values->lang_code;
        $langCodeDB = $langCodeAllow[$langCode];

        $number = $values->chapter;
        $timestamp = $values->timestamp;

        $chapterDB = $chapterRepo->findOneBy(array(
          'langCode' => $langCodeDB,
          'manga' => $mangaDB,
          'number' => $number
        ));

        if (isset($chapterDB)) {
          if ($chapterDB->getDate()->getTimestamp() < $timestamp) {
            $chapterDB->setChapterId($chapter_id)
              ->setDate(new \DateTime(date('Y-m-d H:i:s', $timestamp)));

            $manager->persist($chapterDB);
            $manager->flush();
          }
        } else {
          $chapter = new Chapter();

          $chapter->setLangCode($langCodeDB)
            ->setManga($mangaDB)
            ->setChapterId($chapter_id)
            ->setNumber($number)
            ->setDate(new \DateTime(date('Y-m-d H:i:s', $timestamp)));

          $manager->persist($chapter);
          $manager->flush();

          $output->writeln($mangaDB->getName() . ' - Langue : ' . $langCodeDB->getLibelle() . ' - Chapitre n°' . $chapter->getNumber() . ' ajouté !');

          $string = $mangaDB->getName() . ' (' . $langCodeDB->getLibelle() . ') - Chapitre n°' . $chapter->getNumber() . ' sortie !' . PHP_EOL;
          $string .= 'Disponible ici ' . $mangadexURL . '/chapter/' . $chapter_id;

          if ($mangaDB->getTwitter()) {
            $result = $this->twitter->sendTweet($string);
          }

          if (!empty($mangaDB->getFollowMangas())) {
            foreach($mangaDB->getFollowMangas() as $follow) {
              $follower = $follow->getUtilisateur();
              if($follower->getNameTwitter() !== null) {
                $this->twitter->sendDirectMessageWithScreenName($follower->getNameTwitter(), $string);
              }
            }
          }
        }
      }
    }

  }
}
