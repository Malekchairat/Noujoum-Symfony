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
<<<<<<< Updated upstream
}
=======
    public function findTopByTicketCount(int $limit): array
    {
        return $this->createQueryBuilder('e')
            ->select('e, SUM(t.quantite) AS totalTickets')
            ->leftJoin('e.tickets', 't')
            ->groupBy('e.id')
            ->orderBy('totalTickets', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    
}
>>>>>>> Stashed changes
