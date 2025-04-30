<?php

namespace App\Controller;

use App\Entity\Favoris;
use App\Repository\FavorisRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

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

        $existing = $em->getRepository(Favoris::class)->findOneBy([
            'user' => $user,
            'id_produit' => $produit->getId()
        ]);

        if (!$existing) {
            $favoris = new Favoris();
            $favoris->setProduit($produit->getId());
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
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
    
        $favorisList = $favorisRepository->findBy(['user' => $this->getUser()]);
    
        return $this->render('favoris/favoris.html.twig', [
            'favoris' => $favorisList,
        ]);
    }
    

    #[Route('/mes-produits', name: 'mes_favoris')]
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

    #[Route('/favoris/delete/{id}', name: 'favoris_delete', methods: ['POST'])]
    public function deleteFavori(Request $request, Favoris $favoris, EntityManagerInterface $em, Security $security): Response
    {
        $user = $security->getUser();

        // Ensure the user is the same as the one who added the product to favorites
        if ($favoris->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer ce favori.');
        }

        // Get the CSRF token from the form
        $submittedToken = $request->request->get('_token');

        // Validate the CSRF token
        if ($this->isCsrfTokenValid('delete' . $favoris->getIdFavoris(), $submittedToken)) {
            // Remove the favoris object from the database
            $em->remove($favoris);
            $em->flush();
        } else {
            // CSRF token is invalid
            $this->addFlash('error', 'Le token CSRF est invalide.');
        }

        // Redirect back to the favoris list after deletion
        return $this->redirectToRoute('app_favoris');
    }

    #[Route('/favoris/toggle/{id}', name: 'favoris_toggle', methods: ['POST'])]
    public function toggleFavoris(
        int $id,
        Request $request,
        ProduitRepository $produitRepository,
        FavorisRepository $favorisRepository,
        EntityManagerInterface $em,
        Security $security
    ): Response {
        if (!$request->isXmlHttpRequest()) {
            return $this->json(['success' => false, 'message' => 'Requête non autorisée'], 403);
        }

        $user = $security->getUser();
        if (!$user) {
            return $this->json(['success' => false, 'message' => 'Utilisateur non connecté'], 401);
        }

        $produit = $produitRepository->find($id);
        if (!$produit) {
            return $this->json(['success' => false, 'message' => 'Produit introuvable'], 404);
        }

        $favoris = $favorisRepository->findOneBy([
            'user' => $user,
            'produit' => $produit,
        ]);

        if ($favoris) {
            $em->remove($favoris);
            $em->flush();
            return $this->json(['success' => true, 'action' => 'removed']);
        } else {
            $favori = new Favoris();
            $favori->setUser($user);
            $favori->setProduit($produit);
            $favori->setDate(new \DateTime());

            $em->persist($favori);
            $em->flush();

            return $this->json(['success' => true, 'action' => 'added']);
        }
    }

   // Podium route for top 3 favorite products
#[Route('/podium', name: 'app_podium')]
public function podium(
    ProduitRepository $produitRepository,
    EntityManagerInterface $em
): Response {
    // Fetch top 3 favorite products
    $connection = $em->getConnection();
    $sql = "
        SELECT p.id, p.nom, p.image_name, COUNT(f.id_favoris) AS nb_favoris
        FROM favoris f
        JOIN produit p ON f.id_produit = p.id
        GROUP BY p.id
        ORDER BY nb_favoris DESC
        LIMIT 3
    ";
    $stmt = $connection->prepare($sql);
    $stmt = $stmt->executeQuery();
    $topFavoris = $stmt->fetchAllAssociative();

    // Convert the result into objects
    $topFavorisObjs = [];
    foreach ($topFavoris as $item) {
        $produit = $produitRepository->find($item['id']);
        if ($produit) {
            $produit->nbFavoris = $item['nb_favoris'];  // Attach the number of favorites
            $topFavorisObjs[] = $produit;
        }
    }

    return $this->render('favoris/podiumf.html.twig', [
        'topFavoris' => $topFavorisObjs,
    ]);
}

}
