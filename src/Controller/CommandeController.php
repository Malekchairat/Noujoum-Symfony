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
    // Route for displaying the checkout form and processing order creation
    #[Route('/checkout', name: 'app_checkout')]
    public function checkout(Request $request, PanierRepository $panierRepository, EntityManagerInterface $em): Response
    {
        // 1. Retrieve all cart items for the current user (here hardcoded as user ID 1)
        $cartItems = $panierRepository->findBy(['id_user' => 1]);

        // 2. If cart is empty, show an error message and redirect to the cart page
        if (empty($cartItems)) {
            $this->addFlash('error', 'Your cart is empty');
            return $this->redirectToRoute('cart');
        }

        // 3. Create a new Commande (order) entity
        $commande = new Commande();

        // 4. Create a form based on the CheckoutType form class, linked to the Commande entity
        $form = $this->createForm(CheckoutType::class, $commande);
        $form->handleRequest($request); // Process the form input

        // 5. If the form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            // 6. Calculate the total price of all items in the cart
            $total = array_reduce($cartItems, fn($sum, $item) => $sum + $item->getTotal(), 0);

            // 7. Set the order properties: total amount, user ID, and associate with one cart item
            $commande->setMontantTotal($total)
                     ->setIdUser(1)
                     ->setPanier($cartItems[0]); // NOTE: Only links to the first cart item (can be improved)

            // 8. Persist (save) the new Commande entity in the database
            $em->persist($commande);
            $em->flush();

            // 9. Redirect to a success page showing order confirmation
            return $this->redirectToRoute('app_postcheckout', ['id' => $commande->getId()]);
        }

        // 10. Render the checkout page with the form and cart summary
        return $this->render('panier/checkout.html.twig', [
            'form' => $form->createView(),
            'cartItems' => $cartItems,
            'total' => array_reduce($cartItems, fn($sum, $item) => $sum + $item->getTotal(), 0)
        ]);
    }

    // Route for showing the success page after a successful checkout
    #[Route('/checkout/success/{id}', name: 'app_postcheckout')]
    public function postCheckout(Commande $commande = null): Response
    {
        // If the order does not exist (invalid or missing ID), show an error and redirect
        if (!$commande) {
            $this->addFlash('error', 'Order not found');
            return $this->redirectToRoute('app_homepage');
        }

        // Render the thank-you page with the order information
        return $this->render('panier/thankyou.html.twig', [
            'commande' => $commande
        ]);
    }
}
