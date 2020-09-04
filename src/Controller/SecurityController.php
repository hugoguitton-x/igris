<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Service\FileUploader;
use App\Form\RegistrationType;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UtilisateurRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{

    /**
     * @Route("/register", name="security_registration")
     */
    public function registration(
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordEncoderInterface $encoder,
        FileUploader $fileUploader,
        Security $security,
        MailerInterface $mailer
    ) {
        if ($security->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('home_page');
        }

        $utilisateur = new Utilisateur();

        $form = $this->createForm(RegistrationType::class, $utilisateur);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            die;
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

            $utilisateur->setActivationToken(md5(uniqid()));

            $manager->persist($utilisateur);
            $manager->flush();

            $message = (new TemplatedEmail())
                ->from(new Address('no-reply@igris.web.trycatchlearn.fr', 'Igris'))
                ->to($utilisateur->getEmail())
                ->cc('igris.site@gmail.com')
                ->subject('Activation de votre compte')
                ->htmlTemplate('emails/activation.html.twig')
                ->context(['token' => $utilisateur->getActivationToken()]);

            $mailer->send($message);

            return $this->redirectToRoute('security_login');
        }

        return $this->render('security/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/login", name="security_login")
     */
    public function login(Request $request, Security $security, AuthenticationUtils $authenticationUtils): Response
    {
        if ($security->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('home_page');
        }

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
        throw new \Exception('This should never be reached!');
    }

    /**
     * @Route("/activation/{token}", name="security_activation")
     */
    public function activation(string $token, EntityManagerInterface $manager, UtilisateurRepository $repo, Request $request, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $formAuthenticator)
    {
        $utilisateur = $repo->findOneBy(['activationToken' => $token]);

        if (!$utilisateur) {
            throw $this->createNotFoundException("This token/user doesn't exist.");
        }

        $utilisateur->setActivationToken(null);

        $manager->persist($utilisateur);
        $manager->flush();

        $this->addFlash('message', 'Your account has been activated successfully!');

        return $guardHandler->authenticateUserAndHandleSuccess(
            $utilisateur,
            $request,
            $formAuthenticator,
            'main' // firewall name in security.yaml
        );
    }


    /**
     * @Route("/mail/{template}", name="security_mail")
     */
    public function mail(string $template, MailerInterface $mailer)
    {
        $message = (new TemplatedEmail())
            ->from(new Address('no-reply@igris.web.trycatchlearn.fr', 'Igris'))
            ->to('nop@nop.com')
            ->cc('igris.site@gmail.com')
            ->subject('Activation de votre compte')
            ->htmlTemplate('emails/activation.html.twig')
            ->context(['token' => 'sdqsd']);

        $mailer->send($message);

        return $this->render('emails/index.html.twig', [
            'token' => 'nop',
        ]);
    }
}
