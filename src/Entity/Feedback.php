<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Feedback
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "integer")]
    #[Assert\NotNull(message: "Reclamation ID cannot be null")]
    #[Assert\Positive(message: "Reclamation ID must be a positive integer")]
    private int $reclamationId;  // changed to camelCase

    #[ORM\Column(type: "integer")]
    #[Assert\NotNull(message: "User ID cannot be null")]
    #[Assert\Positive(message: "User ID must be a positive integer")]
    private int $utilisateurId;  // changed to camelCase

    #[ORM\Column(type: "integer")]
    #[Assert\NotNull(message: "Rating cannot be null")]
    #[Assert\Range(
        min: 1,
        max: 5,
        notInRangeMessage: "Rating must be between {{ min }} and {{ max }}"
    )]
    private int $note;

    #[ORM\Column(type: "text")]
    #[Assert\NotBlank(message: "Comment cannot be blank")]
    #[Assert\Length(
        min: 10,
        max: 1000,
        minMessage: "Comment must be at least {{ limit }} characters",
        maxMessage: "Comment cannot exceed {{ limit }} characters"
    )]
    private string $commentaire;

    #[ORM\Column(type: "date")]
    #[Assert\NotNull(message: "Feedback date cannot be null")]
    #[Assert\Type("\DateTimeInterface", message: "Invalid date format")]
    private \DateTimeInterface $dateFeedback;  // changed to camelCase

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $value): self
    {
        $this->id = $value;
        return $this;
    }

    public function getReclamationId(): int  // changed to camelCase
    {
        return $this->reclamationId;
    }

    public function setReclamationId(int $value): self  // changed to camelCase
    {
        $this->reclamationId = $value;
        return $this;
    }

    public function getUtilisateurId(): int  // changed to camelCase
    {
        return $this->utilisateurId;
    }

    public function setUtilisateurId(int $value): self  // changed to camelCase
    {
        $this->utilisateurId = $value;
        return $this;
    }

    public function getNote(): int
    {
        return $this->note;
    }

    public function setNote(int $value): self
    {
        $this->note = $value;
        return $this;
    }

    public function getCommentaire(): string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $value): self
    {
        $this->commentaire = $value;
        return $this;
    }

    public function getDateFeedback(): \DateTimeInterface  // changed to camelCase
    {
        return $this->dateFeedback;
    }

    public function setDateFeedback(\DateTimeInterface $value): self  // changed to camelCase
    {
        $this->dateFeedback = $value;
        return $this;
    }
}