<?php

namespace App\Entity;

use App\Repository\ValidatorMailRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Service\GenerateurCode ;

/**
 * @ORM\Entity(repositoryClass=ValidatorMailRepository::class)
 */
class ValidatorMail
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $code;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="ValidatorChangementMDPs", cascade={"remove"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\JoinColumn(nullable=false)
     */
    private $compte;

    public function __construct(User $user)
    {
        $this->setCompte($user);
        $generateurCode  = new GenerateurCode();
        $this->setCode($generateurCode->createNewCode()) ;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getCompte(): ?User
    {
        return $this->compte;
    }

    public function setCompte(?User $compte): self
    {
        $this->compte = $compte;

        return $this;
    }
}
