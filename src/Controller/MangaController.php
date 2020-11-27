<?php

namespace App\Controller;

use App\Entity\FollowManga;
use App\Entity\Manga;
use Psr\Log\LoggerInterface;
use App\Repository\MangaRepository;
use App\Repository\ChapterRepository;
use App\Repository\FollowMangaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/manga", name="manga_")
 */
class MangaController extends AbstractController
{

  private $logger;

  public function __construct(LoggerInterface $logger)
  {
    $this->logger = $logger;
  }


  /**
   * @Route("", name="list")
   */
  public function listMangas(MangaRepository $repo, PaginatorInterface $paginator, Request $request)
  {
    $query = $repo->findMangaOrderByNameQuery();

    $mangas = $paginator->paginate(
      $query,
      $request->query->getInt('page', 1),
      30
    );

    $mangas->setCustomParameters([
      'align' => 'center', # center|right
      'size' => 'small', # small|large
    ]);

    return $this->render('manga/mangas-list.html.twig', [
      'controller_name' => 'MangaController',
      'mangas' => $mangas
    ]);
  }

  /**
   * @Route("/chapters", name="chapters")
   */
  public function chaptersList(ChapterRepository $repo, EntityManagerInterface $manager, PaginatorInterface $paginator, Request $request/* , string $language */)
  {
    $array_language = [
      'en' => 'English',
      'fr' => 'French'
    ];
    $query = $repo->findLastChapterOrderByDateQuery();

    $chapters = $paginator->paginate(
      $query,
      $request->query->getInt('page', 1),
      30
    );

    $chapters->setCustomParameters([
      'align' => 'center', # center|right
      'size' => 'small', # small|large
    ]);

    return $this->render('manga/chapters-list.html.twig', [
      'controller_name' => 'MangaController',
      'chapters' => $chapters
    ]);
  }


  /**
   * @Route("/twitter/{id}", name="twitter", methods={"POST"})
   */
  public function twitterManga(
    Manga $manga = null,
    EntityManagerInterface $manager,
    TranslatorInterface $translator
  ): Response {

    if (!$this->getUser()) {
      return $this->json(['code' => 403, 'message' => 'Unauthorized'], 403);
    }


    if ($manga->getTwitter() === null || $manga->getTwitter() === false) {
      $manga->setTwitter(true);

      $twitter = 'enabled';
    } else {
      $manga->setTwitter(false);

      $twitter = 'disabled';
    }

    $manager->persist($manga);
    $manager->flush();

    $message = $translator->trans('twitter.' . $twitter, ['%slug%' => ucfirst($manga->getName())]);


    return $this->json(['code' => 200, 'message' => $message, 'value' => $translator->trans(ucfirst($twitter))], 200);
  }

  /**
   * @Route("/follow/{id}", name="follow", methods={"POST"})
   */
  public function followManga(
    Manga $manga = null,
    EntityManagerInterface $manager,
    TranslatorInterface $translator,
    FollowMangaRepository $repo
  ) {
    $user = $this->getUser();
    if (!$user) {
      return $this->json(['code' => 403, 'message' => 'Unauthorized'], 403);
    }

    if ($manga->isFollowedByUser($user)) {
      $mangaFollow = $repo->findOneBy([
        'utilisateur' => $this->getUser(),
        'manga' => $manga
      ]);

      $manager->remove($mangaFollow);

      $follow_status = 'unfollowed';
    } else {
      $follow = new FollowManga();
      $follow->setManga($manga);
      $follow->setUtilisateur($this->getUser());

      $manager->persist($follow);

      $follow_status = 'followed';
    }

    $manager->flush();

    $message = $translator->trans($translator->trans('manga.' . $follow_status, ['%slug%' => ucfirst($manga->getName())]));

    return $this->json(['code' => 200, 'message' => $message, 'value' => $translator->trans(ucfirst($follow_status))], 200);
  }
}
