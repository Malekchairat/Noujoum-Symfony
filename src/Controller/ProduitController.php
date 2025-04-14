<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProduitController extends AbstractController
{
    #[Route('/shop', name: 'app_shop')]
    public function shop(ProduitRepository $produitRepository): Response
    {
        $produits = $produitRepository->findAllProducts(); // Récupération des produits

        return $this->render('produit/shop.html.twig', [
            'produits' => $produits, // Transmission à Twig
        ]);
    }
 
#[Route('/shop', name: 'app_shop')]
public function index(Request $request, ProduitRepository $produitRepository): Response
{
    $search = $request->query->get('search');
    $sort = $request->query->get('sort');

    $produits = $produitRepository->findBySearchAndSort($search, $sort);

    return $this->render('produit/shop.html.twig', [
        'produits' => $produits,
        'search' => $search,
        'sort' => $sort,
    ]);
}   

#[Route('/shop/album/{id}', name: 'app_album_details')]
public function details(ProduitRepository $repo, int $id): Response
{
    $produit = $repo->find($id);

    if (!$produit) {
        throw $this->createNotFoundException("Album non trouvé");
    }

    $promo = null;
    foreach ($produit->getPromotions() as $p) {
        if ($p->getExpiration() >= new \DateTime()) {
            $promo = $p;
            break;
        }
    }

    return $this->render('produit/details.html.twig', [
        'produit' => $produit,
        'promo' => $promo
    ]);
}

}