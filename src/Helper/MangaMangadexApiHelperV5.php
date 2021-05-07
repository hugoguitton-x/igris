<?php

namespace App\Helper;

use App\Entity\Manga;
use App\Entity\Chapter;
use App\Entity\LanguageCode;
use App\Repository\MangaRepository;
use App\Repository\ChapterRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\LanguageCodeRepository;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MangaMangadexApiHelperV5
{

  private $params;
  private $manager;

  private $twitter;
  private $sendTwitter;

  private $manga;

  private $authToken;

  private $client;
  /**
   * Undocumented function
   *
   * @param ParameterBagInterface $params
   * @param EntityManagerInterface $manager
   */
  function __construct(ParameterBagInterface $params, EntityManagerInterface $manager)
  {
    $this->params = $params;
    $this->manager = $manager;

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

    $this->client = HttpClient::create(['http_version' => '2.0']);

    //$this->authLogin();
  }

  /**
   * Undocumented function
   *
   * @return void
   */
  public function authLogin()
  {
    $response = $this->client->request('POST', $this->getEndPoint("auth_login"), ["json" => ["username" => "tatsukiaisu", "password" => "d5af65d70c69daad40b212c2a7592998"]]);

    if ($response->getStatusCode() != 200) {
      // TODO
    }
    $response_json = json_decode($response->getContent());

    $this->authToken = $response_json->token;
  }

  /**
   * Undocumented function
   *
   * @return void
   */
  public function authCheck()
  {
    $response = $this->client->request('GET', $this->getEndPoint("check_token"), ["auth_bearer" => $this->authToken->session]);

    if ($response->getStatusCode() != 200) {
      // TODO
    }
    $response_json = json_decode($response->getContent());
    if (!$response_json->isAuthenticated) {
      $this->authRefresh();
    }
  }

  /**
   * Undocumented function
   *
   * @return void
   */
  public function authRefresh()
  {
    $response = $this->client->request('POST', $this->getEndPoint("refresh_token"), ["json" => ["token" => $this->authToken->refresh]]);

    if ($response->getStatusCode() == 200) {
      $response_json = json_decode($response->getContent());
      $this->authToken = $response_json->token;
    } else if ($response->getStatusCode() == 401) {
      $this->authLogin();
    }
  }

  public function refreshMangaById(string $mangaId): string
  {
    $langCodeAllow = $this->getLanguageAllow();

    $result = $this->viewManga($mangaId);

    if ($result === "updated" || $result === "created" || $result === "not_updated") {
      $this->refreshChapterByMangaId($mangaId, $langCodeAllow);
    }

    return $result;
  }

  public function refreshChapterByMangaId(string $mangaId, array $langCodeAllow, int $offset = 0)
  {
    $response = $this->client->request('GET', $this->getEndPoint("chapter"), ["query" => ["manga" => $mangaId, "limit" => 100, "offset" => $offset, "order[publishAt]" => "desc"]]);
    if ($response->getStatusCode() == 200) {

      $response_json = json_decode($response->getContent());
      $results_json = $response_json->results;

      foreach ($results_json as $key => $chapter_result) {
        $chapterJson = $chapter_result->data;

        if (isset($chapterJson->attributes->chapter) && $chapterJson->attributes->chapter !== '' && array_key_exists($chapterJson->attributes->translatedLanguage, $langCodeAllow)) {
          $langCode = $chapterJson->attributes->translatedLanguage;
          $langCodeDatabase = $langCodeAllow[$langCode];

          $chapterDatabase = $this->chapterRepo->findOneBy(array(
            'langCode' => $langCodeDatabase,
            'manga'    => $this->manga,
            'number'   => $chapterJson->attributes->chapter
          ));
          if (isset($chapterDatabase)) {
            $this->updateChapter($chapterDatabase, $chapterJson);
          } else {
            $this->createChapter($langCodeDatabase, $chapterJson);
          }
        }
      }

      if ($offset < $response_json->total) {
        $this->refreshChapterByMangaId($mangaId, $langCodeAllow, $offset + 100);
      }
    } else if ($response->getStatusCode() == 401) {
      $this->authRefresh();
      $this->refreshChapterByMangaId($mangaId, $langCodeAllow, $offset);
    } else {
      return $response->getStatusCode();
    }
  }

  /**
   * Undocumented function
   *
   * @param LanguageCode $langCode
   * @param object $chapterJson
   * @return void
   */
  private function createChapter(LanguageCode $langCode, object $chapterJson)
  {
    $chapter = new Chapter();

    $chapter->setLangCode($langCode)
      ->setManga($this->manga)
      ->setChapterId($chapterJson->id)
      ->setNumber($chapterJson->attributes->chapter)
      ->setVolume($chapterJson->attributes->volume)
      ->setTitle($chapterJson->attributes->title)
      ->setPublishedAt(new \DateTime($chapterJson->attributes->publishAt));

    $manga = $chapter->getManga();
    $manga->setLastUploaded($chapter->getPublishedAt());

    $this->manager->persist($chapter);
    $this->manager->persist($manga);
    $this->manager->flush();

    //$this->writeOutput($this->manga->getName() . ' - Langue : ' . $langCode->getLibelle() . ' - Chapitre n°' . $chapter->getNumber() . ' ajouté !');

    $string = $langCode->getTwitterFlag() . ' ' . $this->manga->getName() . ' - Chapitre n°' . $chapter->getNumber() . ' sortie !' . PHP_EOL;
    $string .= 'Disponible ici ' . "Bientôt"; //$this->getMangadexUrlChapter($chapterJson->id);

    if ($this->manga->getTwitter() && $this->sendTwitter) {
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
   * @param Chapter $chapterDatabase
   * @param object $chapterJson
   * @return void
   */
  private function updateChapter(Chapter $chapterDatabase, object $chapterJson)
  {
    if ($chapterDatabase->getPublishedAt() < new \DateTime($chapterJson->attributes->publishAt)) {
      $chapterDatabase->setChapterId($chapterJson->id)
        ->setVolume($chapterJson->attributes->volume)
        ->setTitle($chapterJson->attributes->title)
        ->setPublishedAt(new \DateTime($chapterJson->attributes->publishAt));

      $manga = $chapterDatabase->getManga();
      $manga->setLastUploaded($chapterDatabase->getPublishedAt());

      $this->manager->persist($chapterDatabase);
      $this->manager->persist($manga);
      $this->manager->flush();
    }
  }

  /**
   * Undocumented function
   *
   * @param string $mangaId
   * @return string
   */
  public function viewManga(string $mangaId): string
  {

    $response = $this->client->request('GET', $this->endPointViewManga($mangaId));

    if ($response->getStatusCode() == 200) {
      $response_json = json_decode($response->getContent());

      $mangaJSON = $response_json->data;

      $mangaDataBase = $this->mangaRepo->findOneBy(array(
        'mangadexId' => $mangaId,
      ));

      if (!isset($mangaDataBase)) {
        return $this->createManga($mangaJSON, $mangaId);
      } else {
        return $this->updateManga($mangaDataBase, $mangaId, $mangaJSON);
      }
    } else if ($response->getStatusCode() == 401) {
      $this->authRefresh();
      $this->viewManga($mangaId);
    } else {
      return $response->getStatusCode();
    }
  }

  /**
   * Undocumented function
   *
   * @param object $mangaJSON
   * @param string $mangaId
   * @return string
   */
  private function createManga(object $mangaJSON, string $mangaId): string
  {
    $this->manga = new Manga();
    $this->manga->setName(html_entity_decode($mangaJSON->attributes->title->en, ENT_QUOTES, 'UTF-8'));

    //$this->manga->setImage($mangaJSON->mainCover);
    $this->manga->setImage('');
    $this->manga->setMangaId(0);
    $this->manga->setMangadexId($mangaId);
    $this->manga->setTwitter(TRUE);
    $this->manga->setLastUploaded(new \DateTime(date("Y-m-d H:i:s", 0)));

    $this->manager->persist($this->manga);
    $this->manager->flush();

    $string = '"' . $this->manga->getName() . '"' . ' a été ajouté !' . PHP_EOL;
    $string .= 'Disponible ici ' . "Bientôt"; //$this->getMangadexUrlManga($mangaId);

    if ($this->sendTwitter) {
      $result = $this->twitter->sendTweet($string);
    }

    return "created";
  }

  /**
   * Undocumented function
   *
   * @param Manga $manga
   * @param string $mangaId
   * @param object $mangaJSON
   * @return string
   */
  private function updateManga(Manga $manga, string $mangaId, object $mangaJSON): string
  {
    $updated = "not_updated";

    if ($manga->getMangadexId() != $mangaId) {
      $manga->setMangadexId($mangaId);
    }

    if ($manga->getRawName() != $mangaJSON->attributes->title->en) {
      $manga->setRawName($mangaJSON->attributes->title->en);
    }

    // if ($manga->getImage() != $mangaJSON->mainCover) {
    //   $manga->setImage($mangaJSON->mainCover);
    // }

    if (($manga->getLastUploaded()) < new \DateTime($mangaJSON->attributes->updatedAt)) {
      $updated = "updated";
    }

    $this->manager->persist($manga);
    $this->manager->flush();

    $this->manga = $manga;

    return $updated;
  }

  /**
   * Undocumented function
   *
   * @param array $params
   * @return string
   */
  public function mangaList(array $params)
  {

    $response = $this->client->request('GET', $this->getEndPoint("manga"), ["query" => $params]);

    if ($response->getStatusCode() == 200) {
      $response_json = json_decode($response->getContent());

      return $response_json->results;
    } else if ($response->getStatusCode() == 401) {
      $this->authRefresh();
    } else {
      return $response->getStatusCode();
    }
  }

  /**
   * Undocumented function
   *
   * @param array $params
   * @return string
   */
  public function chapterList(array $params)
  {

    $response = $this->client->request('GET', $this->getEndPoint("chapter"), ["query" => $params]);

    if ($response->getStatusCode() == 200) {
      $response_json = json_decode($response->getContent());

      return $response_json->results;
    } else if ($response->getStatusCode() == 401) {
      $this->authRefresh();
    } else {
      return $response->getStatusCode();
    }
  }

  /**
   * Undocumented function
   *
   * @param string $id
   * @return string
   */
  private function getEndPoint(string $endPoint_code): string
  {
    $endPoint = "";
    switch ($endPoint_code) {
      case "auth_login":
        $endPoint = "/auth/login";
        break;
      case "check_token":
        $endPoint = "/auth/check";
        break;
      case "refresh_token":
        $endPoint = "/auth/refresh";
        break;
      case "manga":
        $endPoint = "/manga";
        break;
      case "chapter":
        $endPoint = "/chapter";
        break;
      default:
        break;
    }

    return $this->getApiMangadexUrl() . $endPoint;
  }

  /**
   * Undocumented function
   *
   * @return string
   */
  private function endPointViewManga(string $id): string
  {
    return $this->getEndPoint("manga")  . "/" . $id;
  }

  /**
   * Undocumented function
   *
   * @return string
   */
  private function getApiMangadexUrl(): string
  {
    return $this->params->get('api_mangadex_url');
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

  /**
   * Undocumented function
   *
   * @return string
   */
  private function getMangadexUrlManga(string $id): string
  {
    return $this->getMangadexUrl()  . '/manga/' . $id;
  }

  /**
   * Undocumented function
   *
   * @return string
   */
  private function getMangadexUrlChapter(string $id): string
  {
    return $this->getMangadexUrl()  . '/chapter/' . $id;
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
}
