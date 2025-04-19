<?php
namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    /**
     * Récupère tous les produits avec jointures si nécessaire
     */
    public function findAllProducts(): array
{
    return $this->createQueryBuilder('p')
        ->orderBy('p.id', 'ASC')
        ->getQuery()
        ->getResult();
}


    /**
     * Version alternative plus simple
     */
    public function findAllVisible(): array
    {
        return $this->findBy([], ['id' => 'ASC']);
    }

    // src/Repository/ProduitRepository.php

public function findBySearchAndSort(?string $search, ?string $sort): array
{
    $qb = $this->createQueryBuilder('p');

    if ($search) {
        $qb->andWhere('LOWER(p.nom) LIKE :search')
           ->setParameter('search', '%' . strtolower($search) . '%');
    }

    if ($sort === 'asc') {
        $qb->orderBy('p.prix', 'ASC');
    } elseif ($sort === 'desc') {
        $qb->orderBy('p.prix', 'DESC');
    }

    return $qb->getQuery()->getResult();
}

}