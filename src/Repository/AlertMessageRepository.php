<?php

namespace App\Repository;

use App\Entity\AlertMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AlertMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method AlertMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method AlertMessage[]    findAll()
 * @method AlertMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AlertMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AlertMessage::class);
    }

    // /**
    //  * @return AlertMessage[] Returns an array of AlertMessage objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AlertMessage
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
