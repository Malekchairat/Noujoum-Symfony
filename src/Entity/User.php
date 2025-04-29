<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Reclamation;

#[ORM\Entity]
class User
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id_user;

    #[ORM\Column(type: "string", length: 255)]
    private string $nom;

    #[ORM\Column(type: "string", length: 255)]
    private string $prenom;

    #[ORM\Column(type: "string", length: 255)]
    private string $email;

    #[ORM\Column(type: "string", length: 255)]
    private string $mdp;

    #[ORM\Column(type: "integer")]
    private int $tel;

    #[ORM\Column(type: "string", length: 255)]
    private string $role;

    #[ORM\Column(type: "string", length: 65535)]
    private string $image;

    #[ORM\OneToMany(mappedBy: "user", targetEntity: Reclamation::class, orphanRemoval: true)]
    private Collection $reclamations;

    public function __construct()
    {
        $this->reclamations = new ArrayCollection();
    }

    // ✅ Getters & setters de base...

    public function getId_user(): int
    {
        return $this->id_user;
    }

    public function setId_user(int $value): void
    {
        $this->id_user = $value;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $value): void
    {
        $this->nom = $value;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function setPrenom(string $value): void
    {
        $this->prenom = $value;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $value): void
    {
        $this->email = $value;
    }

    public function getMdp(): string
    {
        return $this->mdp;
    }

    public function setMdp(string $value): void
    {
        $this->mdp = $value;
    }

    public function getTel(): int
    {
        return $this->tel;
    }

    public function setTel(int $value): void
    {
        $this->tel = $value;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $value): void
    {
        $this->role = $value;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function setImage(string $value): void
    {
        $this->image = $value;
    }

    // ✅ Relations : Reclamations

    public function getReclamations(): Collection
    {
        return $this->reclamations;
    }

    public function addReclamation(Reclamation $reclamation): self
    {
        if (!$this->reclamations->contains($reclamation)) {
            $this->reclamations[] = $reclamation;
            $reclamation->setUser($this);
        }

        return $this;
    }

    public function removeReclamation(Reclamation $reclamation): self
    {
        if ($this->reclamations->removeElement($reclamation)) {
            if ($reclamation->getUser() === $this) {
                $reclamation->setUser(null);
            }
        }

        return $this;
    }
}
