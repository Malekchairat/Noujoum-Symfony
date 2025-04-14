<?php

namespace App\Controller;

use App\Entity\Favoris;
use App\Entity\User;
use App\Repository\FavorisRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;

class FavorisController extends AbstractController
{
    #[Route('/favoris/add/{id}', name: 'add_to_favoris', methods: ['POST'])]
    public function addToFavoris(int $id, ProduitRepository $produitRepository, EntityManagerInterface $em, Security $security): RedirectResponse
    {
        $user = $security->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $produit = $produitRepository->find($id);
        if (!$produit) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        // Check if this product is already in the user's favoris
        $existing = $em->getRepository(Favoris::class)->findOneBy([
            'user' => $user,
            'id_produit' => $produit->getId()
        ]);

        if (!$existing) {
            $favoris = new Favoris();
            $favoris->setIdProduit($produit->getId());
            $favoris->setUser($user);
            $favoris->setDate(new \DateTime());

            $em->persist($favoris);
            $em->flush();
        }

        return $this->redirectToRoute('app_album_details', ['id' => $id]);
    }
    #[Route('/favoris', name: 'app_favoris')]
    public function index(FavorisRepository $favorisRepository): Response
    {
        // Fetch the favoris for the logged-in user
        $favorisList = [];
        if ($this->getUser()) {
            $favorisList = $favorisRepository->findBy(['user' => $this->getUser()]);
        }

        return $this->render('favoris/index.html.twig', [
            'favorisList' => $favorisList,
        ]);
    }
    #[Route('/mes-produits', name: 'app_favoris')]
public function mesFavoris(FavorisRepository $favorisRepository): Response
{
    $user = $this->getUser();

    if (!$user) {
        return $this->redirectToRoute('app_login');
    }

    $favoris = $favorisRepository->findBy(['user' => $user]);

    return $this->render('favoris/favoris.html.twig', [
        'favoris' => $favoris,
    ]);
}

}
