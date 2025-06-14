<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function findByEtatDifferentDe(string $etatLibelle)
    {
        return $this->createQueryBuilder('s')
            ->join('s.etat', 'e')
            ->where('e.libelle != :etat')
            ->setParameter('etat', $etatLibelle)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les sorties par états et critères - Méthode utilisée par SortieController
     */
    public function findSortiesByEtatsAndCriteria(array $etatsLibelles, array $criteria = []): array
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.etat', 'e')
            ->leftJoin('s.moniteur', 'm')
            ->leftJoin('s.typeSortie', 't')
            ->leftJoin('s.participants', 'p')
            ->leftJoin('s.membresFamilleInscrits', 'mf')
            ->leftJoin('s.niveauxAdmis', 'n')
            ->addSelect('e', 'm', 't', 'p', 'mf', 'n');

        // Filtrer par états
        if (!empty($etatsLibelles)) {
            $qb->andWhere('e.libelle IN (:etats)')
                ->setParameter('etats', $etatsLibelles);
        }

        // Filtrer par titre si critère fourni
        if (!empty($criteria['titre'])) {
            $qb->andWhere('s.titre LIKE :titre')
                ->setParameter('titre', '%' . $criteria['titre'] . '%');
        }

        // Filtrer par date si critère fourni
        if (!empty($criteria['dateMin'])) {
            $qb->andWhere('s.date >= :dateMin')
                ->setParameter('dateMin', $criteria['dateMin']);
        }

        if (!empty($criteria['dateMax'])) {
            $qb->andWhere('s.date <= :dateMax')
                ->setParameter('dateMax', $criteria['dateMax']);
        }

        // Filtrer par moniteur si critère fourni
        if (!empty($criteria['moniteur'])) {
            $qb->andWhere('s.moniteur = :moniteur')
                ->setParameter('moniteur', $criteria['moniteur']);
        }

        // Filtrer par type de sortie si critère fourni
        if (!empty($criteria['typeSortie'])) {
            $qb->andWhere('s.typeSortie = :typeSortie')
                ->setParameter('typeSortie', $criteria['typeSortie']);
        }

        // Filtrer par niveau si critère fourni
        if (!empty($criteria['niveau'])) {
            $qb->andWhere(':niveau MEMBER OF s.niveauxAdmis')
                ->setParameter('niveau', $criteria['niveau']);
        }

        // Tri
        if (!empty($criteria['tri'])) {
            if (strtolower($criteria['tri']) === 'asc') {
                $qb->orderBy('s.date', 'ASC');
            } elseif (strtolower($criteria['tri']) === 'desc') {
                $qb->orderBy('s.date', 'DESC');
            }
        } else {
            // Tri par défaut
            $qb->orderBy('s.date', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Recherche des sorties selon différents critères.
     *
     * @param array $criteria
     *      - titre: string|null
     *      - dateMin: \DateTimeInterface|null
     *      - dateMax: \DateTimeInterface|null
     *      - niveau: Niveau|null
     *      - etat: Etat|null
     * @param string|null $etatExclu
     * @return Sortie[]
     */
    public function findBySearchCriteria(array $criteria, ?string $etatExclu = null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.etat', 'e')
            ->leftJoin('s.niveauxAdmis', 'n')
            ->addSelect('e', 'n');

        if (!empty($criteria['titre'])) {
            $qb->andWhere('s.titre LIKE :titre')
                ->setParameter('titre', '%' . $criteria['titre'] . '%');
        }

        if (!empty($criteria['dateMin'])) {
            $qb->andWhere('s.date >= :dateMin')
                ->setParameter('dateMin', $criteria['dateMin']);
        }

        if (!empty($criteria['dateMax'])) {
            $qb->andWhere('s.date <= :dateMax')
                ->setParameter('dateMax', $criteria['dateMax']);
        }

        if (!empty($criteria['niveau'])) {
            $qb->andWhere(':niveau MEMBER OF s.niveauxAdmis')
                ->setParameter('niveau', $criteria['niveau']);
        }

        if (!empty($criteria['etat'])) {
            $qb->andWhere('s.etat = :etat')
                ->setParameter('etat', $criteria['etat']);
        }

        if ($etatExclu) {
            $qb->andWhere('e.libelle != :etatExclu')
                ->setParameter('etatExclu', $etatExclu);
        }

        if (!empty($criteria['tri']) && in_array(strtolower($criteria['tri']), ['asc', 'desc'])) {
            $qb->orderBy('s.date', strtoupper($criteria['tri']));
        } else {
            $qb->orderBy('s.date', 'ASC');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Trouve toutes les sorties avec leurs relations chargées (pour optimiser les performances)
     */
    public function findAllWithRelations(): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.etat', 'e')
            ->leftJoin('s.moniteur', 'm')
            ->leftJoin('s.typeSortie', 't')
            ->leftJoin('s.participants', 'p')
            ->leftJoin('s.membresFamilleInscrits', 'mf')
            ->addSelect('e', 'm', 't', 'p', 'mf')
            ->orderBy('s.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les sorties par état (version simplifiée)
     */
    public function findByEtats(array $etatsLibelles): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.etat', 'e')
            ->where('e.libelle IN (:etats)')
            ->setParameter('etats', $etatsLibelles)
            ->orderBy('s.date', 'ASC')
            ->getQuery()
            ->getResult();
    }
}