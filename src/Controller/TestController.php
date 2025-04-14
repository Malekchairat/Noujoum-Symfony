<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;

class TestController extends AbstractController
{
    #[Route('/test-produits', name: 'test_produits')]
    public function testProduits(EntityManagerInterface $em): Response
    {
        // 1. Création manuelle d'un produit test
        $produit = new Produit();
        $produit->setNom('Produit Test');
        $produit->setPrix(19.99);
        $produit->setImageName('test.jpg');
        
        // 2. Sauvegarde en base
        $em->persist($produit);
        $em->flush();
        
        // 3. Récupération depuis la base
        $produits = $em->getRepository(Produit::class)->findAll();
        
        // 4. Debug complet
        dd([
            'count' => count($produits),
            'produits' => $produits,
            'methods' => method_exists($produit, 'getNom') ? 'OK' : 'Méthodes manquantes'
        ]);
        
        // 5. Affichage template
        return $this->render('produit/shop.html.twig', [
            'produits' => $produits
        ]);
    }
}