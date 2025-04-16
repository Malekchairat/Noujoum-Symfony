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

// Custom query to find reclamations by priority
public function findByPriority($priority)
{
return $this->createQueryBuilder('r')
->andWhere('r.priorite = :priority')
->setParameter('priority', $priority)
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
