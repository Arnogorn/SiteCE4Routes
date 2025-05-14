<?php
// src/Service/VerificationChevauchementService.php

namespace App\Services;

use App\Repository\CalendrierRepository;

class VerificationChevauchementService
{
    private $calendrierRepository;

    public function __construct(CalendrierRepository $calendrierRepository)
    {
        $this->calendrierRepository = $calendrierRepository;
    }

    public function verifierChevauchements(
        string $jour,
        string $heureDebut,
        string $heureFin,
        ?int $excludeId = null
    ): array {
        return $this->calendrierRepository->findOverlappingEvents($jour, $heureDebut, $heureFin, $excludeId);
    }

    public function verifierHorairesValides(string $heureDebut, string $heureFin): bool
    {
        // Convertir en minutes pour faciliter la comparaison
        list($heureDebutH, $heureDebutM) = explode(':', $heureDebut);
        list($heureFinH, $heureFinM) = explode(':', $heureFin);

        $debutMinutes = (int)$heureDebutH * 60 + (int)$heureDebutM;
        $finMinutes = (int)$heureFinH * 60 + (int)$heureFinM;

        return $finMinutes > $debutMinutes;
    }
}