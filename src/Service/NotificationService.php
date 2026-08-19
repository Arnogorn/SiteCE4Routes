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
        private string $senderEmail,
    )
    {
    }

    /**
     * Envoie un mail de désinscription pour un user OU un membre.
     *
     * @param User|null $userDestinataire L’utilisateur parent du membre, ou l’utilisateur lui-même.
     * @param MembreFamille|null $membre La personne désinscrite (null si c’est le user principal).
     * @param Sortie $sortie La sortie concernée.
     * @param float|null $amountRefunded Montant remboursé en euros.
     */
    public function sendDesinscriptionMail(
        ?User          $userDestinataire,
        ?MembreFamille $membre,
        Sortie         $sortie,
        ?int         $amountRefunded = null
    ): void
    {
        if (!$userDestinataire) {
            return;
        }

        // Définir l'adresse et le nom du désinscrit
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
                $sortie->getDate()->format('d/m/Y à H:i')
            )
        ];


        if ($amountRefunded !== null && $amountRefunded > 0) {
            $bodyLines[] = "";
            $bodyLines[] = sprintf(
                "Le remboursement d'une place (%s) sera traité dans les prochains jours.",
                number_format($amountRefunded, 2, ',', ' ') . ' €'
            );
        }

        $bodyLines[] = "";
        $bodyLines[] = "Si vous avez des questions, n'hésitez pas à nous contacter.";
        $bodyLines[] = "";
        $bodyLines[] = "Cordialement,";
        $bodyLines[] = "L'équipe des Écuries des 4 Routes";

        $email = (new Email())
            ->from($this->senderEmail)
            ->to($userDestinataire->getEmail())
            ->subject($subject)
            ->text(implode("\n", $bodyLines));

        sleep(2);

        $this->mailer->send($email);
    }

    /**
     * Envoie un mail lorsqu'un paiement confirmé n'a pas pu être honoré faute de
     * place disponible (course de concurrence entre deux paiements simultanés) et
     * a été intégralement remboursé.
     */
    public function sendPlacesIndisponiblesMail(User $user, Sortie $sortie, float $montantRembourseEuros): void
    {
        $subject = sprintf(
            "Sortie complète : votre paiement pour « %s » a été remboursé",
            $sortie->getTitre()
        );

        $bodyLines = [
            sprintf("Bonjour %s,", $user->getPrenom()),
            "",
            sprintf(
                "Votre paiement pour la sortie « %s » a bien été reçu, mais toutes les places ont été prises par d'autres inscriptions confirmées entre-temps. Nous sommes désolés, votre inscription n'a donc pas pu être finalisée.",
                $sortie->getTitre()
            ),
            "",
            sprintf(
                "Vous avez été intégralement remboursé (%s). Le remboursement sera visible sur votre compte dans les prochains jours.",
                number_format($montantRembourseEuros, 2, ',', ' ') . ' €'
            ),
            "",
            "N'hésitez pas à consulter nos autres activités disponibles sur le site.",
            "",
            "Toutes nos excuses pour la gêne occasionnée,",
            "L'équipe des Écuries des 4 Routes",
        ];

        $email = (new Email())
            ->from($this->senderEmail)
            ->to($user->getEmail())
            ->subject($subject)
            ->text(implode("\n", $bodyLines));

        $this->mailer->send($email);
    }
}