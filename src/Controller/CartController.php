<?php

// src/Controller/CartController.php
namespace App\Controller;

use App\Entity\Panier;
use App\Repository\PanierRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(PanierRepository $panierRepository)
    {
        $cartItems = $panierRepository->findAll(); // Fetch cart items

        // Calculate total price
        $totalPrice = 0;
        foreach ($cartItems as $item) {
            $totalPrice += $item->getNbrProduit() * $item->getProduit()->getPrix();
        }

        return $this->render('cart.html.twig', [
            'cartItems' => $cartItems,
            'totalPrice' => $totalPrice
        ]);
    }
}

    

