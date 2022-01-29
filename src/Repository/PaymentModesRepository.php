<?php

namespace App\Repository;

use App\Entity\PaymentModes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PaymentModes|null find($id, $lockMode = null, $lockVersion = null)
 * @method PaymentModes|null findOneBy(array $criteria, array $orderBy = null)
 * @method PaymentModes[]    findAll()
 * @method PaymentModes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentModesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentModes::class);
    }

    // /**
    //  * @return PaymentModes[] Returns an array of PaymentModes objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PaymentModes
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
