<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Bundle\PdfBundle\Service\PdfGenerator;
use App\Repository\CommandeRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Entity\Panier;
use App\Form\CheckoutType;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CommandeController extends AbstractController
{
    #[Route('/checkout', name: 'app_checkout')]
    public function checkout(Request $request, PanierRepository $panierRepository, EntityManagerInterface $em): Response
    {


        // 1. Get current cart items for hardcoded user id 1
        $cartItems = $panierRepository->findBy(['id_user' => 1]);

        // 2. Check if cart is empty
        if (empty($cartItems)) {
            $this->addFlash('error', 'Your cart is empty');
            return $this->redirectToRoute('cart');
        }

        // 3. Create new Commande entity
        $commande = new Commande();

        $form = $this->createForm(CheckoutType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 4. Calculate total
            $total = array_reduce($cartItems, fn($sum, $item) => $sum + $item->getTotal(), 0);

            // 5. Product summary
            $productSummaryArray = [];
            foreach ($cartItems as $item) {
                $productSummaryArray[] = $item->getProduit()->getNom() . ' x' . $item->getNbrProduit();
            }
            $productSummaryString = implode(', ', $productSummaryArray);

            // 6. Set order details
            $commande->setMontantTotal($total)
                     ->setIdUser(1)
                     ->setPanier($cartItems[0])
                     ->setProductsSummary($productSummaryString);

            // 7. Save the order
            $em->persist($commande);
            $em->flush();

            // 8. Clear the cart
            foreach ($cartItems as $item) {
                $em->remove($item);
            }
            $em->flush();

            // Return JSON response for AJAX
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => true,
                    'orderId' => $commande->getId(),
                    'message' => 'Order placed successfully'
                ]);
            }

            // 9. Redirect to thank you page
            return $this->redirectToRoute('app_postcheckout', ['id' => $commande->getId()]);
        }

        // 10. Render the checkout page
        return $this->render('panier/checkout.html.twig', [
            'form' => $form->createView(),
            'cartItems' => $cartItems,
            'total' => array_reduce($cartItems, fn($sum, $item) => $sum + $item->getTotal(), 0)
        ]);
    }

    #[Route('/checkout/success/{id}', name: 'app_postcheckout')]
    public function postCheckout(Commande $commande = null): Response
    {
        if (!$commande) {
            $this->addFlash('error', 'Order not found');
            return $this->redirectToRoute('app_homepage');
        }

        return $this->render('panier/thankyou.html.twig', [
            'commande' => $commande
        ]);
    }

    #[Route('/export/commandes', name: 'export_commandes')]
    public function exportCommandes(CommandeRepository $commandeRepository): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Rue');
        $sheet->setCellValue('C1', 'Ville');
        $sheet->setCellValue('D1', 'Code Postal');
        $sheet->setCellValue('E1', 'État');
        $sheet->setCellValue('F1', 'Montant Total');
        $sheet->setCellValue('G1', 'Méthode de Paiement');
        $sheet->setCellValue('H1', 'ID Utilisateur');

        // Data
        $commandes = $commandeRepository->findAll();
        $row = 2;
        foreach ($commandes as $commande) {
            $sheet->setCellValue('A' . $row, $commande->getId());
            $sheet->setCellValue('B' . $row, $commande->getRue());
            $sheet->setCellValue('C' . $row, $commande->getVille());
            $sheet->setCellValue('D' . $row, $commande->getCodePostal());
            $sheet->setCellValue('E' . $row, $commande->getEtat());
            $sheet->setCellValue('F' . $row, $commande->getMontantTotal());
            $sheet->setCellValue('G' . $row, $commande->getMethodePaiment());
            $sheet->setCellValue('H' . $row, $commande->getIdUser());
            $row++;
        }

        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $filename = 'commandes.xlsx';

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $filename . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    #[Route('/commande/{id}/receipt', name: 'commande_receipt')]
    public function generateReceipt(Commande $commande, PdfGenerator $pdfGenerator): Response
    {
        $html = $this->renderView('pdf/receipt.html.twig', [
            'commande' => $commande,
        ]);

        return $pdfGenerator->generate($html, 'commande_' . $commande->getId() . '_receipt.pdf');
    }

    #[Route('/historique', name: 'app_commande_historique')]
    public function historique(CommandeRepository $commandeRepository): Response
    {
        // Hardcoded user id = 1 instead of $this->getUser()
        $userId = 1;

        $commandes = $commandeRepository->findBy([
            'id_user' => $userId,
        ]);

        return $this->render('panier/historique.html.twig', [
            'commandes' => $commandes,
        ]);
    }
}
