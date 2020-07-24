<?php

namespace App\Controller;

use App\Repository\MangaRepository;
use App\Repository\ChapterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


/**
 * @Route("/manga", name="manga_")
 */
class MangaController extends AbstractController
{
    /**
     * @Route("", name="index")
     */
    public function index(ChapterRepository $repo, PaginatorInterface $paginator, Request $request)
    {      
        $query = $repo->findLastChapterOrderByDateQuery();

        $chapters = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            16
        );

        return $this->render('manga/index.html.twig', [
            'controller_name' => 'MangaController',
            'chapters' => $chapters
        ]);
    }

    /**
     * @Route("/list", name="list")
     */
    public function listeManga(MangaRepository $repo, PaginatorInterface $paginator, Request $request)
    {      
        $query = $repo->findMangaOrderByNameQuery();

        $mangas = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            24
        );

        return $this->render('manga/liste.html.twig', [
            'controller_name' => 'MangaController',
            'mangas' => $mangas
        ]);
    }

    /**
     * @Route("/{language}", name="language")
     */
    public function indexFr(ChapterRepository $repo, EntityManagerInterface $manager, PaginatorInterface $paginator, Request $request, string $language)
    {      
        $array_language = [
            'en' => 'English',
            'fr' => 'French'
        ];
        $query = $repo->findLastChapterOrderByDateQuery($array_language[$language]);

        $chapters = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            16
        );

        return $this->render('manga/index.html.twig', [
            'controller_name' => 'MangaController',
            'chapters' => $chapters
        ]);
    }
}
