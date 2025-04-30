<?php

// src/Controller/AdminController.php

namespace App\Controller;

use App\Entity\Feedback;
use App\Entity\Reclamation;
use App\Entity\User;
use App\Form\AdminReclamationType;
use App\Repository\ReclamationRepository;
use App\Service\SmsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository; 

#[Route('/admin')]
class AdminController extends AbstractController
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    #[Route('/', name: 'admin_reclamation_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $repo = $entityManager->getRepository(Reclamation::class);

        $qb = $repo->createQueryBuilder('r');

        if ($request->query->get('search')) {
            $search = $request->query->get('search');
            $qb->join('r.user', 'u')
                ->andWhere('r.titre LIKE :search OR u.nom LIKE :search OR u.prenom LIKE :search OR u.email LIKE :search')
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

        // 📊 Collect feedback stats
        $feedbackStats = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        $feedbacks = $entityManager->getRepository(Feedback::class)->findAll();

        foreach ($feedbacks as $feedback) {
            $rating = $feedback->getNote();
            if (array_key_exists($rating, $feedbackStats)) {
                $feedbackStats[$rating]++;
            }
        }

        // 👥 Top 5 Users with the Most Reclamations
        $userStats = [];

        foreach ($reclamations as $rec) {
            $user = $rec->getUser(); // Get the User entity
            $userName = $user ? $user->getNom() . ' ' . $user->getPrenom() : "Unknown User";

            if (!isset($userStats[$userName])) {
                $userStats[$userName] = 0;
            }

            $userStats[$userName]++;
        }

        // Sort descending by count
        arsort($userStats);

        // Get Top 5 Users
        $topUsers = array_slice($userStats, 0, 5, true);

        // Prepare data for the chart or display
        $topUserNames = array_keys($topUsers);
        $topUserCounts = array_values($topUsers);

        $totalReclamations = count($reclamations);
        $totalUsers = count(array_unique(array_map(fn($rec) => $rec->getUser() ? $rec->getUser()->getIdUser() : null, $reclamations)));
        $totalFeedbacks = count($feedbacks);

        $monthlyStats = [];
        foreach ($reclamations as $rec) {
            $monthYear = $rec->getDateCreation()->format('Y-m');
            if (!isset($monthlyStats[$monthYear])) {
                $monthlyStats[$monthYear] = 0;
            }
            $monthlyStats[$monthYear]++;
        }

        ksort($monthlyStats);

        return $this->render('admin/index.html.twig', [
            'reclamations' => $reclamations,
            'feedbacks' => $feedbacks,
            'statusStats' => $statusStats,
            'priorityStats' => $priorityStats,
            'feedbackStats' => $feedbackStats,
            'monthlyStats' => $monthlyStats,
            'topUserNames' => $topUserNames,
            'topUserCounts' => $topUserCounts,
            'totalReclamations' => $totalReclamations,
            'totalUsers' => $totalUsers,
            'totalFeedbacks' => $totalFeedbacks,
        ]);
    }

    #[Route('/reclamations/filter', name: 'admin_reclamations_filter')]
    public function filter(Request $request, ReclamationRepository $reclamationRepository): Response
    {
        $search = $request->query->get('search');
        $statut = $request->query->get('statut');
        $priorite = $request->query->get('priorite');

        $reclamations = $reclamationRepository->findByFilters($search, $statut, $priorite);

        return $this->render('admin/reclamations/_reclamations_list.html.twig', [
            'reclamations' => $reclamations
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_reclamation_edit', methods: ['POST'])]
    public function edit(
        Request $request,
        Reclamation $reclamation,
        EntityManagerInterface $entityManager
    ): Response {
        $statut = $request->request->get('statut');
        $answer = $request->request->get('answer');

        $validStatuses = ['OPEN', 'IN_PROGRESS', 'RESOLVED', 'CLOSED'];
        if (!in_array($statut, $validStatuses, true)) {
            $this->addFlash('danger', 'Invalid status value.');
            return $this->redirectToRoute('admin_reclamation_index');
        }

        $reclamation->setStatut($statut);
        $reclamation->setAnswer($answer);

        $entityManager->flush();

        $smsService = new SmsService(
            'AC6be49cb91b9e3630bc17ef0093eb97b4',
            '5e1cb6a2ea1ecf2ae1d07e52e6aeab02',
            '+15632028564'
        );

        $userPhone = "+21654410619";
        $message = "Bonjour, votre réclamation a été traitée. Réponse : " . $reclamation->getAnswer();
        $smsService->sendSms($userPhone, $message);

        $this->addFlash('success', 'Reclamation updated successfully.');
        return $this->redirectToRoute('admin_reclamation_index');
    }
}