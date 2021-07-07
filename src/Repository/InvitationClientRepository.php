<?php

namespace App\Repository;

use App\Entity\InvitationClient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method InvitationClient|null find($id, $lockMode = null, $lockVersion = null)
 * @method InvitationClient|null findOneBy(array $criteria, array $orderBy = null)
 * @method InvitationClient[]    findAll()
 * @method InvitationClient[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvitationClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvitationClient::class);
    }

    // /**
    //  * @return InvitationClient[] Returns an array of InvitationClient objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?InvitationClient
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
