<?php

namespace App\Entity;

use App\Repository\DepenseRecurrenteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DepenseRecurrenteRepository::class)
 */
class DepenseRecurrente
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
     * @ORM\ManyToOne(targetEntity=CategorieDepense::class, inversedBy="depensesRecurrentes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $categorie;

    /**
     * @ORM\ManyToOne(targetEntity=CompteDepense::class, inversedBy="depensesRecurrentes")
     */
    private $compteDepense;

    /**
     * @ORM\OneToMany(targetEntity=Depense::class, mappedBy="depenseRecurrente")
     */
    private $depenses;

    public function __construct()
    {
        $this->depenses = new ArrayCollection();
    }

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

    public function getCategorie(): ?CategorieDepense
    {
        return $this->categorie;
    }

    public function setCategorie(?CategorieDepense $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getCompteDepense(): ?CompteDepense
    {
        return $this->compteDepense;
    }

    public function setCompteDepense(?CompteDepense $compteDepense): self
    {
        $this->compteDepense = $compteDepense;

        return $this;
    }

    /**
     * @return Collection|Depense[]
     */
    public function getDepenses(): Collection
    {
        return $this->depenses;
    }

    public function addDepense(Depense $depense): self
    {
        if (!$this->depenses->contains($depense)) {
            $this->depenses[] = $depense;
            $depense->setDepenseRecurrente($this);
        }

        return $this;
    }

    public function removeDepense(Depense $depense): self
    {
        if ($this->depenses->removeElement($depense)) {
            // set the owning side to null (unless already changed)
            if ($depense->getDepenseRecurrente() === $this) {
                $depense->setDepenseRecurrente(null);
            }
        }

        return $this;
    }
}
