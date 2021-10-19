<?php

namespace App\Repository;

use App\Entity\FieldOffices;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FieldOffices|null find($id, $lockMode = null, $lockVersion = null)
 * @method FieldOffices|null findOneBy(array $criteria, array $orderBy = null)
 * @method FieldOffices[]    findAll()
 * @method FieldOffices[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FieldOfficesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FieldOffices::class);
    }

    // /**
    //  * @return FieldOffices[] Returns an array of FieldOffices objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FieldOffices
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
