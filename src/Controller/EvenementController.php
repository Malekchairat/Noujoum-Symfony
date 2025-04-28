<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Repository\EvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/evenement')]
class EvenementController extends AbstractController
{
    #[Route('/', name: 'evenement_index', methods: ['GET'])]
    public function index(EvenementRepository $evenementRepository, Request $request): Response
    {
        $selectedType = $request->query->get('type', 'Tous');
        $searchQuery = $request->query->get('q', '');

        // Build a query that filters by type (if not "Tous") and performs an advanced search
        $qb = $evenementRepository->createQueryBuilder('e');

        if ($selectedType !== 'Tous') {
            $qb->andWhere('e.typeE = :type')
               ->setParameter('type', $selectedType);
        }

        if ($searchQuery) {
            $qb->andWhere('
                e.titre LIKE :q OR 
                e.description LIKE :q OR 
                e.lieu LIKE :q OR 
                e.artiste LIKE :q
            ')
            ->setParameter('q', '%' . $searchQuery . '%');
        }

        $evenements = $qb->getQuery()->getResult();

        return $this->render('evenement/index.html.twig', [
            'evenements'    => $evenements,
            'selectedType'  => $selectedType,
            'searchQuery'   => $searchQuery,
        ]);
    }

    

 

}
