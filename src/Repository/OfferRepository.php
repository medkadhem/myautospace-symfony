<?php

namespace App\Repository;

use App\Entity\Offer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Offer>
 */
class OfferRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Offer::class);
    }

    /**
     * Find offers for a collection of announcements
     */
    public function findOffersForAnnouncements(Collection $announcements): array
    {
        if ($announcements->isEmpty()) {
            return [];
        }

        $announcementIds = [];
        foreach ($announcements as $announcement) {
            $announcementIds[] = $announcement->getId();
        }

        return $this->createQueryBuilder('o')
            ->innerJoin('o.announcement', 'a')
            ->andWhere('a.id IN (:announcementIds)')
            ->setParameter('announcementIds', $announcementIds)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
