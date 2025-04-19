<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Panier;
use App\Form\CheckoutType;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommandeController extends AbstractController
{
    #[Route('/checkout', name: 'app_checkout')]
public function checkout(Request $request, PanierRepository $panierRepository, EntityManagerInterface $em): Response
{
    // 1. Get current cart items
    $cartItems = $panierRepository->findBy(['id_user' => 1]);
    
    // 2. Check if cart is empty
    if (empty($cartItems)) {
        $this->addFlash('error', 'Your cart is empty');
        return $this->redirectToRoute('cart');
    }

    // 3. Always create NEW order (remove existing order check)
    $commande = new Commande();
    $form = $this->createForm(CheckoutType::class, $commande);
    
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // 4. Calculate total
        $total = array_reduce($cartItems, fn($sum, $item) => $sum + $item->getTotal(), 0);
        
        // 5. Set order details
        $commande->setMontantTotal($total)
                ->setIdUser(1)
                ->setPanier($cartItems[0]); // Link to first cart item

        // 6. Save new order
        $em->persist($commande);
        $em->flush();

        // 7. Redirect to thank you page
        return $this->redirectToRoute('app_postcheckout', ['id' => $commande->getId()]);
    }

    // 8. Show checkout form if not submitted
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
        // Handle case where order isn't found
        $this->addFlash('error', 'Order not found');
        return $this->redirectToRoute('app_homepage');
    }

    return $this->render('panier/thankyou.html.twig', [
        'commande' => $commande
    ]);
}
}