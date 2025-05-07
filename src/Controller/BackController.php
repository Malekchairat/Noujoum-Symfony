<?php

namespace App\Controller;

use App\Repository\EvenementRepository;
use App\Repository\FavorisRepository;
use App\Repository\ReclamationRepository;
use App\Controller\AdminController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Promotion;
use App\Form\PromotionType;
use App\Entity\Produit; // Add this line
use App\Form\ProduitType; // Add this line if you have a ProduitType form
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface; // Add this line
use Symfony\Component\HttpFoundation\Request; // Add this line


class BackController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
<<<<<<< HEAD
public function dashboard(
    EvenementRepository $evenementRepo,
    FavorisRepository   $favorisRepo,
    ReclamationRepository $reclamationRepo
): Response
{
    // Fetching top 3 events by ticket count
    $results     = $evenementRepo->findTopByTicketCount(3);
    
    // Fetching top 3 favorite products by likes
    $favorites   = $favorisRepo->findTopByLikes(3);
    
    // Fetching status and priority statistics for reclamations
    $statusStats = $reclamationRepo->countByStatus();
    $priorityStats = $reclamationRepo->countByPriority();
    
    // Fetch all events (to pass to Twig for the notifications section)
    $evenements = $evenementRepo->findAll();

    return $this->render('dashboard.html.twig', [
        'evenements'    => $evenements,   // Add this line to pass the events to the template
        'results'       => $results,
        'favorites'     => $favorites,
        'statusStats'   => $statusStats,
        'priorityStats' => $priorityStats,
    ]);
}
=======
    public function dashboard(
        EvenementRepository $evenementRepo,
        FavorisRepository   $favorisRepo,
        ReclamationRepository $reclamationRepo
    ): Response
    {
        $results     = $evenementRepo->findTopByTicketCount(3);
        $favorites   = $favorisRepo->findTopByLikes(3);
        $statusStats = $reclamationRepo->countByStatus();
        $priorityStats = $reclamationRepo->countByPriority();

        return $this->render('dashboard.html.twig', [
            'results'       => $results,
            'favorites'     => $favorites,
            'statusStats'   => $statusStats,
            'priorityStats' => $priorityStats,
        ]);
    }
>>>>>>> origin/GestionCommandes


    #[Route('/backoffice/produits', name: 'produits_index')]
    public function produitsIndex(ProduitRepository $produitRepository): Response
    {
        return $this->render('backoffice/produits/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }

    // src/Controller/BackController.php
    #[Route('/backoffice/produits/ajout', name: 'produit_ajout')]
public function ajoutProduit(Request $request, EntityManagerInterface $em): Response
{
    $produit = new Produit();
    $form = $this->createForm(ProduitType::class, $produit);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $imageFile = $form->get('image')->getData();
        
        if ($imageFile) {
            // Get uploads directory path
            $uploadsDirectory = $this->getParameter('kernel.project_dir').'/public/uploads';
            
            // Generate unique filename
            $newFilename = uniqid().'.'.$imageFile->guessExtension();
            
            // Move the file
            $imageFile->move(
                $uploadsDirectory,
                $newFilename
            );
            
            $produit->setImageName($newFilename);
        }

        $em->persist($produit);
        $em->flush();

        $this->addFlash('success', 'Produit ajouté avec succès!');
        return $this->redirectToRoute('produits_index');
    }

    return $this->render('backoffice/produits/ajout.html.twig', [
        'form' => $form->createView()
    ]);
}
    
// src/Controller/BackController.php
#[Route('/backoffice/produits/edit/{id}', name: 'produit_edit')]
public function editProduit(Request $request, Produit $produit, EntityManagerInterface $em): Response
{
    $form = $this->createForm(ProduitType::class, $produit);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->flush();
        $this->addFlash('success', 'Produit modifié avec succès!');
        return $this->redirectToRoute('produits_index');
    }

    return $this->render('backoffice/produits/editprod.html.twig', [
        'form' => $form->createView(),
        'produit' => $produit
    ]);
}

    #[Route('/backoffice/produits/delete/{id}', name: 'produit_delete')]
    public function deleteProduit(Produit $produit, EntityManagerInterface $em): Response
    {
        $em->remove($produit);
        $em->flush();
        
        $this->addFlash('success', 'Produit supprimé avec succès!');
        return $this->redirectToRoute('produits_index');
    }
// src/Controller/PromotionController.php

#[Route('/promotion/add/{produitId}', name: 'promotion_add')]
public function addPromotion(Request $request, EntityManagerInterface $entityManager, int $produitId): Response
{
    $produit = $entityManager->getRepository(Produit::class)->find($produitId);
    
    if (!$produit) {
        throw $this->createNotFoundException('Produit non trouvé');
    }

    $promotion = new Promotion();
    $promotion->setProduit($produit);

    $form = $this->createForm(PromotionType::class, $promotion);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($promotion);
        $entityManager->flush();

        $this->addFlash('success', 'Promotion ajoutée avec succès');
        return $this->redirectToRoute('produits_index');
    }

    return $this->render('backoffice/promotions/ajout.html.twig', [
        'form' => $form->createView(),
        'produit' => $produit,
    ]);
}
#[Route('/produit/{id}/promotions', name: 'produit_promotions')]
public function showPromotions(Produit $produit): Response
{
    return $this->render('backoffice/promotions/show.html.twig', [
        'produit' => $produit,
    ]);
}
#[Route('/promotion/{id}/edit', name: 'promotion_edit')]
public function edit(Request $request, Promotion $promotion, EntityManagerInterface $em): Response
{
    $form = $this->createForm(PromotionType::class, $promotion);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->flush();
        $this->addFlash('success', 'La promotion a été modifiée avec succès.');
        return $this->redirectToRoute('produits_index');
    }

    return $this->render('backoffice/promotions/editpromo.html.twig', [
        'form' => $form->createView(),
        'promotion' => $promotion,
    ]);
}

#[Route('/promotion/{id}/delete', name: 'promotion_delete')]
public function delete(Promotion $promotion, EntityManagerInterface $em): Response
{
    $produit = $promotion->getProduit(); // Pour rediriger vers la vue correspondante après suppression

    $em->remove($promotion);
    $em->flush();

    $this->addFlash('danger', 'La promotion a été supprimée avec succès.');

    return $this->redirectToRoute('produits_index'); // Ou 'promotion_show', par ex. avec l'ID produit
}

#[Route('/backoffice/produits/{id}/add-image', name: 'produit_add_image')]
public function addImage(Produit $produit): Response
{
    // Logic to handle image addition (e.g., uploading an image)
    return $this->render('backoffice/produits/add_image.html.twig', [
        'produit' => $produit
    ]);
}

}