<?php

namespace App\Controller;

use App\Form\EditUserType;
use App\Entity\Utilisateur;
use App\Service\FileRemover;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UtilisateurRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Filesystem\Filesystem;
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
     * @Route("/admin/user/edit/{id}", name="admin_edit_user")
     */
    public function editUser(Utilisateur $user, Request $request, EntityManagerInterface $manager, FileUploader $fileUploader, FileRemover $fileRemover)
    {
        $form = $this->createForm(EditUserType::class, $user);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('avatar')->getData();
            if ($imageFile) {
                $old_avatar = $user->getAvatar();
                $imageFile = $fileUploader->uploadImage($imageFile, 'avatar');
                $user->setAvatar($imageFile);
                $fileRemover->removeImage(new Filesystem(), 'avatar', $old_avatar);
            }

            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', ucfirst($user->getUsername()).' modifié avec succès');
            return $this->redirectToRoute('admin_user');
        }
        
        return $this->render('administration/edituser.html.twig', [
            'formUser' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/user/delete/{id}", name="admin_delete_user")
     */
    public function deleteUser(Utilisateur $user, Request $request, EntityManagerInterface $manager, FileRemover $fileRemover)
    {
        $avatar = $user->getAvatar();

        if($avatar !== 'default.png'){
            $fileRemover->removeImage(new Filesystem(), 'avatar', $avatar);
        }

        $manager->remove($user);
        $manager->flush();

        $this->addFlash('warning', ucfirst($user->getUsername()).' supprimé avec succès');
        return $this->redirectToRoute('admin_user');
    }
}
