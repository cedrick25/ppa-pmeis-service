<?php

namespace App\Repository;

use App\Entity\Venues;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Venues|null find($id, $lockMode = null, $lockVersion = null)
 * @method Venues|null findOneBy(array $criteria, array $orderBy = null)
 * @method Venues[]    findAll()
 * @method Venues[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VenuesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Venues::class);
    }

    // /**
    //  * @return Venues[] Returns an array of Venues objects
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
    public function findOneBySomeField($value): ?Venues
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
