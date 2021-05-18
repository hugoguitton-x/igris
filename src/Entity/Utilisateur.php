<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UtilisateurRepository")
 * @UniqueEntity(
 *  fields={"email"},
 *  message="The email you entered is already in use"
 * )
 * @UniqueEntity(
 *  fields={"username"},
 *  message="The username you entered is already in use."
 * )
 */
class Utilisateur implements UserInterface
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
  private $username;

  /**
   * @ORM\Column(type="string", length=255)
   * @Assert\Email()
   */
  private $email;

  /**
   * @ORM\Column(type="string", length=255)
   */
  private $firstname;

  /**
   * @ORM\Column(type="string", length=255)
   */
  private $lastname;

  /**
   * @ORM\Column(type="jsonb")
   */
  private $roles = [];

  /**
   * @ORM\Column(type="string", length=255)
   * @Assert\Length(min="8", minMessage="Your password must be at least {{ limit }} characters long.")
   */
  private $password;

  /**
   * @Assert\EqualTo(propertyPath="password", message="You did not enter the same password.")
   */
  private $password_confirm;

  /**
   * @ORM\OneToMany(targetEntity=Avis::class, mappedBy="utilisateur", orphanRemoval=true)
   */
  private $avis;

  /**
   * @ORM\Column(type="datetime", nullable=true)
   */
  private $lastLogin;

  /**
   * @ORM\Column(type="string", length=255)
   */
  private $avatar;

  /**
   * @ORM\Column(type="string", length=255, nullable=true)
   */
  private $NameTwitter;

  /**
   * @ORM\OneToMany(targetEntity=FollowManga::class, mappedBy="utilisateur", orphanRemoval=true)
   */
  private $followMangas;

  /**
   * @ORM\OneToMany(targetEntity=CompteDepense::class, mappedBy="utilisateur", orphanRemoval=true)
   */
  private $comptesDepense;

  public function __construct()
  {
    $this->documents = new ArrayCollection();
    $this->avis = new ArrayCollection();
    $this->followMangas = new ArrayCollection();
    $this->comptesDepense = new ArrayCollection();
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getUsername(): ?string
  {
    return mb_strtolower($this->username);
  }

  public function setUsername(string $username): self
  {
    $this->username = mb_strtolower($username);

    return $this;
  }

  public function getEmail(): ?string
  {
    return $this->email;
  }

  public function setEmail(string $email): self
  {
    $this->email = $email;

    return $this;
  }

  public function getFirstname(): ?string
  {
    return $this->firstname;
  }

  public function setFirstname(string $firstname): self
  {
    $this->firstname = $firstname;

    return $this;
  }

  public function getLastname(): ?string
  {
    return $this->lastname;
  }

  public function setLastname(string $lastname): self
  {
    $this->lastname = $lastname;

    return $this;
  }

  public function getRoles(): ?array
  {
    return $this->roles;
  }

  public function setRoles(array $roles): self
  {
    $this->roles = $roles;

    return $this;
  }

  public function getPassword(): ?string
  {
    return $this->password;
  }

  public function setPassword(string $password): self
  {
    $this->password = $password;

    return $this;
  }

  public function getPasswordConfirm(): ?string
  {
    return $this->password_confirm;
  }

  public function setPasswordConfirm(string $password_confirm): self
  {
    $this->password_confirm = $password_confirm;

    return $this;
  }

  public function eraseCredentials()
  {
  }

  public function getSalt()
  {
  }

  public function getFirstnameLastname(): ?string
  {
    return $this->firstname . ' ' . $this->lastname;
  }

  /**
   * @return Collection|Avis[]
   */
  public function getAvis(): Collection
  {
    return $this->avis;
  }

  public function addAvi(Avis $avis): self
  {
    if (!$this->avis->contains($avis)) {
      $this->avis[] = $avis;
      $avis->setUtilisateur($this);
    }

    return $this;
  }

  public function removeAvi(Avis $avis): self
  {
    if ($this->avis->contains($avis)) {
      $this->avis->removeElement($avis);
      // set the owning side to null (unless already changed)
      if ($avis->getUtilisateur() === $this) {
        $avis->setUtilisateur(null);
      }
    }

    return $this;
  }

  public function getLastLogin(): ?\DateTimeInterface
  {
    return $this->lastLogin;
  }

  public function setLastLogin(?\DateTimeInterface $lastLogin): self
  {
    $this->lastLogin = $lastLogin;

    return $this;
  }

  public function getAvatar(): ?string
  {
    return $this->avatar;
  }

  public function setAvatar(?string $avatar): self
  {
    $this->avatar = $avatar;

    return $this;
  }

  public function getNameTwitter(): ?string
  {
      return $this->NameTwitter;
  }

  public function setNameTwitter(?string $NameTwitter): self
  {
      $this->NameTwitter = $NameTwitter;

      return $this;
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
          $followManga->setUtilisateur($this);
      }

      return $this;
  }

  public function removeFollowManga(FollowManga $followManga): self
  {
      if ($this->followMangas->removeElement($followManga)) {
          // set the owning side to null (unless already changed)
          if ($followManga->getUtilisateur() === $this) {
              $followManga->setUtilisateur(null);
          }
      }

      return $this;
  }

  /**
   * @return Collection|CompteDepense[]
   */
  public function getComptesDepense(): Collection
  {
      return $this->comptesDepense;
  }

  public function addComptesDepense(CompteDepense $comptesDepense): self
  {
      if (!$this->comptesDepense->contains($comptesDepense)) {
          $this->comptesDepense[] = $comptesDepense;
          $comptesDepense->setUtilisateur($this);
      }

      return $this;
  }

  public function removeComptesDepense(CompteDepense $comptesDepense): self
  {
      if ($this->comptesDepense->removeElement($comptesDepense)) {
          // set the owning side to null (unless already changed)
          if ($comptesDepense->getUtilisateur() === $this) {
              $comptesDepense->setUtilisateur(null);
          }
      }

      return $this;
  }

}
