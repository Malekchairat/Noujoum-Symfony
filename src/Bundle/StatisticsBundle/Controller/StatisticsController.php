<?php

namespace App\Bundle\StatisticsBundle\Controller;

use App\Entity\Commande;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatisticsController extends AbstractController
{
    #[Route('/admin/statistics', name: 'statistics_charts')]
    public function index(EntityManagerInterface $em): Response
    {
        $commandes = $em->getRepository(Commande::class)->findAll();
        $orderCount = count($commandes);
        
        $productStats = [];
        $cityStats = [];
        $totalProducts = 0;

        foreach ($commandes as $commande) {
            $summary = $commande->getProductsSummary();
            $ville = $commande->getVille();

            if ($summary) {
                // Split products by comma if multiple products
                $products = explode(',', $summary);
                
                foreach ($products as $product) {
                    $product = trim($product);
                    
                    // Extract product name and quantity
                    if (preg_match('/^(.*?)\s*x(\d+)$/i', $product, $matches)) {
                        $productName = trim($matches[1]);
                        $quantity = (int)$matches[2];
                        
                        if (!empty($productName)) {
                            if (!isset($productStats[$productName])) {
                                $productStats[$productName] = 0;
                            }
                            $productStats[$productName] += $quantity;
                            $totalProducts += $quantity;
                        }
                    }
                }
            }

            if ($ville) {
                $cityStats[$ville] = ($cityStats[$ville] ?? 0) + 1;
            }
        }

        // Sort products by quantity in descending order
        arsort($productStats);
        
        // Get top 10 products
        $topProducts = array_slice($productStats, 0, 10, true);

        // Sort cities by number of orders in descending order
        arsort($cityStats);

        // Debug information
        $debug = [
            'orderCount' => $orderCount,
            'productCount' => count($productStats),
            'cityCount' => count($cityStats),
            'totalProducts' => $totalProducts,
            'sampleProducts' => array_slice($productStats, 0, 3, true),
            'rawSummary' => $commandes[0]->getProductsSummary() ?? 'No summary available'
        ];

        return $this->render('views/statistics.html.twig', [
            'productNames' => array_keys($topProducts),
            'productQuantities' => array_values($topProducts),
            'cityStats' => array_map(fn($name, $y) => ['name' => $name, 'y' => $y], array_keys($cityStats), array_values($cityStats)),
            'totalProducts' => $totalProducts,
            'totalOrders' => $orderCount,
            'debug' => $debug
        ]);
    }
}
