<?php

namespace App\Repository;

use App\Entity\Offer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OfferRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Offer::class);
    }

    public function findByAnnouncement($announcement)
    {
        return $this->createQueryBuilder('o')
            ->where('o.announcement = :announcement')
            ->setParameter('announcement', $announcement)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByBuyer($buyer)
    {
        return $this->createQueryBuilder('o')
            ->where('o.buyer = :buyer')
            ->setParameter('buyer', $buyer)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findBySeller($seller)
    {
        return $this->createQueryBuilder('o')
            ->join('o.announcement', 'a')
            ->where('a.vendor = :seller')
            ->setParameter('seller', $seller)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
