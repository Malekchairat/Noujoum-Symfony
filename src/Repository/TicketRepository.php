<?php

namespace App\Repository;

use App\Entity\Ticket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ticket>
<<<<<<< HEAD
=======
 *
 * @method Ticket|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ticket|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ticket[]    findAll()
 * @method Ticket[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
>>>>>>> origin/GestionCommandes
 */
class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

<<<<<<< HEAD
    public function findByTicketCount(int $limit = 5): array
    {
        return $this->createQueryBuilder('t')
            ->select('e as evenement', 'COUNT(t.id) as ticketCount')
            ->join('t.evenement', 'e')
            ->groupBy('e.id')
            ->orderBy('ticketCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
=======
    // Add custom methods here if needed
>>>>>>> origin/GestionCommandes
}
