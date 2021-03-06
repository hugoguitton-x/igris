<?php

namespace App\Controller;

use App\Entity\Manga;
use App\Entity\FollowManga;
use Psr\Log\LoggerInterface;
use App\Data\MangaSearchData;
use App\Form\MangaSearchType;
use App\Repository\MangaRepository;
use App\Repository\ChapterRepository;
use App\Helper\MangaMangadexApiHelperV5;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\FollowMangaRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    public function listMangas(MangaRepository $repo, Request $request)
    {
        $data = new MangaSearchData();
        $data->page = $request->get('page', 1);
        $form = $this->createForm(MangaSearchType::class, $data);
        $form->handleRequest($request);

        $mangas = $repo->findMangaOrderByNameQuery($data);

        if ($request->get('ajax')) {
            return new JsonResponse([
                'content' => $this->renderView('manga/_mangas.html.twig', ['mangas' => $mangas]),
                'pagination' => $this->renderView('manga/_pagination.html.twig', ['mangas' => $mangas])
            ]);
        }


        return $this->render('manga/mangas-list.html.twig', [
            'controller_name' => 'MangaController',
            'mangas' => $mangas,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/search", name="search")
     */
    public function searchMangas(MangaRepository $repo, Request $request, EntityManagerInterface $manager)
    {
        $data = new MangaSearchData();
        $data->page = $request->get('page', 1);
        $form = $this->createForm(MangaSearchType::class, $data);
        $form->handleRequest($request);

        $mangaMangadexApi = new MangaMangadexApiHelperV5($this->container->get('parameter_bag'), $manager);

        if ($data->q) {
            $result_search = $mangaMangadexApi->mangaList(["title" =>  $data->q, "limit" => 100]);
        } else {
            $result_search = "";
        }

        if ($request->get('ajax')) {

            return new JsonResponse([
                'content' => $this->renderView('manga/_search.html.twig', ['result_search' => $result_search])
            ]);
        }

        return $this->render('manga/mangas-search.html.twig', [
            'controller_name' => 'MangaController',
            'result_search' => $result_search,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/chapters", name="chapters")
     */
    public function chaptersList(ChapterRepository $repo, EntityManagerInterface $manager, PaginatorInterface $paginator, Request $request/* , string $language */)
    {
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
        EntityManagerInterface $manager,
        TranslatorInterface $translator,
        Manga $manga = null
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

        $message = $translator->trans('manga.twitter.' . $twitter, ['%slug%' => ucfirst($manga->getName())]);

        return $this->json(['code' => 200, 'content' => $this->renderView('components/toast.html.twig', ['toastMessage' => $message]), 'value' => $translator->trans(ucfirst($twitter))], 200);
    }

    /**
     * @Route("/follow/{id}", name="follow", methods={"POST"})
     */
    public function followManga(
        EntityManagerInterface $manager,
        TranslatorInterface $translator,
        FollowMangaRepository $repo,
        Manga $manga = null
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

        return $this->json(['code' => 200, 'content' => $this->renderView('components/toast.html.twig', ['toastMessage' => $message]), 'value' => $translator->trans(ucfirst($follow_status))], 200);
    }

    /**
     * @Route("/refresh/{id}", name="refresh", methods={"POST"})
     */
    public function refreshManga(
        EntityManagerInterface $manager,
        TranslatorInterface $translator,
        Manga $manga = null
    ): Response {

        if (!$this->getUser()) {
            return $this->json(['code' => 403, 'message' => 'Unauthorized'], 403);
        }

        $mangaMangadexApi = new MangaMangadexApiHelperV5($this->container->get('parameter_bag'), $manager);
        $mangaMangadexApi->refreshMangaById($manga->getMangaId());

        $message = $translator->trans('manga.refresh', ['%slug%' => ucfirst($manga->getName())]);

        return new JsonResponse([
            'content' => $this->renderView('components/toast.html.twig', ['toastMessage' => $message]),
        ]);
    }
}
