<?php

namespace App\Repository;

use App\Entity\HistoriquePaiement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class HistoriquePaiementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoriquePaiement::class);
    }

    /**
     * Recherche avec filtres pour l'admin
     */
    public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('h')
            ->leftJoin('h.utilisateur', 'u')
            ->addSelect('u')
            ->leftJoin('h.sortie', 's')
            ->addSelect('s')
            ->orderBy('h.date', 'DESC');

        // Filtre par email utilisateur
        if (!empty($filters['email'])) {
            $qb->andWhere('u.email LIKE :email')
                ->setParameter('email', '%' . $filters['email'] . '%');
        }

        // Filtre par type de paiement
        if (!empty($filters['type'])) {
            $qb->andWhere('h.type = :type')
                ->setParameter('type', $filters['type']);
        }

        // Filtre par nom/prénom utilisateur
        if (!empty($filters['nom'])) {
            $qb->andWhere('(u.nom LIKE :nom OR u.prenom LIKE :nom)')
                ->setParameter('nom', '%' . $filters['nom'] . '%');
        }

        // Filtre par sortie
        if (!empty($filters['sortie'])) {
            $qb->andWhere('s.titre LIKE :sortie')
                ->setParameter('sortie', '%' . $filters['sortie'] . '%');
        }

        // Filtre par date (période)
        if (!empty($filters['date_debut'])) {
            try {
                $dateDebut = new \DateTimeImmutable($filters['date_debut']);
                $qb->andWhere('h.date >= :date_debut')
                    ->setParameter('date_debut', $dateDebut);
            } catch (\Exception $e) {
                // Ignorer si la date est invalide
            }
        }

        if (!empty($filters['date_fin'])) {
            try {
                $dateFin = new \DateTimeImmutable($filters['date_fin']);
                $qb->andWhere('h.date <= :date_fin')
                    ->setParameter('date_fin', $dateFin);
            } catch (\Exception $e) {
                // Ignorer si la date est invalide
            }
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Statistiques pour le dashboard admin
     */
    public function getStatistiques(): array
    {
        // Total des paiements
        $total = $this->count([]);

        // Paiements réussis
        $reussis = $this->count(['type' => 'paiement_reussi']);

        // Paiements annulés
        $annules = $this->count(['type' => 'paiement_annule']);

        // Calcul du taux de réussite
        $tauxReussite = $total > 0 ? round(($reussis / $total) * 100, 2) : 0;

        return [
            'total' => $total,
            'reussis' => $reussis,
            'annules' => $annules,
            'taux_reussite' => $tauxReussite
        ];
    }

    /**
     * Recherche par utilisateur pour la page utilisateur
     */
    public function findByUser(\App\Entity\User $user): array
    {
        return $this->findBy(
            ['utilisateur' => $user],
            ['date' => 'DESC']
        );
    }

    /**
     * Recherche des paiements récents (pour un dashboard)
     */
    public function findRecent(int $limit = 10): array
    {
        return $this->createQueryBuilder('h')
            ->leftJoin('h.utilisateur', 'u')
            ->addSelect('u')
            ->leftJoin('h.sortie', 's')
            ->addSelect('s')
            ->orderBy('h.date', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques par type de paiement
     */
    public function getStatistiquesByType(): array
    {
        $qb = $this->createQueryBuilder('h')
            ->select('h.type, COUNT(h.id) as count')
            ->groupBy('h.type')
            ->orderBy('count', 'DESC');

        $results = $qb->getQuery()->getResult();

        $stats = [];
        foreach ($results as $result) {
            $stats[$result['type']] = $result['count'];
        }

        return $stats;
    }
}