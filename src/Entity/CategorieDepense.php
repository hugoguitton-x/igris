<?php

namespace App\Entity;

use App\Repository\CategorieDepenseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CategorieDepenseRepository::class)
 */
class CategorieDepense
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $libelle;

    /**
     * @ORM\OneToMany(targetEntity=DepenseRecurrente::class, mappedBy="categorie", orphanRemoval=true)
     */
    private $depensesRecurrentes;

    /**
     * @ORM\OneToMany(targetEntity=Depense::class, mappedBy="categorie", orphanRemoval=true)
     */
    private $depenses;

    public function __construct()
    {
        $this->depensesRecurrentes = new ArrayCollection();
        $this->depenses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
     * @return Collection|DepenseRecurrente[]
     */
    public function getDepensesRecurrentes(): Collection
    {
        return $this->depensesRecurrentes;
    }

    public function addDepensesRecurrente(DepenseRecurrente $depensesRecurrente): self
    {
        if (!$this->depensesRecurrentes->contains($depensesRecurrente)) {
            $this->depensesRecurrentes[] = $depensesRecurrente;
            $depensesRecurrente->setCategorie($this);
        }

        return $this;
    }

    public function removeDepensesRecurrente(DepenseRecurrente $depensesRecurrente): self
    {
        if ($this->depensesRecurrentes->removeElement($depensesRecurrente)) {
            // set the owning side to null (unless already changed)
            if ($depensesRecurrente->getCategorie() === $this) {
                $depensesRecurrente->setCategorie(null);
            }
        }

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
            $depense->setCategorie($this);
        }

        return $this;
    }

    public function removeDepense(Depense $depense): self
    {
        if ($this->depenses->removeElement($depense)) {
            // set the owning side to null (unless already changed)
            if ($depense->getCategorie() === $this) {
                $depense->setCategorie(null);
            }
        }

        return $this;
    }
}
