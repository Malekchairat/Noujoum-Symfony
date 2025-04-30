<?php

namespace App\Controller;

use App\Entity\Feedback;
use App\Entity\Reclamation;
use App\Entity\User;
use App\Form\FeedbackType;
use App\Form\ReclamationType;
use App\Form\UserReclamationType;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;


#[Route('/reclamation')]
class ReclamationController extends AbstractController
{


    #[Route('/', name: 'app_reclamation_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, Request $request, PaginatorInterface $paginator): Response
    {
        $userId = 1;

        $search = $request->query->get('search');
        $statusFilter = $request->query->get('status');

        $user = $entityManager->getRepository(User::class)->find($userId);

        $qb = $entityManager->getRepository(Reclamation::class)->createQueryBuilder('r');
        $qb->where('r.user = :user')
            ->setParameter('user', $user);


        if ($search) {
            $qb->andWhere('r.titre LIKE :search OR r.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($statusFilter) {
            $qb->andWhere('r.statut = :status')
                ->setParameter('status', $statusFilter);
        }

        $reclamations = $qb->orderBy('r.dateCreation', 'DESC')->getQuery()->getResult();

        $feedbackRepository = $entityManager->getRepository(Feedback::class);
        $feedbackForm = $this->createForm(FeedbackType::class, new Feedback(), [
            'action' => $this->generateUrl('app_feedback_new')
        ]);

        $pagination = $paginator->paginate(
            $qb,                         // Query or QueryBuilder
            $request->query->getInt('page', 1), // Page number (default to 1)
            5                            // Limit per page
        );

        return $this->render('reclamation/index.html.twig', [
            'pagination' => $pagination,
            'reclamations' => $reclamations,
            'feedbacks' => $feedbackRepository->findAll(),
            'feedbackForm' => $feedbackForm->createView(),
            'search' => $search,
            'statusFilter' => $statusFilter
        ]);
    }
    #[Route('/test-mail', name: 'test_mail')]
    public function testMail(EmailService $emailService, EntityManagerInterface $em): Response
    {
        $reclamation = $em->getRepository(Reclamation::class)->findOneBy([]);

        if (!$reclamation) {
            return new Response('No reclamation found for test.');
        }

        $emailService->sendReclamationNotification(
            'montassar.bhz@esprit.tn',
            'Test Email - Réclamation',
            $reclamation
        );

        return new Response('Email sent!');
    }




    #[Route('/new', name: 'app_reclamation_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        HttpClientInterface $httpClient,
        EmailService $emailService
    ): Response {
        $reclamation = new Reclamation();
        $reclamation->setDateCreation(new \DateTime());

        // 🟢 Use dynamic user ID if you have security, else stick to static one
        $user = $entityManager->getRepository(User::class)->find(1);
        $reclamation->setUser($user);
        // Replace with the logged-in user ID when available

        // Set default values
        $reclamation->setStatut('OPEN');
        $reclamation->setPriorite('MEDIUM'); // Default, can be overridden by Gemini
        $reclamation->setAnswer(''); // Optional: prevent DB issues if null not allowed

        $form = $this->createForm(UserReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // 🧠 Use Gemini API to classify priority
                $apiResponse = $httpClient->request('POST',
                    'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=AIzaSyCWYXrEGlHeFdgJ-HEgeP-d-53vxgsxCso',
                    [
                        'json' => [
                            'contents' => [
                                'parts' => [
                                    [
                                        'text' => "Analyze this support request and classify its priority (LOW, MEDIUM, HIGH, CRITICAL) based on urgency. Only respond with the priority level in uppercase. Request: " . $reclamation->getDescription()
                                    ]
                                ]
                            ]
                        ],
                        'timeout' => 5
                    ]
                );

                $priority = $this->parseGeminiResponse($apiResponse->toArray());
                $reclamation->setPriorite($priority ?? 'MEDIUM');
            } catch (TransportExceptionInterface|\Exception $e) {
                $this->addFlash('warning', 'Priority auto-detection failed. Using default priority.');
                $reclamation->setPriorite('MEDIUM');
            }

            $entityManager->persist($reclamation);
            $entityManager->flush();

            $emailService->sendReclamationNotification(
                'mahmoud.touil@esprit.tn',
                'Nouvelle Réclamation',
                $reclamation
            );

            $this->addFlash('success', 'Your reclamation has been submitted successfully!');
            return $this->redirectToRoute('app_reclamation_index');
        }

        return $this->render('reclamation/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    private function parseGeminiResponse(array $response): ?string
    {
        try {
            $text = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $priority = strtoupper(trim($text));
            return in_array($priority, ['LOW', 'MEDIUM', 'HIGH', 'CRITICAL']) ? $priority : null;
        } catch (\Exception $e) {
            return null;
        }
    }


    #[Route('/{id}', name: 'app_reclamation_show', methods: ['GET'])]
    public function show(Reclamation $reclamation): Response
    {
        return $this->render('reclamation/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Reclamation $reclamation,
        EntityManagerInterface $entityManager,
        HttpClientInterface $httpClient
    ): Response {
        // Add security check here (e.g., isGranted or owner check)
        // For testing purposes, we're skipping authentication

        $form = $this->createForm(UserReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Call Gemini API with the updated description
                $apiResponse = $httpClient->request('POST',
                    'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=AIzaSyCWYXrEGlHeFdgJ-HEgeP-d-53vxgsxCso',
                    [
                        'json' => [
                            'contents' => [
                                'parts' => [
                                    [
                                        'text' => "Analyze this support request and classify its priority (LOW, MEDIUM, HIGH, CRITICAL) based on urgency. Only respond with the priority level in uppercase. Request: " . $reclamation->getDescription()
                                    ]
                                ]
                            ]
                        ],
                        'timeout' => 5
                    ]
                );

                $priority = $this->parseGeminiResponse($apiResponse->toArray());
                $reclamation->setPriorite($priority ?? 'MEDIUM');
            } catch (TransportExceptionInterface|\Exception $e) {
                $this->addFlash('warning', 'Priority auto-update failed. Keeping previous priority.');
            }

            $entityManager->flush();

            $this->addFlash('success', 'Reclamation updated successfully!');
            return $this->redirectToRoute('app_reclamation_index');
        }

        return $this->render('reclamation/edit.html.twig', [
            'form' => $form->createView(),
            'reclamation' => $reclamation,
        ]);
    }


    #[Route('/{id}', name: 'app_reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reclamation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reclamation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
    }
}

