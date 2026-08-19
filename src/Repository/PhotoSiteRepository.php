<?php

namespace App\Repository;

use App\Entity\PhotoSite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PhotoSite>
 */
class PhotoSiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PhotoSite::class);
    }

    public function findOneBySlug(string $slug): ?PhotoSite
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    /**
     * @return array<string, PhotoSite>
     */
    public function findAllIndexedBySlug(): array
    {
        $indexed = [];
        foreach ($this->findAll() as $photoSite) {
            $indexed[$photoSite->getSlug()] = $photoSite;
        }

        return $indexed;
    }
}
