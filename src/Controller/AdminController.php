<?php

// src/Controller/AdminController.php

namespace App\Controller;

use App\Entity\Feedback;
use App\Entity\Reclamation;
use App\Form\AdminReclamationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_reclamation_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $repo = $entityManager->getRepository(Reclamation::class);

        $qb = $repo->createQueryBuilder('r');

        if ($request->query->get('search')) {
            $search = $request->query->get('search');
            $qb->andWhere('r.titre LIKE :search OR r.userId LIKE :search')
                ->setParameter('search', "%$search%");
        }

        if ($request->query->get('statut')) {
            $qb->andWhere('r.statut = :statut')
                ->setParameter('statut', $request->query->get('statut'));
        }

        if ($request->query->get('priorite')) {
            $qb->andWhere('r.priorite = :priorite')
                ->setParameter('priorite', $request->query->get('priorite'));
        }

        $reclamations = $qb->orderBy('r.dateCreation', 'DESC')->getQuery()->getResult();

        // 📊 Collect stats
        $allRecs = $entityManager->getRepository(Reclamation::class)->findAll();

        $statusStats = ['OPEN' => 0, 'IN_PROGRESS' => 0, 'RESOLVED' => 0, 'CLOSED' => 0];
        $priorityStats = ['LOW' => 0, 'MEDIUM' => 0, 'HIGH' => 0];

        foreach ($allRecs as $rec) {
            $status = $rec->getStatut();
            $priority = $rec->getPriorite();

            if (!isset($statusStats[$status])) {
                $statusStats[$status] = 0;
            }
            if (!isset($priorityStats[$priority])) {
                $priorityStats[$priority] = 0;
            }

            $statusStats[$status]++;
            $priorityStats[$priority]++;
        }


        return $this->render('admin/index.html.twig', [
            'reclamations' => $reclamations,
            'feedbacks' => $entityManager->getRepository(Feedback::class)->findAll(),
            'statusStats' => $statusStats,
            'priorityStats' => $priorityStats,
        ]);
    }



    #[Route('/{id}/edit', name: 'admin_reclamation_edit', methods: ['POST'])]
    public function edit(
        Request $request,
        Reclamation $reclamation,
        EntityManagerInterface $entityManager
    ): Response {
        // Get form values from the modal
        $statut = $request->request->get('statut');
        $answer = $request->request->get('answer');

        // Validate the status manually if needed
        $validStatuses = ['OPEN', 'IN_PROGRESS', 'RESOLVED', 'CLOSED'];
        if (!in_array($statut, $validStatuses, true)) {
            $this->addFlash('danger', 'Invalid status value.');
            return $this->redirectToRoute('admin_reclamation_index');
        }

        // Update the entity
        $reclamation->setStatut($statut);
        $reclamation->setAnswer($answer);

        $entityManager->flush();

        $this->addFlash('success', 'Reclamation updated successfully.');
        return $this->redirectToRoute('admin_reclamation_index');
    }

}