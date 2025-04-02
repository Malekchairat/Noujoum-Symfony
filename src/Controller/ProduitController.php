<?php


// src/Controller/ProduitController.php

namespace App\Controller;

use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProduitController extends AbstractController
{
    #[Route('/shop', name: 'shop')]
    public function shop(EntityManagerInterface $entityManager): Response
    {
        $produits = $entityManager->getRepository(Produit::class)->findAll();
    
        dd($produits); // Debugging: Check if products are fetched
    
        return $this->render('hop.html.twig', [
            'produits' => $produits
        ]);
    }
    
}


