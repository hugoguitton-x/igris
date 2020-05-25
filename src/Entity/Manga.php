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
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $rss;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\LastChapter", mappedBy="manga")
     */
    private $lastChapter;

    public function __construct()
    {
        $this->lastChapter = new ArrayCollection();
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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getRss(): ?string
    {
        return $this->rss;
    }

    public function setRss(string $rss): self
    {
        $this->rss = $rss;

        return $this;
    }

    /**
     * @return Collection|LastChapter[]
     */
    public function getLastChapter(): Collection
    {
        return $this->lastChapter;
    }

    public function addLastChapter(LastChapter $lastChapter): self
    {
        if (!$this->lastChapter->contains($lastChapter)) {
            $this->lastChapter[] = $lastChapter;
            $lastChapter->setManga($this);
        }

        return $this;
    }

    public function removeLastChapter(LastChapter $lastChapter): self
    {
        if ($this->lastChapter->contains($lastChapter)) {
            $this->lastChapter->removeElement($lastChapter);
            // set the owning side to null (unless already changed)
            if ($lastChapter->getManga() === $this) {
                $lastChapter->setManga(null);
            }
        }

        return $this;
    }
}
