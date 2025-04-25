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
        try {
            $orderDetails = [
                'products_summary' => $commande->getProductsSummary(),
                'montant_total' => $commande->getMontantTotal(),
                'rue' => $commande->getRue(),
                'ville' => $commande->getVille(),
                'code_postal' => $commande->getCodePostal()
            ];

            $emailService->sendOrderConfirmation('hedifridhy@gmail.com', $orderDetails);

            return new JsonResponse(['success' => true, 'message' => 'Email sent successfully']);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
} 