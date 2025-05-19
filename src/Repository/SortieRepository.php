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
    //    /**
    //     * @return Sortie[] Returns an array of Sortie objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Sortie
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    /**
     * Trouve les sorties par états et critères avec une seule requête optimisée
     */
    /**
     * Trouve les sorties par états et critères avec une seule requête optimisée
     */
    public function findSortiesByEtatsAndCriteria(array $etatsLibelles, array $criteria = [])
    {
        $qb = $this->createQueryBuilder('s')
            ->select('s, e, ts, m') // Relations immédiates
            ->join('s.etat', 'e')
            ->leftJoin('s.typeSortie', 'ts')
            ->leftJoin('s.moniteur', 'm');

        // Jointures pour les collections avec fetch EAGER
        $qb->leftJoin('s.participants', 'p')
            ->addSelect('p')
            ->leftJoin('s.membresFamilleInscrits', 'mf')
            ->addSelect('mf')
            ->leftJoin('s.niveauxAdmis', 'na')
            ->addSelect('na');

        // Filtrage par états
        $qb->where('e.libelle IN (:etats)')
            ->setParameter('etats', $etatsLibelles);

        // Autres filtres
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

        // Tri
        if (!empty($criteria['tri']) && in_array($criteria['tri'], ['asc', 'desc'])) {
            $qb->orderBy('s.date', $criteria['tri']);
        } else {
            $qb->orderBy('s.date', 'desc');
        }

        return $qb->getQuery()
            ->enableResultCache(60)
            ->getResult();
    }
}