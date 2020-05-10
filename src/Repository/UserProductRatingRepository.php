<?php

namespace App\Repository;

use App\Entity\UserProductRating;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserProductRating|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserProductRating|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserProductRating[]    findAll()
 * @method UserProductRating[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserProductRatingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserProductRating::class);
    }

    // /**
    //  * @return UserProductRating[] Returns an array of UserProductRating objects
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
    public function findOneBySomeField($value): ?UserProductRating
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
