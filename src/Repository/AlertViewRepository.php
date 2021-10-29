<?php

namespace App\Repository;

use App\Entity\AlertView;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AlertView|null find($id, $lockMode = null, $lockVersion = null)
 * @method AlertView|null findOneBy(array $criteria, array $orderBy = null)
 * @method AlertView[]    findAll()
 * @method AlertView[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AlertViewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AlertView::class);
    }

    // /**
    //  * @return AlertView[] Returns an array of AlertView objects
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
    public function findOneBySomeField($value): ?AlertView
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
