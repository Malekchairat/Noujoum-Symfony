<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Panier::class, inversedBy: "commande")]
    #[ORM\JoinColumn(name: 'id_panier', referencedColumnName: 'id', unique: true)]
    private ?Panier $panier = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'adresse ne peut pas être vide")]
    #[Assert\Length(
        max: 30,
        maxMessage: "L'adresse ne peut pas dépasser {{ limit }} caractères"
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-ZÀ-ÿ0-9\s\-\'\.]+$/",
        message: "L'adresse ne peut contenir que des lettres, chiffres et certains caractères spéciaux"
    )]
    private ?string $rue = null;
        
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La ville ne peut pas être vide")]
    #[Assert\Length(
        max: 10,
        maxMessage: "La ville ne peut pas dépasser {{ limit }} caractères"
    )]
    /* **#[Assert\Regex(
        pattern: "/^[a-zA-ZÀ-ÿ\s\-]+$/",
        message: "La ville ne peut contenir que des lettres"
    )] */
    private ?string $ville = null;

    #[ORM\Column(length: 255, name: "code_postal")]
    #[Assert\NotBlank(message: "Le code postal ne peut pas être vide")]
    #[Assert\Regex(
        pattern: "/^\d{4,5}$/",
        message: "Le code postal doit être composé de 4 ou 5 chiffres"
    )]
    private ?string $code_postal = null;

    #[ORM\Column]
    private ?string $etat = null;

    #[ORM\Column(name: "montant_total")]
    private ?int $montantTotal = null;

    #[ORM\Column(length: 255)]
    private ?string $methodePaiment = null;

    #[ORM\Column]
    private ?int $id_user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPanier(): ?Panier
    {
        return $this->panier;
    }

    public function setPanier(?Panier $panier): static
    {
        $this->panier = $panier;
        return $this;
    }

    public function getRue(): ?string
    {
        return $this->rue;
    }

    public function setRue(string $rue): static
    {
        $this->rue = $rue;
        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): static
    {
        $this->ville = $ville;
        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->code_postal;
    }

    public function setCodePostal(string $code_postal): static
    {
        $this->code_postal = $code_postal;
        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;
        return $this;
    }

    public function getMontantTotal(): ?int
    {
        return $this->montantTotal;
    }

    public function setMontantTotal(int $montantTotal): static
    {
        $this->montantTotal = $montantTotal;
        return $this;
    }

    public function getMethodePaiment(): ?string
    {
        return $this->methodePaiment;
    }

    public function setMethodePaiment(string $methodePaiment): static
    {
        $this->methodePaiment = $methodePaiment;
        return $this;
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
}