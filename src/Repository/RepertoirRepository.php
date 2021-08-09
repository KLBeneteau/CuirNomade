<?php

namespace App\Repository;

use App\Entity\Repertoir;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Repertoir|null find($id, $lockMode = null, $lockVersion = null)
 * @method Repertoir|null findOneBy(array $criteria, array $orderBy = null)
 * @method Repertoir[]    findAll()
 * @method Repertoir[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RepertoirRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Repertoir::class);
    }
}
