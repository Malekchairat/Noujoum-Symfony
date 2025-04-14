<?php

namespace App\Controller;

use App\Entity\Favoris;
use App\Repository\FavorisRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class ProduitController extends AbstractController
{
    #[Route('/shop', name: 'app_shop')]
    public function index(Request $request, ProduitRepository $produitRepository, FavorisRepository $favorisRepository): Response
    {
        $search = $request->query->get('search');
        $sort = $request->query->get('sort');

        // Get the products based on search and sort
        $produits = $produitRepository->findBySearchAndSort($search, $sort);

        // Initialize favoris list
        $favorisList = [];
        
        if ($this->getUser()) {
            // Fetch the favoris for the logged-in user
            foreach ($favorisRepository->findBy(['user' => $this->getUser()]) as $favori) {
                $produit = $favori->getProduit();
                // Ensure the produit is valid and store the product ID in the favoris list
                if ($produit) {
                    $favorisList[$produit->getId()] = true;
                }
            }
        }

        return $this->render('produit/shop.html.twig', [
            'produits' => $produits,
            'search' => $search,
            'sort' => $sort,
            'favorisList' => $favorisList, // Pass favoris list to template
        ]);
    }

    #[Route('/shop/album/{id}', name: 'app_album_details')]
    public function details(ProduitRepository $repo, FavorisRepository $favorisRepository, int $id): Response
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

        $favoris = false;
        if ($this->getUser()) {
            $favoris = $favorisRepository->findOneBy([
                'user' => $this->getUser(),
                'produit' => $produit
            ]) !== null;
        }

        return $this->render('produit/details.html.twig', [
            'produit' => $produit,
            'promo' => $promo,
            'favoris' => $favoris
        ]);
    }
}
