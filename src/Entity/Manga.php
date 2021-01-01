<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MangaRepository")
 */
class Manga
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
  private $name;

  /**
   * @ORM\Column(type="string", length=255)
   */
  private $image;

  /**
   * @ORM\OneToMany(targetEntity=Chapter::class, mappedBy="manga", orphanRemoval=true)
   */
  private $chapters;

  /**
   * @ORM\Column(type="integer")
   */
  private $mangaId;

  /**
   * @ORM\Column(type="boolean")
   */
  private $Twitter;

  /**
   * @ORM\OneToMany(targetEntity=FollowManga::class, mappedBy="manga", orphanRemoval=true)
   */
  private $followMangas;

  /**
   * @ORM\Column(type="datetime")
   */
  private $lastUploaded;

  public function __construct()
  {
    $this->lastChapter = new ArrayCollection();
    $this->chapters = new ArrayCollection();
    $this->Utilisateur = new ArrayCollection();
    $this->followMangas = new ArrayCollection();
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getName(): ?string
  {
    return $this->name;
  }

  public function setName(string $name): self
  {
    $this->name = $name;

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

  /**
   * @return Collection|Chapter[]
   */
  public function getChapters(): Collection
  {
    return $this->chapters;
  }

  public function addChapter(Chapter $chapter): self
  {
    if (!$this->chapters->contains($chapter)) {
      $this->chapters[] = $chapter;
      $chapter->setManga($this);
    }

    return $this;
  }

  public function removeChapter(Chapter $chapter): self
  {
    if ($this->chapters->contains($chapter)) {
      $this->chapters->removeElement($chapter);
      // set the owning side to null (unless already changed)
      if ($chapter->getManga() === $this) {
        $chapter->setManga(null);
      }
    }

    return $this;
  }

  public function getMangaId(): ?int
  {
    return $this->mangaId;
  }

  public function setMangaId(int $mangaId): self
  {
    $this->mangaId = $mangaId;

    return $this;
  }

  public function getTwitter(): ?bool
  {
    return $this->Twitter;
  }

  public function setTwitter(bool $Twitter): self
  {
    $this->Twitter = $Twitter;

    return $this;
  }

  /**
   * @return Collection|MangaFollowed[]
   */
  public function getUtilisateur(): Collection
  {
      return $this->Utilisateur;
  }

  /**
   * @return Collection|FollowManga[]
   */
  public function getFollowMangas(): Collection
  {
      return $this->followMangas;
  }

  public function addFollowManga(FollowManga $followManga): self
  {
      if (!$this->followMangas->contains($followManga)) {
          $this->followMangas[] = $followManga;
          $followManga->setManga($this);
      }

      return $this;
  }

  public function removeFollowManga(FollowManga $followManga): self
  {
      if ($this->followMangas->removeElement($followManga)) {
          // set the owning side to null (unless already changed)
          if ($followManga->getManga() === $this) {
              $followManga->setManga(null);
          }
      }

      return $this;
  }

  /**
   * Manga is followed by user or not
   *
   * @param Utilisateur $utilisateur
   * @return boolean
   */
  public function isFollowedByUser(Utilisateur $utilisateur): bool
  {
    foreach($this->followMangas as $followManga) {
      if($followManga->getUtilisateur() === $utilisateur) {
        return true;
      }
    }

    return false;
  }

  public function getLastUploaded(): ?\DateTimeInterface
  {
      return $this->lastUploaded;
  }

  public function setLastUploaded(?\DateTimeInterface $lastUploaded): self
  {
      $this->lastUploaded = $lastUploaded;

      return $this;
  }
}
