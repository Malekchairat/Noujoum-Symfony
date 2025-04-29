<?php

namespace App\Controller\Api;

use App\Entity\Commande;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class OrderConfirmationController extends AbstractController
{
    private $emailService;
    private $security;
    private $entityManager;

    public function __construct(
        EmailService $emailService,
        Security $security,
        EntityManagerInterface $entityManager
    ) {
        $this->emailService = $emailService;
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    #[Route('/api/order/confirm/{id}', name: 'app_order_confirm', methods: ['POST'])]
    public function confirmOrder(Commande $commande, EmailService $emailService): JsonResponse
    {
        // Get the currently logged-in user
        $user = $this->getUser();
        
        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'User not authenticated'], 401);
        }
    
        // Debug: Check which user is logged in
        dump($user->getEmail()); // This will show in your Symfony profiler
        
        try {
            $orderDetails = [
                'products_summary' => $commande->getProductsSummary(),
                'montant_total' => $commande->getMontantTotal(),
                'rue' => $commande->getRue(),
                'ville' => $commande->getVille(),
                'code_postal' => $commande->getCodePostal(),
                'user_email' => $user->getEmail() // Add user email to details
            ];
    
            // Send to the logged-in user's email - pass $user->getEmail()
            $result = $emailService->sendOrderConfirmation($user->getEmail(), $orderDetails);
            
            // Debug: Check what email was actually sent
            dump($result);
            
            return new JsonResponse(['success' => true, 'message' => 'Email sent successfully']);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
} 