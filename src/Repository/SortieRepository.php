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
}