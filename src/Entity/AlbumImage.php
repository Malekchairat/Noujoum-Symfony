<?php
namespace App\Entity;

use App\Repository\AlbumImageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AlbumImageRepository::class)]
class AlbumImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $imagePath = null;

    #[ORM\ManyToOne(targetEntity: Produit::class, inversedBy: 'albumImages')]
    #[ORM\JoinColumn(name: "produit_id", referencedColumnName: "id", nullable: false)]
    private ?Produit $produit = null;

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(string $imagePath): self
    {
        $this->imagePath = $imagePath;
        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): self
    {
        $this->produit = $produit;
        return $this;
    }
}
