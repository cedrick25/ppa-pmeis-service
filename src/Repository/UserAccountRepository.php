<?php

namespace App\Repository;

use App\Entity\UserAccount;
use App\Entity\UserDetails;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserAccount|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserAccount|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserAccount[]    findAll()
 * @method UserAccount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserAccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserAccount::class);
    }

    // /**
    //  * @return UserAccount[] Returns an array of UserAccount objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

//    /**
//     */
//    public function findAccountWithDetailsByID(int $id): ?array
//    {
//        return $this->createQueryBuilder('ua')
//            ->innerJoin(UserDetails::class, 'ud', Join::WITH, 'ud.userAccountId = ua.userAccountId')
//            ->andWhere('ua.userAccountId = :id')
//            ->setParameter('id', $id)
//            ->getQuery()
//            ->getResult();
//    }

    /**
     * @throws NonUniqueResultException
     */
    public function findAccountByID(int $id): ?UserAccount
    {
        return $this->createQueryBuilder('ua')
            ->andWhere('ua.userAccountId = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
