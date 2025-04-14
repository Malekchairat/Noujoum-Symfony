<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\AlbumImage;
use App\Form\AlbumImageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/produit/{id}/ajouter-image', name: 'app_product_add_image')]
    public function addImage(int $id, Request $request, EntityManagerInterface $entityManager)
    {
        // Trouver le produit par ID
        $produit = $entityManager->getRepository(Produit::class)->find($id);

        if (!$produit) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        // Création de l'entité AlbumImage
        $albumImage = new AlbumImage();

        // Création d'un formulaire pour l'upload de l'image (nous assumerons que tu veux uploader des fichiers)
        $form = $this->createForm(AlbumImageType::class, $albumImage);

        // Traitement du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrer l'image dans le produit
            $produit->addAlbumImage($albumImage);

            // Sauvegarde de l'entité AlbumImage
            $entityManager->persist($albumImage);
            $entityManager->persist($produit);  // Sauvegarde du produit avec la relation image
            $entityManager->flush();

            // Rediriger vers la page de détails du produit
            return $this->redirectToRoute('app_product_show', ['id' => $produit->getId()]);
        }

        return $this->render('produit/details.html.twig', [
            'form' => $form->createView(),
            'produit' => $produit,
        ]);
    }
}
