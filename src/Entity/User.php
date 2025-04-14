<?php

// src/Entity/User.php

namespace App\Entity;

use App\Enum\RoleEnum;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé.')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_user', type: 'integer')]
    private ?int $id_user = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $mdp = null;

    #[ORM\Column(type: 'integer')]
    private $tel;

    #[ORM\Column(type: 'string', length: 30)]
    private string $role;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\OneToMany(targetEntity: Favoris::class, mappedBy: 'user')]
    private Collection $favoris;

    public function __construct()
    {
        $this->favoris = new ArrayCollection();
    }

    public function getIdUser(): ?int
    {
        return $this->id_user;
    }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(?string $nom): self
{
    $this->nom = $nom;
    return $this;
}

    public function getPrenom(): ?string { return $this->prenom; }
    public function setPrenom(?string $prenom): self
{
    $this->prenom = $prenom;
    return $this;
}

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): static
    {
        if ($email !== null) {
            $this->email = $email;
        }
        return $this;
    }
    
    public function getMdp(): ?string { return $this->mdp; }
    public function setMdp(string $mdp): static { $this->mdp = $mdp; return $this; }

    public function getTel(): ?string
    {
        return $this->tel;
    }
        public function setTel(?string $tel): static
    {
        if ($tel !== null) {
            $this->tel = $tel;
        }
        return $this;
    }
    
    public function getRole(): RoleEnum { return RoleEnum::from($this->role); }
    public function setRole(RoleEnum $role): self { $this->role = $role->value; return $this; }

    public function getImage(): ?string { return $this->image; }
    public function setImage(?string $image): static { $this->image = $image; return $this; }

    public function getUserIdentifier(): string { return $this->email; }
    public function getPassword(): string { return $this->mdp; }

    public function getRoles(): array { return [$this->getRole()->value]; }
    public function getSalt(): ?string { return null; }
    public function eraseCredentials() {}

    public function __toString(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }

    public function getFavoris(): Collection { return $this->favoris; }

    public function addFavori(Favoris $favori): static
    {
        if (!$this->favoris->contains($favori)) {
            $this->favoris->add($favori);
            $favori->setUser($this);
        }
        return $this;
    }

    public function removeFavori(Favoris $favori): static
    {
        if ($this->favoris->removeElement($favori)) {
            if ($favori->getUser() === $this) {
                $favori->setUser(null);
            }
        }
        return $this;
    }
}
