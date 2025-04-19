<?php
// src/Repository/ReclamationRepository.php

namespace App\Repository;

use App\Entity\Reclamation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReclamationRepository extends ServiceEntityRepository
{
public function __construct(ManagerRegistry $registry)
{
parent::__construct($registry, Reclamation::class);
}

// Custom query to find all active reclamations
public function findActiveReclamations()
{
return $this->createQueryBuilder('r')
->andWhere('r.statut = :status')
->setParameter('status', 'active') // example status
->getQuery()
->getResult();
}


public function countByStatus(): array
    {
        return $this->createQueryBuilder('r')
            ->select('r.statut   AS statut')
            ->addSelect('COUNT(r.id) AS cnt')
            ->groupBy('r.statut')
            ->getQuery()
            ->getResult();  // yields [ ['statut'=>'OPEN','cnt'=>5], … ]
    }

    /**
     * Retourne un tableau ['LOW' => 2, 'MEDIUM' => 4, …]
     */
    public function countByPriority(): array
    {
        return $this->createQueryBuilder('r')
            ->select('r.priorite AS priorite')
            ->addSelect('COUNT(r.id) AS cnt')
            ->groupBy('r.priorite')
            ->getQuery()
            ->getResult();
    }

// Custom query to find reclamations by user ID
public function findByUserId($userId)
{
return $this->createQueryBuilder('r')
->andWhere('r.user_id = :userId')
->setParameter('userId', $userId)
->getQuery()
->getResult();
}
}