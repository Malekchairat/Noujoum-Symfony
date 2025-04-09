<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
class Panier {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column]
    private ?int $id_user = null;
    
    #[ORM\Column]
    private ?int $nbr_produit = null;
    
    #[ORM\ManyToOne(targetEntity: Produit::class)]
    #[ORM\JoinColumn(name: "id_produit", referencedColumnName: "id")]
    private ?Produit $produit = null;
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getIdUser(): ?int
    {
        return $this->id_user;
    }
    
    public function setIdUser(int $id_user): static
    {
        $this->id_user = $id_user;
        
        return $this;
    }
    
    public function getNbrProduit(): ?int
    {
        return $this->nbr_produit;
    }
    
    public function setNbrProduit(int $nbr_produit): static
    {
        $this->nbr_produit = $nbr_produit;
        
        return $this;
    }
    
    public function getProduit(): ?Produit
    {
        return $this->produit;
    }
    
    public function setProduit(?Produit $produit): static
    {
        $this->produit = $produit;
        
        return $this;
    }

#[ORM\OneToOne(mappedBy: 'panier', targetEntity: Commande::class)]
private ?Commande $commande = null;

public function getCommande(): ?Commande
{
    return $this->commande;
}

public function setCommande(?Commande $commande): static
{
    $this->commande = $commande;
    return $this;
}
    
    // Calculate item total
    public function getTotal(): float
    {
        if ($this->produit) {
            return $this->nbr_produit * $this->produit->getPrix();
        }
        return 0;
    }
}