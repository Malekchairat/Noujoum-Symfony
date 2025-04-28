<?php

namespace App\Repository;


use App\Entity\Evenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    public function findTopByTicketCount($limit)
    {
        return $this->createQueryBuilder('e')
            ->select('e', 'COUNT(t.id) AS totalTickets')
            ->leftJoin('e.tickets', 't') // Assuming there's a relation with tickets
            ->groupBy('e.id')
            ->orderBy('totalTickets', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
    
}
