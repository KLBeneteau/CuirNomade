<?php

namespace App\Repository;

use App\Entity\ValidatorChangementMDP;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ValidatorChangementMDP|null find($id, $lockMode = null, $lockVersion = null)
 * @method ValidatorChangementMDP|null findOneBy(array $criteria, array $orderBy = null)
 * @method ValidatorChangementMDP[]    findAll()
 * @method ValidatorChangementMDP[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ValidatorChangementMDPRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ValidatorChangementMDP::class);
    }

    // /**
    //  * @return ChangementMDP[] Returns an array of ChangementMDP objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ChangementMDP
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
