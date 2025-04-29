<?php
// src/Repository/FeedbackRepository.php

namespace App\Repository;

use App\Entity\Feedback;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FeedbackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Feedback::class);
    }

    // Custom query to find feedback by reclamation ID
    public function findByReclamationId($reclamationId)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.reclamation_id = :reclamationId')
            ->setParameter('reclamationId', $reclamationId)
            ->getQuery()
            ->getResult();
    }

    // Custom query to find feedback by user ID
    public function findByUtilisateurId($utilisateurId)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.utilisateur_id = :utilisateurId')
            ->setParameter('utilisateurId', $utilisateurId)
            ->getQuery()
            ->getResult();
    }

    // Custom query to find feedback with a certain rating (note)
    public function findByRating($rating)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.note = :rating')
            ->setParameter('rating', $rating)
            ->getQuery()
            ->getResult();
    }

    public function findFiltered($userId, ?string $search, ?string $dateFrom, ?string $dateTo): array
    {
        $qb = $this->createQueryBuilder('f')
            ->where('f.utilisateurId = :userId')
            ->setParameter('userId', $userId);

        if ($search) {
            $qb->andWhere('f.commentaire LIKE :search OR f.note LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($dateFrom) {
            $qb->andWhere('f.dateFeedback >= :dateFrom')
                ->setParameter('dateFrom', new \DateTime($dateFrom));
        }

        if ($dateTo) {
            $qb->andWhere('f.dateFeedback <= :dateTo')
                ->setParameter('dateTo', new \DateTime($dateTo . ' 23:59:59'));
        }

        return $qb->getQuery()->getResult();
    }
}
