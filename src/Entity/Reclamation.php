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
        minMessage: "Description must be at least 5 characters",
        maxMessage: "Description cannot exceed 50 characters"
    )]
    private string $description;

    #[ORM\Column(type: "date")]
    #[Assert\NotNull(message: "Creation date cannot be null")]
    #[Assert\Type("\DateTimeInterface", message: "Invalid date format")]
    private \DateTimeInterface $dateCreation;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Status cannot be blank")]
    #[Assert\Choice(
        choices: ["OPEN", "IN PROGRESS", "RESOLVED", "CLOSED"],
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

    #[ORM\Column(type: "integer")]
    #[Assert\NotNull(message: "User ID cannot be null")]
    #[Assert\Positive(message: "User ID must be a positive integer")]
    private int $userId;

    #[ORM\Column(type: "string", length: 500)]
    #[Assert\Length(
        max: 500,

        maxMessage: "Answer cannot exceed {{ limit }} characters"
    )]
    private string $answer;

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

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId($value)
    {
        $this->userId = $value;
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