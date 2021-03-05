<?php

namespace App\Controller;

use DateTime;
use App\Entity\Depense;
use App\Form\DepenseType;
use App\Data\DepenseSearchData;
use App\Form\DepenseFilterType;
use App\Entity\CategorieDepense;
use App\Entity\CompteDepense;
use App\Form\CategorieDepenseType;
use App\Repository\CompteDepenseRepository;
use App\Repository\DepenseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/depense", name="depense_")
 */
class DepenseController extends AbstractController
{
  /**
   * @Route("", name="index")
   */
  public function index(DepenseRepository $repository, CompteDepenseRepository $compteRepository, Request $request): Response
  {
    $data = new DepenseSearchData();

    $form = $this->createForm(DepenseFilterType::class, $data);
    $form->handleRequest($request);
    $depenses = $repository->findByFilter($data);

    $compte = $compteRepository->findOneByUtilisateur($this->getUser(), array('id' => 'ASC'));
    $depenseMonth = $repository->findDepenseForMonth($data);
    $depenseTotal = $repository->findDepenseAfterDate($data);

    if ($request->get('ajax')) {

      return new JsonResponse([
        'content' => $this->renderView('depense/_depenses.html.twig', [
          'depenses' => $depenses,
          'formDepense' => $form->createView(),
          'soldeIntial' => ($compte->getSolde() - $depenseTotal['depenseTotal']) - $depenseMonth['depenseMonth'],
          'depenseMonth' => $depenseMonth['depenseMonth'],
          'soldeFinal' => ($compte->getSolde() - $depenseTotal['depenseTotal'])
        ]),
      ]);
    }

    $depenseCourseAvgMonth = $repository->findDepenseCourseAvgByMonthTotal();
    $depenseCourseAvg = $repository->findDepenseAvgByCourse();


    return $this->render('depense/index.html.twig', [
      'depenses' => $depenses,
      'formDepense' => $form->createView(),
      'soldeIntial' => ($compte->getSolde() - $depenseTotal['depenseTotal']) - $depenseMonth['depenseMonth'],
      'depenseMonth' => $depenseMonth['depenseMonth'],
      'soldeFinal' => ($compte->getSolde() - $depenseTotal['depenseTotal']),
      'depenseCourseAvgMonth' => $depenseCourseAvgMonth,
      'depenseCourseAvg' => $depenseCourseAvg,
    ]);
  }

  /**
   * @Route("/new", name="new")
   * @Route("/edit/{id}", name="_edit")
   */
  public function manageFormDepense(
    Request $request,
    EntityManagerInterface $manager,
    Depense $depense = null,
    TranslatorInterface $translator
  ): Response {
    if (!$depense) {
      $depense = new Depense();
    }

    $form = $this->createForm(DepenseType::class, $depense);
    $form->handleRequest($request);

    if ($depense->getId() !== null) {
      $edit = true;
    } else {
      $edit = false;
    }

    if ($form->isSubmitted() && $form->isValid()) {
      $compte = $depense->getCompteDepense();
      $compte->setSolde($compte->getSolde() + $depense->getMontant());

      $manager->persist($depense);
      $manager->flush();
      return $this->redirectToRoute('depense_index');
    }

    return $this->render('depense/form.html.twig', [
      'formDepense' => $form->createView(),
      'editMode' => $edit
    ]);
  }

  /**
   * @Route("/category/new", name="categorie_new")
   * @Route("/category/edit/{id}", name="categorie_edit")
   */
  public function manageFormCategory(
    Request $request,
    EntityManagerInterface $manager,
    CategorieDepense $categorie = null,
    TranslatorInterface $translator
  ): Response {
    if (!$categorie) {
      $categorie = new CategorieDepense();
    }

    $form = $this->createForm(CategorieDepenseType::class, $categorie);
    $form->handleRequest($request);

    if ($categorie->getId() !== null) {
      $edit = true;
    } else {
      $edit = false;
    }

    if ($form->isSubmitted() && $form->isValid()) {
      $manager->persist($categorie);
      $manager->flush();
      return $this->redirectToRoute('depense_index');
    }

    return $this->render('depense/categorie/form.html.twig', [
      'formCategorie' => $form->createView(),
      'editMode' => $edit
    ]);
  }
}
