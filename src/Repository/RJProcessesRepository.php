<?php

namespace App\Repository;

use App\Entity\RJProcesses;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RJProcesses|null find($id, $lockMode = null, $lockVersion = null)
 * @method RJProcesses|null findOneBy(array $criteria, array $orderBy = null)
 * @method RJProcesses[]    findAll()
 * @method RJProcesses[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RJProcessesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RJProcesses::class);
    }

    // /**
    //  * @return RJProcesses[] Returns an array of RJProcesses objects
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
    public function findOneBySomeField($value): ?RJProcesses
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
