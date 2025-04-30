<?php

namespace App\Repository;

use App\Entity\Promotion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PromotionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Promotion::class);
    }

    

    // Remise moyenne par produit
    public function averageDiscountByProduct(): array
    {
        return $this->createQueryBuilder('p')
            ->select('pr.nom AS produit, AVG(p.pourcentage) AS pourcentage_moyen')
            ->join('p.produit', 'pr')
            ->groupBy('pr.id')
            ->getQuery()
            ->getResult();
    }

    // Promotions actives vs expirées
    public function countActiveVsExpiredPromotions(): array
    {
        $today = new \DateTime();
        return [
            'actives' => $this->createQueryBuilder('p')
                ->select('COUNT(p.id)')
                ->where('p.expiration >= :today')
                ->setParameter('today', $today)
                ->getQuery()
                ->getSingleScalarResult(),
            'expirees' => $this->createQueryBuilder('p')
                ->select('COUNT(p.id)')
                ->where('p.expiration < :today')
                ->setParameter('today', $today)
                ->getQuery()
                ->getSingleScalarResult(),
        ];
    }

    // Promotions par catégorie de produit
    public function countPromotionsByCategory(): array
    {
        return $this->createQueryBuilder('p')
            ->select('pr.categorie AS categorie, COUNT(p.id) AS nombre_promotions')
            ->join('p.produit', 'pr')
            ->groupBy('pr.categorie')
            ->getQuery()
            ->getResult();
    }
    


public function countPromotionsByProduct(int $produitId): int
{
    return (int) $this->createQueryBuilder('p')
        ->select('COUNT(p.id)')
        ->where('p.produit = :produitId')
        ->setParameter('produitId', $produitId)
        ->getQuery()
        ->getSingleScalarResult();
}}