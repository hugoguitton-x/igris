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
   * @Route("/twitter/{id}", name="twitter")
   */
  public function twitterManga(
    Manga $manga = null,
    EntityManagerInterface $manager,
    TranslatorInterface $translator
  ) {
    if ($manga->getTwitter() !== null) {
      $manga->setTwitter(!$manga->getTwitter());
    } else {
      $manga->setTwitter(true);
    }

    $manager->persist($manga);
    $manager->flush();

    if ($manga->getTwitter()) {
      $twitter = 'enabled';
    } else {
      $twitter = 'disabled';
    }

    $this->addFlash('success', $translator->trans('twitter.' . $twitter, ['%slug%' => ucfirst($manga->getName())]));
    return $this->redirectToRoute('manga_list');
  }

  /**
   * @Route("/follow/{id}", name="follow")
   */
  public function followManga(
    Manga $manga = null,
    EntityManagerInterface $manager,
    TranslatorInterface $translator,
    FollowMangaRepository $repo
  ) {

    $mangaFollow = $repo->findBy(
      ['utilisateur' => $this->getUser(), 'manga' => $manga],
      [],
      1
    );
    if (!empty($mangaFollow)) {
      $manager->remove($mangaFollow[0]);

      $follow_status = 'unfollowed';
    } else {
      $follow = new FollowManga();
      $follow->setManga($manga);
      $follow->setUtilisateur($this->getUser());

      $manager->persist($follow);

      $follow_status = 'followed';
    }

    $manager->flush();


    $this->addFlash('success', $translator->trans('manga.' . $follow_status, ['%slug%' => ucfirst($manga->getName())]));
    return $this->redirectToRoute('manga_list');
  }
}
