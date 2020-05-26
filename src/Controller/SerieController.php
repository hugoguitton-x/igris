<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Serie;
use App\Form\AvisType;
use App\Form\SerieType;
use App\Service\FileUploader;
use App\Repository\AvisRepository;
use App\Repository\SerieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SerieController extends AbstractController
{
    /**
     * @Route("/serie", name="serie")
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

        return $this->render('serie/index.html.twig', [
            'controller_name' => 'SerieController',
            'series' => $series,
        ]);
    }

    /**
     * @Route("/serie/new", name="serie_new")
     * @Route("/serie/edit/{id}", name="serie_edit")
     */
    public function form(
        Request $request,
        EntityManagerInterface $manager,
        Serie $serie = null,
        FileUploader $fileUploader
    ) {
        if (!$serie) {
            $serie = new Serie();
            $serie->setNoteMoyenne(0);
        }

        $form = $this->createForm(SerieType::class, $serie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $imageFile = $fileUploader->uploadImage($imageFile, 'series');
            } else {
                $imageFile = '';
            }

            $serie->setSynopsis(nl2br($serie->getSynopsis()));
            $serie->setCreatedAt(new \DateTime());
            $serie->setImage($imageFile);

            $manager->persist($serie);
            $manager->flush();

            return $this->redirectToRoute('serie');
        }

        return $this->render('serie/form.html.twig', [
            'formSerie' => $form->createView(),
            'editMode' => $serie->getId() !== null,
        ]);
    }

    /**
     * @Route("/serie/{id}", name="serie_show")
     */
    public function showSerie(
        AvisRepository $repo,
        Serie $serie,
        Request $request,
        EntityManagerInterface $manager,
        Avis $avis = null
    ) {
        if (!$avis) {
            $avis = new Avis();
            $avis->setCreatedAt(new \DateTime());
            $avis->setUtilisateur($this->getUser());
            $avis->setSerie($serie);
        }

        $avisList = $repo->findBy(
            ['serie' => $serie],
            ['createdAt' => 'DESC'],
            5
        );

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

            return $this->redirectToRoute('serie');
        }

        $checkAvisUser = $repo->findBy(
            ['utilisateur' => $this->getUser(), 'serie' => $serie],
            [],
            1
        );
        if (!$checkAvisUser) {
            $formAvis = $form->createView();
        } else {
            $formAvis = null;
        }

        return $this->render('serie/show.html.twig', [
            'controller_name' => 'SerieController',
            'serie' => $serie,
            'formAvis' => $formAvis,
            'avisList' => $avisList,
        ]);
    }

    /**
     * @Route("/serie/{id}/avis", name="avis_serie_show")
     */
    public function showAvis(
        AvisRepository $repo,
        Serie $serie,
        EntityManagerInterface $manager,
        PaginatorInterface $paginator,
        Request $request
    ) {
        $query = $repo->findAvisSerieByDateQuery($serie);

        $avisList = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('serie/avis.html.twig', [
            'avisList' => $avisList,
            'serie' => $serie,
        ]);
    }
}
