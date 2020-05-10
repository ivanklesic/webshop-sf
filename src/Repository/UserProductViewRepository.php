<?php

namespace App\Repository;

use App\Entity\UserProductView;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserProductView|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserProductView|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserProductView[]    findAll()
 * @method UserProductView[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserProductViewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserProductView::class);
    }

    // /**
    //  * @return UserProductView[] Returns an array of UserProductView objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserProductView
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
