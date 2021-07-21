<?php

namespace App\Service;

class CreationProduit{

    public function getEntityPattern(String $nom, int $isVIP) {

        $texte = '<?php' ;
        $texte.= "\r\n namespace App\Entity; \r\n";
        $texte.= ' use App\Repository\\'.$nom.'Repository;
                    use Doctrine\ORM\Mapping as ORM;
                    use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
                    
                    /**
                     * @ORM\Entity(repositoryClass='.$nom.'Repository::class)
                     * @UniqueEntity(fields={"nom"}, message="Il existe déja un produit avec se nom !")
                     */
                    class '.$nom.'
                    {
                        /**
                         * @ORM\Id
                         * @ORM\GeneratedValue
                         * @ORM\Column(type="integer")
                         */
                        private $id;
                    
                        /**
                         * @ORM\Column(type="string", length=50, unique=true)
                         */
                        private $nom;
                    
                        /**
                         * @ORM\Column(type="boolean", options = {"default" = '.$isVIP.'}))
                         */
                        private $isVIP;
                    
                        /**
                         * @ORM\Column(type="integer")
                         */
                        private $prix;
                    
                        /**
                         * @ORM\Column(type="string", length=300, nullable=true)
                         */
                        private $description;
                    
                        /**
                         * @ORM\Column(type="integer")
                         */
                        private $nbStock;
                    
                        public function getId(): ?int
                        {
                            return $this->id;
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
                    
                        public function getIsVIP(): ?bool
                        {
                            return $this->isVIP;
                        }
                    
                        public function setIsVIP(bool $isVIP): self
                        {
                            $this->isVIP = $isVIP;
                    
                            return $this;
                        }
                    
                        public function getPrix(): ?int
                        {
                            return $this->prix;
                        }
                    
                        public function setPrix(int $prix): self
                        {
                            $this->prix = $prix;
                    
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
                    
                        public function getNbStock(): ?int
                        {
                            return $this->nbStock;
                        }
                    
                        public function setNbStock(int $nbStock): self
                        {
                            $this->nbStock = $nbStock;
                    
                            return $this;
                        }
                    } ' ;
        return $texte ;
    }

    public function getRepositoryPattern(String $nom) {

        $texte = '<?php' ;
        $texte.= "\r\n namespace App\Repository; \r\n";
        $texte.= '  use App\Entity\\'.$nom.';
                    use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
                    use Doctrine\Persistence\ManagerRegistry;
                    
                    /**
                     * @method '.$nom.'|null find($id, $lockMode = null, $lockVersion = null)
                     * @method '.$nom.'|null findOneBy(array $criteria, array $orderBy = null)
                     * @method '.$nom.'[]    findAll()
                     * @method '.$nom.'[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
                     */
                    class '.$nom.'Repository extends ServiceEntityRepository
                    {
                        public function __construct(ManagerRegistry $registry)
                        {
                            parent::__construct($registry, '.$nom.'::class);
                        }
                    } ' ;
        return $texte;

    }
}
