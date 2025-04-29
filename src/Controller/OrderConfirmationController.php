<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\CommandeRepository;
use App\Service\EmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OrderConfirmationController extends AbstractController
{
    #[Route('/api/order/confirm/{id}', name: 'app_order_confirm', methods: ['POST'])]
    public function confirmOrder(Commande $commande, EmailService $emailService): JsonResponse
    {
        // Get the currently logged-in user
        $user = $this->getUser();
        
        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'User not authenticated'], 401);
        }

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