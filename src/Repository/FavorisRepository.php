<?php

namespace App\Repository;

use App\Entity\Favoris;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Favoris>
 */
class FavorisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Favoris::class);
    }

    //    /**
    //     * @return Favoris[] Returns an array of Favoris objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Favoris
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findByUser(User $user): array
{
    return $this->createQueryBuilder('f')
        ->andWhere('f.user = :user')
        ->setParameter('user', $user)
        ->getQuery()
        ->getResult();
}

public function findTopByLikes(int $limit): array
    {
        return $this->createQueryBuilder('f')
            ->select('p.nom AS productName, COUNT(f.idFavoris) AS totalLikes')
            ->join('f.produit', 'p')
            ->groupBy('p.id')
            ->orderBy('totalLikes', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
