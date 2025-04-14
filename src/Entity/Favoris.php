<?php

namespace App\Entity;

use App\Repository\FavorisRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: FavorisRepository::class)]
class Favoris
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", name: "id_favoris")]
    private ?int $idFavoris = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "id_user", referencedColumnName: "id_user", nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Produit::class)]
    #[ORM\JoinColumn(name: "id_produit", referencedColumnName: "id", nullable: false)]
    private ?Produit $produit = null;

    #[ORM\Column(type: "datetime", name: "date")]
    private \DateTime $date;

    public function getIdFavoris(): ?int
    {
        return $this->idFavoris;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
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

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Add user to the favorites
     *
     * @param User $user
     * @param Produit $produit
     * @return Favoris
     */
    public static function createFavori(User $user, Produit $produit): self
    {
        $favoris = new self();
        $favoris->setUser($user)
                ->setProduit($produit)
                ->setDate(new \DateTime()); // Set the current date and time
        
        return $favoris;
    }
}
