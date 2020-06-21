<?php

namespace App\Controller;

use App\Form\EditUserType;
use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdministrationController extends AbstractController
{
    /**
     * @Route("/admin", name="administration")
     */
    public function index()
    {
        return $this->render('administration/index.html.twig', [
            'controller_name' => 'AdministrationController',
        ]);
    }

    /**
     * @Route("/admin/user", name="admin_user")
     */
    public function user(Request $request, UtilisateurRepository $repo, PaginatorInterface $paginator)
    {
        $hasAccess = $this->isGranted('ROLE_ADMIN');

$this->denyAccessUnlessGranted('ROLE_ADMIN');
        $query = $repo->getUtilisateurAdminInfosOrderByUsernameQuery();

        $users = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('administration/user.html.twig', [
            'controller_name' => 'AdministrationController',
            'users' => $users
        ]);
    }

    /**
     * @Route("/admin/user/edit/{id}", name="modifier_utilisateur")
     */
    public function editUser(Utilisateur $user, Request $request)
    {
        $form = $this->createForm(EditUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('message', 'Utilisateur modifié avec succès');
            return $this->redirectToRoute('admin_utilisateurs');
        }
        
        return $this->render('admin/edituser.html.twig', [
            'userForm' => $form->createView(),
        ]);
    }
}
