<?php

namespace App\Controller;

use App\Entity\Commande;

use App\Form\AdminCommandeType;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminCommandeController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/admin/test', name: 'admin_test')]
    public function test(EntityManagerInterface $entityManager): Response
    {
        $commandes = $entityManager->getRepository(Commande::class)->findAll();

        return $this->render('admin/test.html.twig', [
            'commandes' => $commandes,
        ]);
    }

    #[Route('/admin/commandes/modify/{id}', name: 'admin_commandes_modify', methods: ['GET', 'POST'])]
    public function modifyCommande(int $id, EntityManagerInterface $entityManager, Request $request): Response
    {
        $commande = $entityManager->getRepository(Commande::class)->find($id);

        if (!$commande) {
            throw $this->createNotFoundException('Commande not found');
        }

        $originalMontantTotal = $commande->getMontantTotal();
        $originalIdUser = $commande->getIdUser();

        // Create the form with validation disabled
        $form = $this->createForm(AdminCommandeType::class, $commande, [
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() ) {
            // Debug form data
            $data = $form->getData();
            
            try {
                 // Restore original values
            $commande->setMontantTotal($originalMontantTotal);
            $commande->setIdUser($originalIdUser);
                $entityManager->persist($commande);
                $entityManager->flush();
                
                $this->addFlash('success', 'Commande mise à jour avec succès');
                return $this->redirectToRoute('admin_test');
            } catch (\Exception $e) {
                // Log the exception
                $this->addFlash('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
            }
        }

        return $this->render('admin/modify_commande.html.twig', [
            'form' => $form->createView(),
            'commande' => $commande,
        ]);
    }
    
    #[Route('/admin/commandes/delete/{id}', name: 'admin_commandes_delete', methods: ['POST'])]
    public function deleteCommande(int $id, EntityManagerInterface $entityManager): Response
    {
        $commande = $entityManager->getRepository(Commande::class)->find($id);

        if (!$commande) {
            throw $this->createNotFoundException('Commande not found');
        }

        $entityManager->remove($commande);
        $entityManager->flush();

        $this->addFlash('success', 'Commande supprimée avec succès');
        return $this->redirectToRoute('admin_test');
    }

    

    #[Route('/admin/test/search', name: 'admin_test_search')]
    public function searchCommandes(Request $request): JsonResponse
    {
        $query = $request->query->get('q');
        
        // Modify the query to avoid using CAST and check if query is empty
        if (!$query) {
            $commandes = $this->entityManager->getRepository(Commande::class)->findAll(); // Return all if no query
        } else {
            $commandes = $this->entityManager->getRepository(Commande::class)
                ->createQueryBuilder('c')
                ->where('LOWER(c.id) LIKE LOWER(:query)')
                ->orWhere('LOWER(c.methodePaiment) LIKE LOWER(:query)')
                ->orWhere('LOWER(c.productsSummary) LIKE LOWER(:query)')
                ->setParameter('query', '%' . strtolower($query) . '%')
                ->getQuery()
                ->getResult();
        }
    
        // Prepare and return the response
        $data = [];
        foreach ($commandes as $commande) {
            $data[] = [
                'id' => $commande->getId(),
                'rue' => $commande->getRue(),
                'ville' => $commande->getVille(),
                'codePostal' => $commande->getCodePostal(),
                'etat' => $commande->getEtat(),
                'montantTotal' => $commande->getMontantTotal(),
                'methodePaiment' => $commande->getMethodePaiment(),
                'idUser' => $commande->getIdUser(),
                'productsSummary' => $commande->getProductsSummary(),
            ];
        }
    
        return new JsonResponse($data);
    }
}    