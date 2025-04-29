<?php

namespace App\Controller;

use App\Entity\Feedback;
use App\Entity\Reclamation;
use App\Entity\User;
use App\Form\FeedbackType;
use App\Repository\FeedbackRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/feedback')]
class FeedbackController extends AbstractController
{
    #[Route('/', name: 'app_feedback_index', methods: ['GET'])]
    public function index(Request $request, FeedbackRepository $feedbackRepository, EntityManagerInterface $entityManager): Response
    {
        $userId = 1; // Example: Replace with actual user ID if needed

        // Get search and date filter parameters from the query string
        $search = $request->query->get('search');
        $dateFrom = $request->query->get('date_from');
        $dateTo = $request->query->get('date_to');

        // Use the repository method to fetch filtered feedbacks
        $feedbacks = $feedbackRepository->findFiltered($userId, $search, $dateFrom, $dateTo);

        // Create form for adding new feedback
        $blankFeedback = new Feedback();
        $feedbackForm = $this->createForm(FeedbackType::class, $blankFeedback);

        // Create edit forms for existing feedbacks
        $editForms = [];
        foreach ($feedbacks as $fb) {
            $form = $this->createForm(FeedbackType::class, $fb, [
                'action' => $this->generateUrl('app_feedback_edit', ['id' => $fb->getId()]),
                'method' => 'POST'
            ]);
            $editForms[$fb->getId()] = $form->createView();
        }

        // Fetch reclamations (adjust query if needed)
        $user = $entityManager->getRepository(User::class)->find(1); // 👈 user ID statique
        $reclamations = $entityManager->getRepository(Reclamation::class)->findBy([
            'user' => $user
        ]);

        // Return filtered feedbacks and other necessary data to the template
        return $this->render('feedback/index.html.twig', [
            'feedbacks' => $feedbacks,
            'reclamations' => $reclamations,
            'editForms' => $editForms,
            'feedbackForm' => $feedbackForm->createView(),
        ]);
    }

    // In FeedbackRepository





    #[Route('/new', name: 'app_feedback_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ): Response {
        $reclamationId = $request->request->get('reclamationId');

        // Early check for Reclamation
        $reclamation = $entityManager->getRepository(Reclamation::class)->find($reclamationId);
        if (!$reclamation) {
            return new Response('Reclamation not found', 404);
        }

        // Simulate user ID (replace with session user in real app)
        $userId = 1;

        // Check user authorization
        /*if ($reclamation->getUserId() !== $userId) {
            return new Response('Unauthorized operation', 403);
        }*/

        $feedback = new Feedback();
        // Set required values BEFORE form validation
        $feedback->setReclamationId($reclamation->getId());
        $feedback->setUtilisateurId($userId);
        $feedback->setDateFeedback(new \DateTime());

        $form = $this->createForm(FeedbackType::class, $feedback);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[] = $error->getMessage();
                }
                return new Response(json_encode(['errors' => $errors]), 422, ['Content-Type' => 'application/json']);
            }

            try {
                $entityManager->persist($feedback);
                $entityManager->flush();

                return new Response(json_encode(['message' => 'Feedback submitted successfully!']), 201, ['Content-Type' => 'application/json']);
            } catch (\Exception $e) {
                $logger->error('Error saving feedback: ' . $e->getMessage());
                return new Response('An error occurred while submitting feedback', 500);
            }
        }

        return new Response('Invalid request', 400);
    }


    #[Route('/{id}', name: 'app_feedback_show', methods: ['GET'])]
    public function show(Feedback $feedback): Response
    {
        return $this->render('feedback/show.html.twig', [
            'feedback' => $feedback,
        ]);
    }

    #[Route('/edit/{id}', name: 'app_feedback_edit', methods: ['POST'])]
    public function edit(Request $request, Feedback $feedback, EntityManagerInterface $em): JsonResponse
    {
        $form = $this->createForm(FeedbackType::class, $feedback);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);

        }

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
    }




    #[Route('/{id}', name: 'app_feedback_delete', methods: ['POST'])]
    public function delete(Request $request, Feedback $feedback, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($feedback);
        $entityManager->flush();
        return $this->redirectToRoute('app_feedback_index', [], Response::HTTP_SEE_OTHER);
    }
}