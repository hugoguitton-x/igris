<?php

namespace App\Controller;

use DateTime;
use App\Entity\Depense;
use App\Form\DepenseType;
use App\Entity\CompteDepense;
use App\Data\DepenseSearchData;
use App\Form\DepenseFilterType;
use App\Entity\CategorieDepense;
use App\Entity\DepenseRecurrente;
use App\Form\CategorieDepenseType;
use App\Form\DepenseRecurrenteType;
use App\Repository\DepenseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CompteDepenseRepository;
use App\Repository\DepenseRecurrenteRepository;
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
  public function index(DepenseRepository $depenseRepository, CompteDepenseRepository $compteRepository, DepenseRecurrenteRepository $depenseRecurrenteRepository, Request $request): Response
  {

    $data = new DepenseSearchData();

    $form = $this->createForm(DepenseFilterType::class, $data);
    $form->handleRequest($request);

    $depenses = $depenseRepository->findByFilter($data);

    $compte = $compteRepository->findOneByUtilisateur($this->getUser(), array('id' => 'ASC'));
    $depenseMonth = $depenseRepository->findDepenseForMonth($data);
    $depenseTotal = $depenseRepository->findDepenseAfterDate($data);
    $depensesRecurrentes = $depenseRecurrenteRepository->findDepenseRecurrenteByAccountNotUsed($data);

    if ($request->get('ajax')) {

      return new JsonResponse([
        'content' => $this->renderView('depense/_depenses.html.twig', [
          'depenses' => $depenses,
          'formDepense' => $form->createView(),
          'soldeIntial' => ($compte->getSolde() - $depenseTotal['depenseTotal']) - $depenseMonth['depenseMonth'],
          'depenseMonth' => $depenseMonth['depenseMonth'],
          'soldeFinal' => ($compte->getSolde() - $depenseTotal['depenseTotal']),
          'depensesRecurrentes' => $depensesRecurrentes,
          'dateSearch' => $data->date
        ]),
      ]);
    }

    $depenseCourseAvgMonth = $depenseRepository->findDepenseCourseAvgByMonthTotal();
    $depenseCourseAvg = $depenseRepository->findDepenseAvgByCourse();

    return $this->render('depense/index.html.twig', [
      'depenses' => $depenses,
      'formDepense' => $form->createView(),
      'soldeIntial' => ($compte->getSolde() - $depenseTotal['depenseTotal']) - $depenseMonth['depenseMonth'],
      'depenseMonth' => $depenseMonth['depenseMonth'],
      'soldeFinal' => ($compte->getSolde() - $depenseTotal['depenseTotal']),
      'depenseCourseAvgMonth' => (- $depenseCourseAvgMonth),
      'depenseCourseAvg' => (- $depenseCourseAvg),
      'depensesRecurrentes' => $depensesRecurrentes,
      'dateSearch' => $data->date
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
   * @Route("/recurrent/new", name="recurrente_new")
   * @Route("/recurrent/edit/{id}", name="recurrente_edit")
   */
  public function manageFormDepenseReccurente(
    Request $request,
    EntityManagerInterface $manager,
    DepenseRecurrente $depenseRecurrente = null,
    TranslatorInterface $translator
  ): Response {
    if (!$depenseRecurrente) {
      $depenseRecurrente = new DepenseRecurrente();
    }

    $form = $this->createForm(DepenseRecurrenteType::class, $depenseRecurrente);
    $form->handleRequest($request);

    if ($depenseRecurrente->getId() !== null) {
      $edit = true;
    } else {
      $edit = false;
    }

    if ($form->isSubmitted() && $form->isValid()) {
      $compte = $depenseRecurrente->getCompteDepense();
      //$compte->setSolde($compte->getSolde() + $depenseRecurrente->getMontant());

      $manager->persist($depenseRecurrente);
      $manager->flush();
      return $this->redirectToRoute('depense_index');
    }

    return $this->render('depense/formRecurrente.html.twig', [
      'formRecurrente' => $form->createView(),
      'editMode' => $edit
    ]);
  }


  /**
   * @Route("/recurrent/{id}/add/", name="recurrente_add")
   */
  public function addDepenseFromReccurente(
    Request $request,
    EntityManagerInterface $manager,
    DepenseRecurrente $depenseRecurrente = null,
    TranslatorInterface $translator
  ): Response {
    $depense = null;

    if (!$request->isMethod('POST')) {
      $depense = new Depense();
      $depense->setMontant($depenseRecurrente->getMontant());
      $depense->setCompteDepense($depenseRecurrente->getCompteDepense());
      $depense->setCategorie($depenseRecurrente->getCategorie());
    }

    $form = $this->createForm(DepenseType::class, $depense);
    $form->handleRequest($request);
    $depense = $form->getData();
    $depense->setDepenseRecurrente($depenseRecurrente);

    if ($depense->getId() === null) {
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
