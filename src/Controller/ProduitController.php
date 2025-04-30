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
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;
use Symfony\Component\HttpClient\HttpClient;

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
    
        $spotifyData = $this->getSpotifyData($produit->getNom());
    
        return $this->render('produit/details.html.twig', [
            'produit' => $produit,
            'promo' => $promo,
            'favoris' => $favoris,
            'spotifyEmbedUrl' => $spotifyData['spotifyEmbedUrl'] ?? null,
        ]);
    }

    
    public function showProduit(Produit $produit, PromotionRepository $promoRepo): Response
{
    $promo = $promoRepo->findPromoForProduit($produit);

    $spotifyData = $this->getSpotifyData($produit->getNom());

    return $this->render('produit/show.html.twig', [
        'produit' => $produit,
        'promo' => $promo,
        'spotifyEmbedUrl' => $spotifyData['spotifyEmbedUrl'] ?? null,
    ]);
}

private function getSpotifyData(string $albumName): array
{
    // 1. Essayer avec l'API officielle
    try {
        $session = new Session($_ENV['SPOTIFY_CLIENT_ID'], $_ENV['SPOTIFY_CLIENT_SECRET']);
        $session->requestCredentialsToken();
        $api = new SpotifyWebAPI();
        $api->setAccessToken($session->getAccessToken());

        $results = $api->search($albumName, 'album', ['limit' => 1]);
       
        if (!empty($results->albums->items)) {
            $album = $results->albums->items[0];
           
            // Vérification cruciale de l'URI
            if (empty($album->uri) || !str_starts_with($album->uri, 'spotify:album:')) {
                throw new \Exception('URI Spotify invalide');
            }

            $tracks = $api->getAlbumTracks($album->id);
           
            foreach ($tracks->items as $track) {
                if ($track->preview_url) {
                    return [
                        'previewUrl' => $track->preview_url,
                        'coverUrl' => $album->images[0]->url ?? null,
                        'spotifyEmbedUrl' => "https://open.spotify.com/embed/album/".str_replace('spotify:album:', '', $album->uri),
                        'spotifyUrl' => $album->external_urls->spotify ?? null
                    ];
                }
            }
           
            return [
                'coverUrl' => $album->images[0]->url ?? null,
                'spotifyEmbedUrl' => "https://open.spotify.com/embed/album/".str_replace('spotify:album:', '', $album->uri),
                'spotifyUrl' => $album->external_urls->spotify ?? null,
                'error' => 'Aucun extrait audio disponible'
            ];
        }
    } catch (\Exception $e) {
        // Logger l'erreur
        error_log('Erreur Spotify API: ' . $e->getMessage());
    }

    // 2. Fallback - Extraction depuis le player web
    $httpClient = HttpClient::create();
    try {
        $response = $httpClient->request('GET', 'https://open.spotify.com/search/'.urlencode($albumName));
       
        if (preg_match('/https:\/\/p\.scdn\.co\/mp3-preview\/[a-f0-9]+/', $response->getContent(), $matches)) {
            return [
                'previewUrl' => $matches[0],
                'coverUrl' => $this->extractCoverUrl($response->getContent())
            ];
        }
    } catch (\Exception $e) {
        error_log('Erreur HTTP: ' . $e->getMessage());
    }

    return ['error' => 'Impossible de trouver cet album'];
}
}
