<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Service\FileUploader;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{

    /**
     * @Route("", name="home_page_redirection")
     */
    public function homepageRedirection() {
        return $this->redirectToRoute('home_page');
    }

    /**
     * @Route("/{_locale}", name="home_page")
     * requirements={
     *  "_locale": "%app.locales%"
     * }
     */
    public function homepage() {
        return $this->render('site/home_page.html.twig');
    }

    /**
     * @Route("/{_locale}/register", name="security_registration")
     * requirements={
     *  "_locale": "%app.locales%"
     * }
     */
    public function registration(
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordEncoderInterface $encoder,
        FileUploader $fileUploader
    ) {
        $utilisateur = new Utilisateur();

        $form = $this->createForm(RegistrationType::class, $utilisateur);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('avatar')->getData();
            if ($imageFile) {
                $imageFile = $fileUploader->uploadImage($imageFile, 'avatar');
            } else {
                $imageFile = 'default.png';
            }
            $utilisateur->setAvatar($imageFile);

            $hash = $encoder->encodePassword(
                $utilisateur,
                $utilisateur->getPassword()
            );

            $utilisateur->setPassword($hash);

            $utilisateur->setRoles(array('ROLE_USER'));

            $manager->persist($utilisateur);
            $manager->flush();

            return $this->redirectToRoute('security_login');
        }

        return $this->render('security/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{_locale}/login", name="security_login")
     * requirements={
     *  "_locale": "%app.locales%"
     * }
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    /**
     * @Route("/logout", name="security_logout")
     */
    public function logout()
    {
    }
}
