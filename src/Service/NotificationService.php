<?php

namespace App\Service;

use App\Entity\MembreFamille;
use App\Entity\Sortie;
use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class NotificationService
{

    public function __construct(
        private MailerInterface $mailer,
        private string          $senderEmail
    )
    {
    }

    /**
     * Envoie un mail de désinscription pour un user OU un membre.
     *
     * @param User|null $userDestinataire L’utilisateur parent du membre, ou l’utilisateur lui-même.
     * @param MembreFamille|null $membre La personne désinscrite (null si c’est le user principal).
     * @param Sortie $sortie La sortie concernée.
     * @param float|null $amountRefunded Montant remboursé en euros (facultatif).
     */
    public function sendDesinscriptionMail(
        ?User          $userDestinataire,
        ?MembreFamille $membre,
        Sortie         $sortie,
        ?float         $amountRefunded = null
    ): void
    {
        if (!$userDestinataire) {
            // Sans destinataire, on ne fait rien
            return;
        }

        // Définir l’adresse et le nom du désinscrit
        if ($membre) {
            $personName = $membre->getPrenom() . ' ' . $membre->getNom();
        } else {
            $personName = $userDestinataire->getPrenom() . ' ' . $userDestinataire->getNom();
        }

        $subject = sprintf(
            "Désinscription de %s à la sortie « %s »",
            $personName,
            $sortie->getTitre()
        );

        $bodyLines = [
            sprintf("Bonjour %s,", $userDestinataire->getPrenom()),
            "",
            sprintf("%s a été désinscrit(e) de la sortie « %s » du %s.",
                $personName,
                $sortie->getTitre(),
                $sortie->getDate()->format('d/m/Y H:i')
            )
        ];

        if ($amountRefunded !== null) {
            $bodyLines[] = "";
            $bodyLines[] = sprintf(
                "Le remboursement d'une place (%.2f €) a bien été effectué.",
                $amountRefunded
            );
        }

        $bodyLines[] = "";
        $bodyLines[] = "Si vous avez des questions, n'hésitez pas à nous contacter.";
        $bodyLines[] = "";
        $bodyLines[] = "Cordialement,";
        $bodyLines[] = "Les Écuries des 4 Routes";

        $email = (new Email())
            ->from($this->senderEmail)
            ->to($userDestinataire->getEmail())
            ->subject($subject)
            ->text(implode("\n", $bodyLines));

        $this->mailer->send($email);

    }
}