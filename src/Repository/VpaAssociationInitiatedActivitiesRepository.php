<?php

namespace App\Repository;

use App\Entity\VpaAssociationInitiatedActivities;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VpaAssociationInitiatedActivities|null find($id, $lockMode = null, $lockVersion = null)
 * @method VpaAssociationInitiatedActivities|null findOneBy(array $criteria, array $orderBy = null)
 * @method VpaAssociationInitiatedActivities[]    findAll()
 * @method VpaAssociationInitiatedActivities[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VpaAssociationInitiatedActivitiesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VpaAssociationInitiatedActivities::class);
    }

    // /**
    //  * @return VpaAssociationInitiatedActivities[] Returns an array of VpaAssociationInitiatedActivities objects
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
    public function findOneBySomeField($value): ?VpaAssociationInitiatedActivities
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
