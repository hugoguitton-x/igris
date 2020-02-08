<?php

namespace App\Controller;

use App\Entity\Tache;
use App\Form\TacheType;
use App\Repository\TacheRepository;
use App\Repository\EtatTacheRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RoadMapController extends AbstractController
{
    /**
     * @Route("/roadmap", name="road_map")
     */
    public function index(TacheRepository $repo)
    {
        $taches = $repo->findBy(array(), array('date' => 'ASC'));

        return $this->render('road_map/index.html.twig', [
            'controller_name' => 'RoadMapController',
            'taches' => $taches
        ]);
    }

    /**
     * @Route("/roadmap/new", name="road_map_new")
     * @Route("/roadmap/edit/{id}", name="road_map_edit")
     */
    public function form(
        Request $request,
        EntityManagerInterface $manager,
        Tache $tache = null
    ) {
        if (!$tache) {
            $tache = new Tache();
            $tache->setDate(new \DateTime());
        }

        $form = $this->createForm(TacheType::class, $tache);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($tache);
            $manager->flush();

            return $this->redirectToRoute('road_map');
        }

        return $this->render('road_map/form.html.twig', [
            'formTache' => $form->createView(),
            'editMode' => $tache->getId() !== null
        ]);
    }

    /**
     * @Route("/roadmap/delete/{id}", name="road_map_delete")
     */
    public function delete(EntityManagerInterface $manager, Tache $tache)
    {
        $manager->remove($tache);
        $manager->flush();

        return $this->redirectToRoute('road_map');
    }

    /**
     * @Route("/roadmap/start/{id}", name="road_map_start")
     */
    public function start(
        EntityManagerInterface $manager,
        Tache $tache,
        EtatTacheRepository $repo
    ) {
        $etatTache = $repo->findOneBy(['nom' => 'in_progress']);
        $tache->setEtat($etatTache);
        $manager->persist($tache);
        $manager->flush();

        return $this->redirectToRoute('road_map');
    }

    /**
     * @Route("/roadmap/complete/{id}", name="road_map_complete")
     */
    public function complete(
        EntityManagerInterface $manager,
        Tache $tache,
        EtatTacheRepository $repo
    ) {
        $etatTache = $repo->findOneBy(['nom' => 'completed']);
        $tache->setEtat($etatTache);
        $manager->persist($tache);
        $manager->flush();

        return $this->redirectToRoute('road_map');
    }
}
