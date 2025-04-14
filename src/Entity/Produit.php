<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $categorie = null;

    #[ORM\Column]
    private ?float $prix = null;

    #[ORM\Column]
    private ?int $disponibilite = null;
    
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $imageName; // Changement de type (anciennement 'blob')
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(string $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getDisponibilite(): ?int
    {
        return $this->disponibilite;
    }

    public function setDisponibilite(int $disponibilite): static
    {
        $this->disponibilite = $disponibilite;

        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    
    public function setImageName(?string $imageName): self
    {
        $this->imageName = $imageName;
        return $this;
    }

    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: Promotion::class, orphanRemoval: true)]
    private Collection $promotions;

    /**
     * @var Collection<int, AlbumImage>
     */
    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: AlbumImage::class)]
    private Collection $albumImages;

    public function __construct()
    {
        $this->promotions = new ArrayCollection();
        $this->albumImages = new ArrayCollection();
    }
    public function getPromotions(): Collection
    {
        return $this->promotions;
    }

    public function addPromotion(Promotion $promotion): self
    {
        if (!$this->promotions->contains($promotion)) {
            $this->promotions[] = $promotion;
            $promotion->setProduit($this);
        }

        return $this;
    }

    public function removePromotion(Promotion $promotion): self
    {
        if ($this->promotions->removeElement($promotion)) {
            if ($promotion->getProduit() === $this) {
                $promotion->setProduit(null);
            }
        }

        return $this;
    }
    public function getPrixPromo(): ?float
    {
        $now = new \DateTime();
    
        foreach ($this->promotions as $promotion) {
            if ($promotion->getExpiration() >= $now) {
                return $this->prix * (1 - ($promotion->getPourcentage() / 100));
            }
        }
    
        return null;
    }

    /**
     * @return Collection<int, AlbumImage>
     */
    public function getAlbumImages(): Collection
    {
        return $this->albumImages;
    }

    public function addAlbumImage(AlbumImage $albumImage): static
    {
        if (!$this->albumImages->contains($albumImage)) {
            $this->albumImages->add($albumImage);
            $albumImage->setProduit($this);
        }

        return $this;
    }

    public function removeAlbumImage(AlbumImage $albumImage): static
    {
        if ($this->albumImages->removeElement($albumImage)) {
            // set the owning side to null (unless already changed)
            if ($albumImage->getProduit() === $this) {
                $albumImage->setProduit(null);
            }
        }

        return $this;
    }
    
}
