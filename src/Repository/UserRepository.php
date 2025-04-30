<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function loadUserByIdentifier(string $identifier): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :email')
            ->setParameter('email', $identifier)
            ->getQuery()
            ->getOneOrNullResult();
    }
// In UserRepository.php
public function getAverageSessionTime(): float
{
    return $this->createQueryBuilder('u')
        ->select('AVG(u.totalSessionTime) as avg_time')
        ->getQuery()
        ->getSingleScalarResult();
}

public function getTotalUsageStatistics(): array
    {
        $qb = $this->createQueryBuilder('u')
            ->select(
                'SUM(u.totalSessionTime) as total_time',
                'AVG(u.totalSessionTime) as avg_time',
                'COUNT(u.id_user) as user_count',
                'SUM(u.loginCount) as total_logins'
            );

        return $qb->getQuery()->getSingleResult();
    }

    public function findTopUsers(int $limit = 5): array
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.totalSessionTime', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    // You can uncomment and modify the methods below if you need them.
    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
