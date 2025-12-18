<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function findConversation($user1, $user2, $announcement)
    {
        return $this->createQueryBuilder('m')
            ->where('m.announcement = :announcement')
            ->andWhere(
                '(m.sender = :user1 AND m.receiver = :user2) OR (m.sender = :user2 AND m.receiver = :user1)'
            )
            ->setParameter('announcement', $announcement)
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findUserConversations($user)
    {
        return $this->createQueryBuilder('m')
            ->where('m.sender = :user OR m.receiver = :user')
            ->setParameter('user', $user)
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countUnreadMessages($user)
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.receiver = :user')
            ->andWhere('m.isRead = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
