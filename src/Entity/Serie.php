<?php

namespace App\Entity;

use App\Repository\SerieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SerieRepository::class)
 */
class Serie
{
  /**
   * @ORM\Id()
   * @ORM\GeneratedValue()
   * @ORM\Column(type="integer")
   */
  private $id;

  /**
   * @ORM\Column(type="string", length=255)
   */
  private $nom;

  /**
   * @ORM\Column(type="string", length=255)
   */
  private $image;

  /**
   * @ORM\Column(type="text")
   */
  private $synopsis;

  /**
   * @ORM\Column(type="string", length=255)
   */
  private $lien;

  /**
   * @ORM\OneToMany(targetEntity=Avis::class, mappedBy="serie", orphanRemoval=true)
   */
  private $avis;

  /**
   * @ORM\Column(type="integer")
   */
  private $nombreEpisodes;

  /**
   * @ORM\Column(type="integer")
   */
  private $dureeEpisode;

  /**
   * @ORM\Column(type="datetime")
   */
  private $createdAt;

  /**
   * @ORM\Column(type="float")
   */
  private $noteMoyenne;


  public function __construct()
  {
    $this->avis = new ArrayCollection();
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getNom(): ?string
  {
    return $this->nom;
  }

  public function setNom(string $nom): self
  {
    $this->nom = $nom;

    return $this;
  }

  public function getImage(): ?string
  {
    return $this->image;
  }

  public function setImage(string $image): self
  {
    $this->image = $image;

    return $this;
  }

  public function getSynopsis(): ?string
  {
    return $this->synopsis;
  }

  public function setSynopsis(string $synopsis): self
  {
    $this->synopsis = $synopsis;

    return $this;
  }

  public function getLien(): ?string
  {
    return $this->lien;
  }

  public function setLien(string $lien): self
  {
    $this->lien = $lien;

    return $this;
  }

  /**
   * @return Collection|Avis[]
   */
  public function getAvis(): Collection
  {
    return $this->avis;
  }

  public function addAvi(Avis $avi): self
  {
    if (!$this->avis->contains($avi)) {
      $this->avis[] = $avi;
      $avi->setSerie($this);
    }

    return $this;
  }

  public function removeAvi(Avis $avi): self
  {
    if ($this->avis->contains($avi)) {
      $this->avis->removeElement($avi);
      // set the owning side to null (unless already changed)
      if ($avi->getSerie() === $this) {
        $avi->setSerie(null);
      }
    }

    return $this;
  }

  public function getNombreEpisodes(): ?int
  {
    return $this->nombreEpisodes;
  }

  public function setNombreEpisodes(int $nombreEpisodes): self
  {
    $this->nombreEpisodes = $nombreEpisodes;

    return $this;
  }

  public function getDureeEpisode(): ?int
  {
    return $this->dureeEpisode;
  }

  public function setDureeEpisode(int $dureeEpisode): self
  {
    $this->dureeEpisode = $dureeEpisode;

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

  public function getNoteMoyenne(): ?float
  {
    return $this->noteMoyenne;
  }

  public function setNoteMoyenne(float $noteMoyenne): self
  {
    $this->noteMoyenne = $noteMoyenne;

    return $this;
  }
}
