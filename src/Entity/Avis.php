<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\AvisRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=AvisRepository::class)
 *
 * @UniqueEntity(
 *     fields={"utilisateur", "serie"},
 *     errorPath="commentaire",
 *     message="You have already posted a review with your account for this series."
 * )
 */
class Avis
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Utilisateur::class, inversedBy="avis")
     * @ORM\JoinColumn(nullable=false)
     */
    private $utilisateur;

    /**
     * @ORM\ManyToOne(targetEntity=Serie::class, inversedBy="avis")
     * @ORM\JoinColumn(nullable=false)
     */
    private $serie;

    /**
     * @ORM\Column(type="integer")
     */
    private $note;

    /**
     * @ORM\Column(type="text")
     */
    private $commentaire;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getSerie(): ?Serie
    {
        return $this->serie;
    }

    public function setSerie(?Serie $serie): self
    {
        $this->serie = $serie;

        return $this;
    }

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(int $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $commentaire): self
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
