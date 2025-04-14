<?php

namespace App\Controller;

use App\Entity\Favoris;
use App\Repository\FavorisRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
        // Fetch the product by ID
        $produit = $repo->find($id);

        // Throw an exception if the product does not exist
        if (!$produit) {
            throw $this->createNotFoundException("Album non trouvé");
        }

        // Check if there is an active promotion for the product
        $promo = null;
        foreach ($produit->getPromotions() as $p) {
            if ($p->getExpiration() >= new \DateTime()) {
                $promo = $p;
                break;
            }
        }

        // Check if the product is already in the user's favoris
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
            'favoris' => $favoris // Pass favoris status to template
        ]);
    }

    #[Route('/favoris/add/{id}', name: 'add_to_favoris', methods: ['POST'])]
    public function addToFavoris(int $id, ProduitRepository $produitRepository, FavorisRepository $favorisRepository, EntityManagerInterface $em, Security $security): RedirectResponse
    {
        // Get the currently logged-in user
        $user = $security->getUser();
        if (!$user) {
            // If not logged in, redirect to login page
            return $this->redirectToRoute('app_login');
        }

        // Fetch the product by ID
        $produit = $produitRepository->find($id);
        if (!$produit) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        // Check if the product is already in the user's favoris
        $existingFavoris = $favorisRepository->findOneBy([
            'user' => $user,
            'produit' => $produit
        ]);

        if (!$existingFavoris) {
            // Add the product to favoris if not already added
            $favoris = new Favoris();
            $favoris->setIdProduit($produit);
            $favoris->setUser($user);
            $favoris->setDate(new \DateTime());

            // Persist the new favoris
            $em->persist($favoris);
            $em->flush();
        }

        // Redirect to the album details page
        return $this->redirectToRoute('app_album_details', ['id' => $id]);
    }
}
