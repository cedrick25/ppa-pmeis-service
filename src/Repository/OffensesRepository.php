<?php

namespace App\Repository;

use App\Entity\Offenses;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Offenses|null find($id, $lockMode = null, $lockVersion = null)
 * @method Offenses|null findOneBy(array $criteria, array $orderBy = null)
 * @method Offenses[]    findAll()
 * @method Offenses[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OffensesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Offenses::class);
    }

    // /**
    //  * @return Offenses[] Returns an array of Offenses objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Offenses
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
