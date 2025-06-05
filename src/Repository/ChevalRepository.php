<?php

namespace App\Repository;

use App\Entity\Cheval;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cheval>
 */
class ChevalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cheval::class);
    }
    /**
     * Recherche avec JOIN pour éviter les requêtes à chaque fois
     *
     * @param string|null $search
     * @return Cheval[]
     */
    public function findAllWithRelations(?string $search = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.user', 'u')
            ->leftJoin('c.sexe', 's')
            ->addSelect('u', 's')
            ->orderBy('c.nom', 'ASC');

        if ($search) {
            $qb->where('c.nom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Compte les chevaux par statut (pour les statistiques admin uniquement)
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select([
                'COUNT(c.id) as total',
                'SUM(CASE WHEN c.appartientEcurie = true THEN 1 ELSE 0 END) as chevaux_club',
                'SUM(CASE WHEN c.aVendre = true AND c.vendu = false THEN 1 ELSE 0 END) as chevaux_vente',
                'SUM(CASE WHEN c.vendu = true THEN 1 ELSE 0 END) as chevaux_vendus'
            ]);

        return $qb->getQuery()->getSingleResult();
    }
}
