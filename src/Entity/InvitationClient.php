<?php

namespace App\Entity;

use App\Repository\InvitationClientRepository;
use App\Service\GenerateurCode;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=InvitationClientRepository::class)
 * @UniqueEntity(fields={"email"}, message="Il existe déja une invitation pour cette email !")
 */
class InvitationClient
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
     * @ORM\Column(type="string", length=30)
     */
    private $code;

    public function __construct(String $email)
    {
        $this->setEmail($email);
        $generateurCode  = new GenerateurCode();
        $this->setCode($generateurCode->createNewCode()) ;
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }
}
