<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"email"}, message="Il existe déja un compte avec cette email !")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     *
     * @Assert\Email(
     *     message = "l'email '{{ value }}' n'est pas un email valide."
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=60)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=60)
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=40)
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/[0-9]{10}/"
     * )
     */
    private $telephone;

    /**
     * @ORM\Column(type="boolean")
     */
    private $EmailValider;

    /**
     * @ORM\OneToMany(targetEntity=ValidatorChangementMDP::class, mappedBy="compte", orphanRemoval=false)
     */
    private $ValidatorChangementMDPs;

    /**
     * @ORM\OneToMany(targetEntity=ValidatorMail::class, mappedBy="compte", orphanRemoval=false)
     */
    private $validatorMails;

    public function __construct()
    {
        $this->changementMDPs = new ArrayCollection();
        $this->validatorMails = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
      return $this->roles ;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getEmailValider(): ?bool
    {
        return $this->EmailValider;
    }

    public function setEmailValider(bool $EmailValider): self
    {
        $this->EmailValider = $EmailValider;

        return $this;
    }

    /**
     * @return Collection|ValidatorChangementMDP[]
     */
    public function getValidatorChangementMDPs(): Collection
    {
        return $this->ValidatorChangementMDPs;
    }

    public function addValidatorChangementMDP(ValidatorChangementMDP $ValidatorChangementMDP): self
    {
        if (!$this->ValidatorChangementMDPs->contains($ValidatorChangementMDP)) {
            $this->ValidatorChangementMDPs[] = $ValidatorChangementMDP;
            $ValidatorChangementMDP->setCompte($this);
        }

        return $this;
    }

    public function removeValidatorChangementMDP(ValidatorChangementMDP $ValidatorChangementMDP): self
    {
        if ($this->ValidatorChangementMDPs->removeElement($ValidatorChangementMDP)) {
            // set the owning side to null (unless already changed)
            if ($ValidatorChangementMDP->getCompte() === $this) {
                $ValidatorChangementMDP->setCompte(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ValidatorMail[]
     */
    public function getValidatorMails(): Collection
    {
        return $this->validatorMails;
    }

    public function addValidatorMail(ValidatorMail $validatorMail): self
    {
        if (!$this->validatorMails->contains($validatorMail)) {
            $this->validatorMails[] = $validatorMail;
            $validatorMail->setCompte($this);
        }

        return $this;
    }

    public function removeValidatorMail(ValidatorMail $validatorMail): self
    {
        if ($this->validatorMails->removeElement($validatorMail)) {
            // set the owning side to null (unless already changed)
            if ($validatorMail->getCompte() === $this) {
                $validatorMail->setCompte(null);
            }
        }

        return $this;
    }

}
