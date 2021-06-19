<?php

namespace App\Entity;

use App\Repository\DepenseRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DepenseRepository::class)
 */
class Depense
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     */
    private $montant;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity=CompteDepense::class, inversedBy="depenses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $compteDepense;

    /**
     * @ORM\ManyToOne(targetEntity=CategorieDepense::class, inversedBy="depenses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $categorie;

    /**
     * @ORM\ManyToOne(targetEntity=DepenseRecurrente::class, inversedBy="depenses")
     */
    private $depenseRecurrente;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): self
    {
        $this->montant = $montant;

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

    public function getCompteDepense(): ?compteDepense
    {
        return $this->compteDepense;
    }

    public function setCompteDepense(?compteDepense $compteDepense): self
    {
        $this->compteDepense = $compteDepense;

        return $this;
    }

    public function getCategorie(): ?CategorieDepense
    {
        return $this->categorie;
    }

    public function setCategorie(?CategorieDepense $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getDepenseRecurrente(): ?DepenseRecurrente
    {
        return $this->depenseRecurrente;
    }

    public function setDepenseRecurrente(?DepenseRecurrente $depenseRecurrente): self
    {
        $this->depenseRecurrente = $depenseRecurrente;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
