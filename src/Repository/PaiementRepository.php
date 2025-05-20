<?php

namespace App\Repository;

use App\Entity\Paiement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
/**
 * @extends ServiceEntityRepository<Paiement>
 */
class PaiementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Paiement::class);
    }

    public function findByStripePaymentIntentId(string $paymentIntentId): ?Paiement
    {
        return $this->findOneBy(['stripePaymentIntentId' => $paymentIntentId]);
    }

    public function findByStripeCheckoutSessionId(string $sessionId): ?Paiement
    {
        return $this->findOneBy(['stripeCheckoutSessionId' => $sessionId]);
    }

    public function findPaidPaymentForUserAndSortie(int $userId, int $sortieId): ?Paiement
    {
        return $this->createQueryBuilder('p')
            ->where('p.user = :userId')
            ->andWhere('p.sortie = :sortieId')
            ->andWhere('p.status = :status')
            ->setParameter('userId', $userId)
            ->setParameter('sortieId', $sortieId)
            ->setParameter('status', 'paid')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByUser(int $userId): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}