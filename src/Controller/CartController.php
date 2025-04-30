<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Produit;
use App\Repository\PanierRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'cart')]
    public function index(PanierRepository $panierRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to view your cart.');
        }
        
        $cartItems = $panierRepository->findBy(['id_user' => $user->getIdUser()]);
        $totalPrice = 0;
        
        foreach ($cartItems as $item) {
            $totalPrice += $item->getTotal();
        }
        
        return $this->render('panier/cart.html.twig', [
            'cartItems' => $cartItems,
            'totalPrice' => $totalPrice
        ]);
    }
    
    #[Route('/cart/add/{id}', name: 'cart_add')]
    public function add(int $id, EntityManagerInterface $entityManager, PanierRepository $panierRepository, ProduitRepository $produitRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to add items to your cart.');
        }
        
        $produit = $produitRepository->find($id);
        if (!$produit) {
            throw $this->createNotFoundException('Product not found');
        }
        
        $cartItem = $panierRepository->findOneBy([
            'id_user' => $user->getIdUser(),
            'produit' => $produit
        ]);
        
        if ($cartItem) {
            $cartItem->setNbrProduit($cartItem->getNbrProduit() + 1);
        } else {
            $cartItem = new Panier();
            $cartItem->setIdUser($user->getIdUser());
            $cartItem->setProduit($produit);
            $cartItem->setNbrProduit(1);
            $entityManager->persist($cartItem);
        }
        
        $entityManager->flush();
        
        return $this->json([
            'success' => true,
            'message' => 'Product added to cart'
        ]);
    }
    
    #[Route('/cart/update/{id}/{quantity}', name: 'cart_update_quantity', methods: ['POST'])]
    public function updateQuantity(int $id, int $quantity, EntityManagerInterface $entityManager, PanierRepository $panierRepository): JsonResponse
    {
        $cartItem = $panierRepository->find($id);
        if (!$cartItem) {
            throw $this->createNotFoundException('Cart item not found');
        }
        
        if ($quantity <= 0) {
            $entityManager->remove($cartItem);
        } else {
            $cartItem->setNbrProduit($quantity);
        }
        
        $entityManager->flush();
        
        return $this->json([
            'success' => true,
            'newQuantity' => $quantity > 0 ? $quantity : 0,
            'newTotal' => $quantity > 0 ? $cartItem->getTotal() : 0,
            'cartTotal' => $this->calculateCartTotal($panierRepository)
        ]);
    }
    
    #[Route('/cart/remove/{id}', name: 'cart_remove', methods: ['POST'])]
    public function remove(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in.');
        }
        
        // Validate CSRF Token.
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete-item' . $id, $submittedToken)) {
            return new JsonResponse(
                [
                    'success' => false,
                    'message' => 'Invalid CSRF token'
                ],
                Response::HTTP_FORBIDDEN
            );
        }
        
        $connection = $entityManager->getConnection();
        
        try {
            // Start transaction.
            $connection->beginTransaction();
            
            // Check for any related commande and delete them.
            $commandeResult = $connection->executeQuery(
                'SELECT id FROM commande WHERE id_panier = :id',
                ['id' => $id]
            )->fetchAllAssociative();
            
            if (!empty($commandeResult)) {
                foreach ($commandeResult as $commande) {
                    $connection->executeStatement(
                        'DELETE FROM commande WHERE id = :id',
                        ['id' => $commande['id']]
                    );
                }
            }
            
            // Now safely delete the panier.
            $connection->executeStatement(
                'DELETE FROM panier WHERE id = :id',
                ['id' => $id]
            );
            
            // Calculate new cart total for the current user.
            $totalResult = $connection->executeQuery(
                'SELECT SUM(p.nbr_produit * pr.prix) as total
                 FROM panier p
                 JOIN produit pr ON p.id_produit = pr.id
                 WHERE p.id_user = :id_user',
                ['id_user' => $user->getIdUser()]
            )->fetchAssociative();
            
            $total = $totalResult['total'] ?? 0;
            
            // Commit transaction.
            $connection->commit();
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Item removed successfully',
                'cartTotal' => $total
            ]);


        } catch (\Exception $e) {
            // Rollback if needed.
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }
            
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
        
    }

    #[Route('/cart/count', name: 'cart_count')]
    public function count(PanierRepository $panierRepository): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in.');
        }
        
        $count = $panierRepository->count(['id_user' => $user->getIdUser()]);
        return $this->json(['count' => $count]);
    }
    
    #[Route('/cart/preview', name: 'cart_preview')]
    public function preview(PanierRepository $panierRepository): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in.');
        }
        
        $cartItems = $panierRepository->findBy(['id_user' => $user->getIdUser()]);
        $items = [];
        $total = 0;
        
        foreach ($cartItems as $item) {
            $items[] = [
                'name' => $item->getProduit()->getNom(),
                'price' => $item->getProduit()->getPrix(),
                'quantity' => $item->getNbrProduit(),
                'image' => $item->getProduit()->getBase64Image()
            ];
            $total += $item->getTotal();
        }
        
        return $this->json([
            'items' => $items,
            'total' => $total
        ]);
    }
    
    private function calculateCartTotal(PanierRepository $panierRepository): float
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in.');
        }
        
        $cartItems = $panierRepository->findBy(['id_user' => $user->getIdUser()]);
        $total = 0;
        
        foreach ($cartItems as $item) {
            $total += $item->getTotal();
        }
        
        return $total;
    }
    
    #[Route('/checkout', name: 'app_checkout')]
    public function checkout(PanierRepository $panierRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to checkout.');
        }
        
        $cartItems = $panierRepository->findBy(['id_user' => $user->getIdUser()]);
        
        if (count($cartItems) === 0) {
            $this->addFlash('error', 'Your cart is empty');
            return $this->redirectToRoute('cart');
        }
        
        return $this->redirectToRoute('app_checkout_process');
    }
}
