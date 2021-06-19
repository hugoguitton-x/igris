<?php

namespace App\Entity;

use App\Repository\LanguageCodeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LanguageCodeRepository::class)
 */
class LanguageCode
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
    private $langCode;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $libelle;

    /**
     * @ORM\OneToMany(targetEntity=Chapter::class, mappedBy="langCode", orphanRemoval=true)
     */
    private $chapters;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $TwitterFlag;

    public function __construct()
    {
        $this->chapters = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLangCode(): ?string
    {
        return $this->langCode;
    }

    public function setLangCode(string $langCode): self
    {
        $this->langCode = $langCode;

        return $this;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

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
            $chapter->setLangCode($this);
        }

        return $this;
    }

    public function removeChapter(Chapter $chapter): self
    {
        if ($this->chapters->contains($chapter)) {
            $this->chapters->removeElement($chapter);
            // set the owning side to null (unless already changed)
            if ($chapter->getLangCode() === $this) {
                $chapter->setLangCode(null);
            }
        }

        return $this;
    }

    public function getTwitterFlag(): ?string
    {
        return $this->TwitterFlag;
    }

    public function setTwitterFlag(string $TwitterFlag): self
    {
        $this->TwitterFlag = $TwitterFlag;

        return $this;
    }
}
