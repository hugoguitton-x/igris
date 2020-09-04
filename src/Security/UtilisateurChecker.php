<?php
namespace App\Security;

use App\Entity\Utilisateur;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UtilisateurChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $utilisateur)
    {
        if (!$utilisateur instanceof Utilisateur) {
            return;
        }

        if ($utilisateur->getActivationToken()) {
            throw new CustomUserMessageAuthenticationException(
                'Inactive account cannot log in'
            );
        }
    }

    public function checkPostAuth(UserInterface $utilisateur)
    {
        $this->checkPreAuth($utilisateur);
    }
}