<?php

namespace App\Helper;

use App\Entity\Manga;
use App\Entity\Chapter;
use App\Entity\LanguageCode;
use SebastianBergmann\Diff\Chunk;
use App\Repository\MangaRepository;
use App\Repository\ChapterRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\LanguageCodeRepository;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MangaMangadexApiHelper
{

  private $params;
  private $manager;

  private $output;
  private $session;
  private $translator;

  private $twitter;
  private $sendTwitter;

  private $manga;

  /**
   * Undocumented function
   *
   * @param ParameterBagInterface $params
   * @param EntityManagerInterface $manager
   * @param OutputInterface $output
   * @param Session $session
   * @param TranslatorInterface $translator
   * @param boolean $add
   */
  function __construct(ParameterBagInterface $params, EntityManagerInterface $manager, OutputInterface $output = null, Session $session = null, TranslatorInterface $translator = null, bool $add = false)
  {
    $this->params = $params;
    $this->manager = $manager;

    $this->output = $output;
    $this->session = $session;
    $this->translator = $translator;

    /**
     * @var MangaRepository $mangaRepo
     */
    $this->mangaRepo = $this->manager->getRepository(Manga::class);

    /**
     * @var LanguageCodeRepository $langCodeRepo
     */
    $this->langCodeRepo = $this->manager->getRepository(LanguageCode::class);

    /**
     * @var ChapterRepository $chapterRepo
     */
    $this->chapterRepo = $this->manager->getRepository(Chapter::class);

    $this->twitter = new TwitterHelper($params);
    $this->sendTwitter = $this->twitter->getTwitterEnable();

    $this->add = $add;
  }

  public function refreshMangaById(string $mangaId)
  {
    $langCodeAllow = $this->getLanguageAllow();

    $client = HttpClient::create(['http_version' => '2.0']);

    $new_upload = $this->manageMangaFromApi($client, $mangaId);

    if ($new_upload === true) {
      $this->manageChapterFromApi($client, $mangaId, $langCodeAllow);

      $this->writeOutput('<info> OK </info>');
    } elseif ($new_upload === false) {
      $this->writeOutput('<info> SKIP </info>');
    }
  }


  /**
   * Undocumented function
   *
   * @param HttpClientInterface $client
   * @param string $mangaId
   * @return boolean|null
   */
  private function manageMangaFromApi(HttpClientInterface $client, string $mangaId): ?bool
  {
    $response = $client->request('GET', $this->getMangadexUrl() . '/api/v2/manga/' . $mangaId);

    if ($response->getStatusCode() != 200) {
      $this->writeOutput("<error>API can't be reach for this manga</error>");

      return null;
    }

    $response_json = json_decode($response->getContent());
    $manga = $response_json->data;

    $mangaInDB = $this->mangaRepo->findOneBy(array(
      'mangaId' => $mangaId,
    ));


    if (!isset($mangaInDB)) {
      return $this->createMangaFronJson($manga, $mangaId);
    } else {
      return $this->updateMangaInDb($mangaInDB, $mangaId, $manga);
    }
  }

  /**
   * Undocumented function
   *
   * @param object $mangaJSON
   * @param string $mangaId
   * @return boolean
   */
  private function createMangaFronJson(object $mangaJSON, string $mangaId): bool
  {
    $this->manga = new Manga();
    $this->manga->setName(html_entity_decode($mangaJSON->title, ENT_QUOTES, 'UTF-8'));

    $this->manga->setImage($mangaJSON->mainCover);
    $this->manga->setMangaId($mangaId);
    $this->manga->setTwitter(TRUE);
    $this->manga->setLastUploaded(new \DateTime(date('Y-m-d H:i:s', $mangaJSON->lastUploaded)));

    $newUpload = true;

    $this->manager->persist($this->manga);
    $this->manager->flush();

    $string = '"' . $this->manga->getName() . '"' . ' a été ajouté !' . PHP_EOL;
    $string .= 'Disponible ici ' .  $this->getMangadexUrl() . '/manga/' . $mangaId;

    if ($this->sendTwitter && ($this->add)) {
      $result = $this->twitter->sendTweet($string);
    }

    $this->addFlash('success', 'successfully.added', ['%slug%' => ucfirst($this->manga->getName())]);

    return $newUpload;
  }

  /**
   * Undocumented function
   *
   * @param Manga $manga
   * @param string $mangaId
   * @return boolean
   */
  private function updateMangaInDb(Manga $manga, string $mangaId, object $mangaJSON): bool
  {
    $newUpload = false;

    if ($manga->getMangaId() != $mangaId) {
      $manga->setMangaId($mangaId);
    }

    if ($manga->getImage() != $mangaJSON->mainCover) {
      $manga->setImage($mangaJSON->mainCover);
    }

    if (($manga->getLastUploaded())->getTimestamp() < $mangaJSON->lastUploaded) {
      $manga->setLastUploaded(new \DateTime(date('Y-m-d H:i:s', $mangaJSON->lastUploaded)));
      $newUpload = true;
    }

    $this->manager->persist($manga);
    $this->manager->flush();

    $this->manga = $manga;

    $this->addFlash('warning', 'successfully.modified', ['%slug%' => ucfirst($this->manga->getName())]);

    return $newUpload;
  }

  /**
   * Undocumented function
   *
   * @param HttpClientInterface $client
   * @param string $mangaId
   * @param array $langCodeAllow
   * @return void
   */
  private function manageChapterFromApi(HttpClientInterface $client, string $mangaId, array $langCodeAllow)
  {
    $response = $client->request('GET', $this->getMangadexUrl() . '/api/v2/manga/' . $mangaId . '/chapters');
    if ($response->getStatusCode() != 200) {
      $this->writeOutput("<error>" . $response->getStatusCode() . ' - ' . $response->getContent() . "</error>");
    }

    $response_json = json_decode($response->getContent());
    $chapters = $response_json->data->chapters;

    foreach ($chapters as $key => $chapter_json) {

      if (isset($chapter_json->chapter) && $chapter_json->chapter !== '' && array_key_exists($chapter_json->language, $langCodeAllow)) {
        $langCode = $chapter_json->language;
        $langCodeInDB = $langCodeAllow[$langCode];

        $number = $chapter_json->chapter;
        $timestamp = $chapter_json->timestamp;

        $chapterInDB = $this->chapterRepo->findOneBy(array(
          'langCode' => $langCodeInDB,
          'manga'    => $this->manga,
          'number'   => $number
        ));

        if (isset($chapterInDB)) {
          $this->updateChapterFromApi($chapterInDB, $chapter_json->id, $timestamp);
        } else {
          $this->createChapterFromApi($langCodeInDB, $chapter_json, $number, $timestamp);
        }
      }
    }
  }

  /**
   * Undocumented function
   *
   * @param LanguageCode $langCode
   * @param object $chapter_json
   * @param string $number
   * @param string $timestamp
   * @return void
   */
  private function createChapterFromApi(LanguageCode $langCode, object $chapter_json, string $number, string $timestamp)
  {

    $chapter = new Chapter();

    $chapter->setLangCode($langCode)
      ->setManga($this->manga)
      ->setChapterId($chapter_json->id)
      ->setNumber($number)
      ->setDate(new \DateTime(date('Y-m-d H:i:s', $timestamp)));

    $this->manager->persist($chapter);
    $this->manager->flush();

    $this->writeOutput($this->manga->getName() . ' - Langue : ' . $langCode->getLibelle() . ' - Chapitre n°' . $chapter->getNumber() . ' ajouté !');

    $string = $langCode->getTwitterFlag() .' ' . $this->manga->getName() . ' - Chapitre n°' . $chapter->getNumber() . ' sortie !' . PHP_EOL;
    $string .= 'Disponible ici ' . $this->getMangadexUrl() . '/chapter/' . $chapter_json->id;

    if ($this->manga->getTwitter() && $this->sendTwitter && !($this->add)) {
      $result = $this->twitter->sendTweet($string);
    }

    if (!empty($this->manga->getFollowMangas()) && $this->sendTwitter) {
      foreach ($this->manga->getFollowMangas() as $follow) {

        $follower = $follow->getUtilisateur();
        if ($follower->getNameTwitter() !== null) {
          $this->twitter->sendDirectMessageWithScreenName($follower->getNameTwitter(), $string);
        }
      }
    }
  }

  /**
   * Undocumented function
   *
   * @param Chapter $chapterInDB
   * @param string $chapterId
   * @param string $timestamp
   * @return void
   */
  private function updateChapterFromApi(Chapter $chapterInDB, string $chapterId, string $timestamp)
  {
    if ($chapterInDB->getDate()->getTimestamp() < $timestamp) {
      $chapterInDB->setChapterId($chapterId)
        ->setDate(new \DateTime(date('Y-m-d H:i:s', $timestamp)));

      $this->manager->persist($chapterInDB);
      $this->manager->flush();
    }
  }

  /**
   * Undocumented function
   *
   * @param string $msg
   * @return void
   */
  private function writeOutput(string $msg)
  {
    if (!is_null($this->output)) {
      $this->output->writeln($msg);
    }
  }

  /**
   * Undocumented function
   *
   * @param string $status
   * @param string $msg
   * @param array $params
   * @return void
   */
  function addFlash(string $status, string $msg, array $params)
  {
    if (!is_null($this->session)) {
      $this->session->getFlashBag()->add($status, $this->translator->trans($msg, $params));
    }
  }

  /**
   * Undocumented function
   *
   * @return LanguageCode[]
   */
  private function getLanguageAllow(): array
  {
    return $this->langCodeRepo->findAllLangCodeArray();
  }

  /**
   * Undocumented function
   *
   * @return string
   */
  private function getMangadexUrl(): string
  {
    return $this->params->get('mangadex_url');
  }
}
