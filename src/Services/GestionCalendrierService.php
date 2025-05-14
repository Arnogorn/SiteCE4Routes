<?php
// src/Service/GestionCalendrierService.php

namespace App\Services;

use App\Entity\Calendrier;
use Doctrine\ORM\EntityManagerInterface;

class GestionCalendrierService
{
    private $entityManager;
    private $verificationService;

    public function __construct(
        EntityManagerInterface $entityManager,
        VerificationChevauchementService $verificationService
    ) {
        $this->entityManager = $entityManager;
        $this->verificationService = $verificationService;
    }

    public function initialiserNouveauCalendrier(?string $jour = null, ?string $heureDebut = null): Calendrier
    {
        $calendrier = new Calendrier();

        // Définir les valeurs par défaut si elles sont fournies
        if ($jour) {
            $calendrier->setJour($jour);
        }

        if ($heureDebut) {
            $calendrier->setHeureDebut($heureDebut);

            // Calculer l'heure de fin par défaut (1 heure plus tard)
            list($h, $m) = explode(':', $heureDebut);
            $hFin = (int)$h + 1;
            if ($hFin <= 21) {
                $calendrier->setHeureFin("$hFin:30");
            } else {
                $calendrier->setHeureFin("21:30"); // Maximum
            }
        }

        return $calendrier;
    }

    public function validerEtSauvegarder(Calendrier $calendrier): array
    {
        $resultat = ['success' => false, 'message' => ''];

        // Vérifier que l'heure de fin est après l'heure de début
        if (!$this->verificationService->verifierHorairesValides(
            $calendrier->getHeureDebut(),
            $calendrier->getHeureFin()
        )) {
            $resultat['message'] = 'L\'heure de fin doit être après l\'heure de début.';
            return $resultat;
        }

        // Vérifier les chevauchements
        $chevauchements = $this->verificationService->verifierChevauchements(
            $calendrier->getJour(),
            $calendrier->getHeureDebut(),
            $calendrier->getHeureFin(),
            $calendrier->getId()
        );

        if (count($chevauchements) > 0) {
            $resultat['message'] = 'Un événement existe déjà sur ce créneau horaire.';
            return $resultat;
        }

        // Tout est valide, on peut sauvegarder
        $estNouveau = $calendrier->getId() === null;

        if ($estNouveau) {
            $this->entityManager->persist($calendrier);
        }

        $this->entityManager->flush();

        $resultat['success'] = true;
        $resultat['message'] = $estNouveau ? 'Événement ajouté avec succès.' : 'Événement mis à jour avec succès.';

        return $resultat;
    }

    public function supprimer(Calendrier $calendrier): void
    {
        $this->entityManager->remove($calendrier);
        $this->entityManager->flush();
    }
}