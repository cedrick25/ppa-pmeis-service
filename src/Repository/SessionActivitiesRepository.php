<?php

namespace App\Repository;

use App\Entity\SessionActivities;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SessionActivities|null find($id, $lockMode = null, $lockVersion = null)
 * @method SessionActivities|null findOneBy(array $criteria, array $orderBy = null)
 * @method SessionActivities[]    findAll()
 * @method SessionActivities[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionActivitiesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SessionActivities::class);
    }

    // /**
    //  * @return SessionActivities[] Returns an array of SessionActivities objects
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
    public function findOneBySomeField($value): ?SessionActivities
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
