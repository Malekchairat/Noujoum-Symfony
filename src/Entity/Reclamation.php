<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Reclamation
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id_user", nullable: false)]
    private User $user;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Title cannot be blank")]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: "Title must be at least {{ limit }} characters",
        maxMessage: "Title cannot exceed {{ limit }} characters"
    )]
    private string $titre;

    #[ORM\Column(type: "text")]
    #[Assert\NotBlank(message: "Description cannot be blank")]
    #[Assert\Length(
        min: 10,
        max: 2000,
        minMessage: "Description must be at least {{ limit }} characters",
        maxMessage: "Description cannot exceed {{ limit }} characters"
    )]
    private string $description;

    #[ORM\Column(type: "date")]
    #[Assert\NotNull(message: "Creation date cannot be null")]
    #[Assert\Type("\DateTimeInterface", message: "Invalid date format")]
    private \DateTimeInterface $dateCreation;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Status cannot be blank")]
    #[Assert\Choice(
        choices: ["OPEN", "IN_PROGRESS", "RESOLVED", "CLOSED"],
        message: "Invalid status value"
    )]
    private string $statut;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Priority cannot be blank")]
    #[Assert\Choice(
        choices: ["LOW", "MEDIUM", "HIGH", "CRITICAL"],
        message: "Invalid priority value"
    )]
    private string $priorite;

    #[ORM\Column(type: "string", length: 500, nullable: true)]
    #[Assert\Length(
        max: 500,
        maxMessage: "Answer cannot exceed {{ limit }} characters"
    )]
    private ?string $answer = null;

    // ... (keep all your existing getters and setters) ...

    // Remove the getUserId() and setUserId() methods since we'll use the User object directly
    // Keep only these user-related methods:

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }


    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getTitre()
    {
        return $this->titre;
    }

    public function setTitre($value)
    {
        $this->titre = $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    public function setDateCreation($value)
    {
        $this->dateCreation = $value;
    }

    public function getStatut()
    {
        return $this->statut;
    }

    public function setStatut($value)
    {
        $this->statut = $value;
    }

    public function getPriorite()
    {
        return $this->priorite;
    }

    public function setPriorite($value)
    {
        $this->priorite = $value;
    }

   

    public function getAnswer()
    {
        return $this->answer;
    }

    public function setAnswer($value)
    {
        $this->answer = $value;
    }
}





