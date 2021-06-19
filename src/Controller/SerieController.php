<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Serie;
use App\Form\AvisType;
use App\Form\SerieType;
use Psr\Log\LoggerInterface;
use App\Service\FileUploader;
use App\Repository\AvisRepository;
use App\Repository\SerieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/serie", name="serie_")
 */
class SerieController extends AbstractController
{

    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @Route("", name="index")
     */
    public function index(
        SerieRepository $repo,
        EntityManagerInterface $manager,
        PaginatorInterface $paginator,
        Request $request
    ) {
        $query = $repo->findSeriesOrderByNameQuery();

        $series = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            16
        );

        $series->setCustomParameters([
            'align' => 'center', # center|right
            'size' => 'small', # small|large
        ]);

        return $this->render('serie/index.html.twig', [
            'controller_name' => 'SerieController',
            'series' => $series,
        ]);
    }

    /**
     * @Route("/{id}", name="show")
     */
    public function showSerie(
        AvisRepository $repo,
        Serie $serie
    ) {
        $avisList = $repo->findBy(
            ['serie' => $serie],
            ['createdAt' => 'DESC'],
            5
        );

        $checkAvisUser = $repo->findBy(
            ['utilisateur' => $this->getUser(), 'serie' => $serie],
            [],
            1
        );

        return $this->render('serie/show.html.twig', [
            'controller_name' => 'SerieController',
            'serie' => $serie,
            'checkAvisUser' => $checkAvisUser,
            'avisList' => $avisList,
        ]);
    }

    /**
     * @Route("/{id}/avis", name="avis_show")
     */
    public function showAvis(
        AvisRepository $repo,
        Serie $serie,
        PaginatorInterface $paginator,
        Request $request
    ) {
        $query = $repo->findAvisSerieByDateQuery($serie);

        $avisList = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        $avisList->setCustomParameters([
            'align' => 'center', # center|right
            'size' => 'small', # small|large
        ]);

        return $this->render('serie/avis.html.twig', [
            'avisList' => $avisList,
            'serie' => $serie,
        ]);
    }


    /**
     * @Route("/{id}/avis/new", name="avis_new")
     */
    public function formAvis(
        AvisRepository $repo,
        Request $request,
        EntityManagerInterface $manager,
        Serie $serie = null
    ) {

        if ($serie) {
            $avis = new Avis();
            $avis->setCreatedAt(new \DateTime());
            $avis->setUtilisateur($this->getUser());
            $avis->setSerie($serie);
        } else {
            throw new NotFoundHttpException('Serie with id : ' . $request->get('id') . ' doesn\'t exist');
        }

        $checkAvisUser = $repo->findBy(
            ['utilisateur' => $this->getUser(), 'serie' => $serie],
            [],
            1
        );

        if ($checkAvisUser) {
            $this->addFlash('warning', 'You have already posted a review with your account for this series.');
            return $this->redirectToRoute('serie_show', ['id' => $serie->getId()]);
        }

        $form = $this->createForm(AvisType::class, $avis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $avis->setCommentaire(nl2br($avis->getCommentaire()));
            $manager->persist($avis);

            $listAvis = $serie->getAvis();

            $total = $avis->getNote();
            foreach ($listAvis as $avis) {
                $total += $avis->getNote();
            }

            $serie->setNoteMoyenne(round($total / (sizeof($listAvis) + 1), 2));
            $manager->persist($serie);

            $manager->flush();

            return $this->redirectToRoute('serie_show', ['id' => $serie->getId()]);
        }

        return $this->render('serie/form_avis.html.twig', [
            'controller_name' => 'SerieController',
            'serie' => $serie,
            'formAvis' => $form->createView()
        ]);
    }
}
