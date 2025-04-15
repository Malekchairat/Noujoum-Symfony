<?php


/// src/Controller/ProduitController.php

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
    
        foreach ($produits as $produit) {
            if ($produit->getImage()) {
                $base64Image = base64_encode(stream_get_contents($produit->getImage()));
                $produit->base64Image = 'data:image/jpeg;base64,' . $base64Image;
            } else {
                $produit->base64Image = null;
            }
        }
    
        return $this->render('panier/shop.html.twig', [
            'produits' => $produits
        ]);
    }


    
}
