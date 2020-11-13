<?php

namespace App\Entity;

use App\Repository\ChapterRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ChapterRepository::class)
 */
class Chapter
{
  /**
   * @ORM\Id()
   * @ORM\GeneratedValue()
   * @ORM\Column(type="integer")
   */
  private $id;

  /**
   * @ORM\Column(type="integer")
   */
  private $chapter_id;

  /**
   * @ORM\Column(type="float")
   */
  private $number;

  /**
   * @ORM\ManyToOne(targetEntity=LanguageCode::class, inversedBy="chapters")
   * @ORM\JoinColumn(nullable=false)
   */
  private $langCode;

  /**
   * @ORM\ManyToOne(targetEntity=Manga::class, inversedBy="chapters")
   * @ORM\JoinColumn(nullable=false)
   */
  private $manga;

  /**
   * @ORM\Column(type="datetime")
   */
  private $date;

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getChapterId(): ?int
  {
    return $this->chapter_id;
  }

  public function setChapterId(int $chapter_id): self
  {
    $this->chapter_id = $chapter_id;

    return $this;
  }

  public function getNumber(): ?string
  {
    return $this->number;
  }

  public function setNumber(string $number): self
  {
    $this->number = $number;

    return $this;
  }

  public function getLangCode(): ?LanguageCode
  {
    return $this->langCode;
  }

  public function setLangCode(?LanguageCode $langCode): self
  {
    $this->langCode = $langCode;

    return $this;
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

  public function getDate(): ?\DateTimeInterface
  {
    return $this->date;
  }

  public function setDate(\DateTimeInterface $date): self
  {
    $this->date = $date;

    return $this;
  }
}
