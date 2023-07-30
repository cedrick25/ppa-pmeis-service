<?php

namespace App\Repository;

use App\Entity\RjConductedProcessClients;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RjConductedProcessClients>
 *
 * @method RjConductedProcessClients|null find($id, $lockMode = null, $lockVersion = null)
 * @method RjConductedProcessClients|null findOneBy(array $criteria, array $orderBy = null)
 * @method RjConductedProcessClients[]    findAll()
 * @method RjConductedProcessClients[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RjConductedProcessClientsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RjConductedProcessClients::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(RjConductedProcessClients $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(RjConductedProcessClients $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return RjConductedProcessClients[] Returns an array of RjConductedProcessClients objects
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
    public function findOneBySomeField($value): ?RjConductedProcessClients
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
