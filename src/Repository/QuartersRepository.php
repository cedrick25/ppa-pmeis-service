<?php

namespace App\Repository;

use App\Entity\Quarters;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Quarters|null find($id, $lockMode = null, $lockVersion = null)
 * @method Quarters|null findOneBy(array $criteria, array $orderBy = null)
 * @method Quarters[]    findAll()
 * @method Quarters[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuartersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quarters::class);
    }

    // /**
    //  * @return Quarters[] Returns an array of Quarters objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('q.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Quarters
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
