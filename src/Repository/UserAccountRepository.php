<?php

namespace App\Repository;

use App\Entity\UserAccount;
use App\Entity\UserDetails;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
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

    /**
     * @param int $id
     * @return array<string, mixed>|null
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function findAccountWithDetailsByID(int $id): ?array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT ua.*, ud.*, rg.name as region_name, fe.name as field_office_name FROM user_account ua 
                    LEFT JOIN user_details ud ON ud.user_account_id = ua.user_account_id
                    LEFT JOIN regions rg ON rg.region_id = ua.region_id
                    LEFT JOIN field_offices fe ON fe.field_office_id = ua.field_office_id
                    WHERE ua.user_account_id = {$id}";
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery();

        return $result->fetchAllAssociative();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findAccountByID(int $id): ?UserAccount
    {
        // TODO: Implement data caching
        return $this->createQueryBuilder('ua')
            ->andWhere('ua.userAccountId = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
