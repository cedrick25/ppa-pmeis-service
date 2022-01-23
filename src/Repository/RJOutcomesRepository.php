<?php

namespace App\Repository;

use App\Entity\RJOutcomes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RJOutcomes|null find($id, $lockMode = null, $lockVersion = null)
 * @method RJOutcomes|null findOneBy(array $criteria, array $orderBy = null)
 * @method RJOutcomes[]    findAll()
 * @method RJOutcomes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RJOutcomesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RJOutcomes::class);
    }

    // /**
    //  * @return RJOutcomes[] Returns an array of RJOutcomes objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RJOutcomes
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
