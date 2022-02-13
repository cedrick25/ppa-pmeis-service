<?php

namespace App\Repository;

use App\Entity\SocialMarketingActivities;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SocialMarketingActivities|null find($id, $lockMode = null, $lockVersion = null)
 * @method SocialMarketingActivities|null findOneBy(array $criteria, array $orderBy = null)
 * @method SocialMarketingActivities[]    findAll()
 * @method SocialMarketingActivities[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SocialMarketingActivitiesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SocialMarketingActivities::class);
    }

    // /**
    //  * @return SocialMarketingActivities[] Returns an array of SocialMarketingActivities objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SocialMarketingActivities
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
