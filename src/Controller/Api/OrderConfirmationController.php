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

    #[Route('/api/order/confirm/{id}', name: 'api_order_confirm', methods: ['POST'])]
    public function confirmOrder(Request $request, int $id): JsonResponse
    {
        try {
            // Get the order
            $order = $this->entityManager->getRepository(Commande::class)->find($id);
            if (!$order) {
                return new JsonResponse(['error' => 'Order not found'], 404);
            }

            // Prepare order details for email
            $orderDetails = [
                'products_summary' => $order->getProductsSummary(),
                'montant_total' => $order->getMontantTotal(),
                'rue' => $order->getRue(),
                'ville' => $order->getVille(),
                'code_postal' => $order->getCodePostal()
            ];

            // Send confirmation email and get debug info
            $emailDebug = $this->emailService->sendOrderConfirmation('hedifridhy@gmail.com', $orderDetails);

            if ($emailDebug['status'] === 'error') {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Failed to send email',
                    'debug' => $emailDebug
                ], 500);
            }

            return new JsonResponse([
                'success' => true,
                'message' => 'Order confirmation email sent successfully',
                'debug' => $emailDebug
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
} 