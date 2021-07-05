<?php

namespace App\Repository;

use App\Entity\ValidatorMail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ValidatorMail|null find($id, $lockMode = null, $lockVersion = null)
 * @method ValidatorMail|null findOneBy(array $criteria, array $orderBy = null)
 * @method ValidatorMail[]    findAll()
 * @method ValidatorMail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ValidatorMailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ValidatorMail::class);
    }

    // /**
    //  * @return ValidatorMail[] Returns an array of ValidatorMail objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ValidatorMail
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
