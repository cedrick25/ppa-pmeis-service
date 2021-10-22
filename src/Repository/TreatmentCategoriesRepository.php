<?php

namespace App\Repository;

use App\Entity\TreatmentCategories;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TreatmentCategories|null find($id, $lockMode = null, $lockVersion = null)
 * @method TreatmentCategories|null findOneBy(array $criteria, array $orderBy = null)
 * @method TreatmentCategories[]    findAll()
 * @method TreatmentCategories[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TreatmentCategoriesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TreatmentCategories::class);
    }

    // /**
    //  * @return TreatmentCategories[] Returns an array of TreatmentCategories objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TreatmentCategories
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
