<?php

namespace App\Repository;

use App\Entity\Calendrier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CalendrierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Calendrier::class);
    }

    /**
     * Trouve les événements qui chevauchent un créneau donné
     */
    /**
     * Trouve les événements qui chevauchent un créneau donné
     */
    /**
     * Trouve les événements qui chevauchent un créneau donné
     */
    public function findOverlappingEvents(string $jour, string $heureDebut, string $heureFin, ?int $excludeId = null): array
    {
        // Convertir les heures en minutes pour faciliter la comparaison
        list($heureDebutH, $heureDebutM) = explode(':', $heureDebut);
        list($heureFinH, $heureFinM) = explode(':', $heureFin);

        $debutMinutes = (int)$heureDebutH * 60 + (int)$heureDebutM;
        $finMinutes = (int)$heureFinH * 60 + (int)$heureFinM;

        $qb = $this->createQueryBuilder('c')
            ->where('c.jour = :jour')
            ->setParameter('jour', $jour);

        // Convertir les heures en minutes pour chaque événement et vérifier les chevauchements
        $qb->andWhere('
        (
            (SUBSTRING(c.heureDebut, 1, 2) * 60 + SUBSTRING(c.heureDebut, 4, 2) < :finMinutes) 
            AND 
            (SUBSTRING(c.heureFin, 1, 2) * 60 + SUBSTRING(c.heureFin, 4, 2) > :debutMinutes)
        )
    ')
            ->setParameter('debutMinutes', $debutMinutes)
            ->setParameter('finMinutes', $finMinutes);

        if ($excludeId !== null) {
            $qb->andWhere('c.id != :id')
                ->setParameter('id', $excludeId);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Récupère tous les événements pour un jour spécifique
     */
    public function findByJour(string $jour): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.jour = :jour')
            ->setParameter('jour', $jour)
            ->orderBy('c.heureDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }
}