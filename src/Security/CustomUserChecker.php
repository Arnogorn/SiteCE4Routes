<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class CustomUserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        // Vérifier si l'email est vérifié
        if (!$user->isVerified()) {
            throw new CustomUserMessageAccountStatusException(
                'Votre adresse email n\'a pas encore été vérifiée.'
            );
        }

        // Vérifier si l'utilisateur est actif
        if (!$user->isActif()) {
            throw new CustomUserMessageAccountStatusException('Votre compte est désactivé.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Aucune vérification post-authentification nécessaire
    }
}