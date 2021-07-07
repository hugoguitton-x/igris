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
     * @ORM\Column(type="string")
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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $volume;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @ORM\JoinColumn(nullable=false)
     */
    private $publishedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChapterId(): ?string
    {
        return $this->chapter_id;
    }

    public function setChapterId(string $chapter_id): self
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getVolume(): ?string
    {
        return $this->volume;
    }

    public function setVolume(string $volume): self
    {
        $this->volume = $volume;

        return $this;
    }

    public function getPublishedAt(): \DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTimeInterface $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }
}
