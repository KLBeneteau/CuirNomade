<?php

namespace App\Entity;

use App\Repository\RepertoirRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=RepertoirRepository::class)
 * @UniqueEntity(fields={"nom"}, message="Il existe déja un produit avec se nom !")
 */
class Repertoir
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $nom;

    public function __construct(String $nom)
    {
        $this->setNom($nom) ;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    private function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }
}
