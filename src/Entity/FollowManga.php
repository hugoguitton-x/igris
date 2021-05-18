<?php

namespace App\Entity;

use App\Repository\FollowMangaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FollowMangaRepository::class)
 */
class FollowManga
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Manga::class, inversedBy="followMangas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $manga;

    /**
     * @ORM\ManyToOne(targetEntity=Utilisateur::class, inversedBy="followMangas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $utilisateur;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getManga(): ?Manga
    {
        return $this->manga;
    }

    public function setManga(?Manga $manga): self
    {
        $this->manga = $manga;

        return $this;
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
}
