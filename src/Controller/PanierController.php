<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PanierController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function index(): Response
    {
        return $this->render('panier/index.html.twig', [
            'controller_name' => 'PanierController',
        ]);
    }

    #[Route('/contact-us', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('panier/contact.html.twig', [
        
        ]);
    }

    #[Route('/about-us', name: 'app_about_us')]
    public function about_us(): Response
    {
        return $this->render('panier/about.html.twig', [
        
        ]);
    
    }

    #[Route('/cart', name: 'app_cart')]
    public function cart(): Response
    {
        return $this->render('panier/cart.html.twig', [
        
        ]);
    
    }

    #[Route('/checkout', name: 'app_checkout')]
    public function checkout(): Response
    {
        return $this->render('panier/checkout.html.twig', [
        
        ]);
    
    }

    #[Route('/postcheckout', name: 'app_postcheckout')]
    public function postcheckout(): Response
    {
        return $this->render('panier/thankyou.html.twig', [
        
        ]);
    
    }

    #[Route('/shop', name: 'app_shop')]
    public function shop(): Response
    {
        return $this->render('produit/shop.html.twig', [
        
        ]);
    
    }
    
}
