<?php

namespace App\Entity;

use App\Repository\ValidatorChangementMDPRepository;
use App\Service\GenerateurCode;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ValidatorChangementMDPRepository::class)
 */
class ValidatorChangementMDP
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $code;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="ValidatorChangementMDPs")
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
