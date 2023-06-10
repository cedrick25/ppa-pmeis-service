<?php

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Entity\UserOtp;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserOtp>
 *
 * @method UserOtp|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserOtp|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserOtp[]    findAll()
 * @method UserOtp[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserOtpRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private AppDateHelper $appDateHelper,
    ) {
        parent::__construct($registry, UserOtp::class);
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(
        int $userAccountId,
        string $otp,
    ): void {
        $entity = new UserOtp();
        $entity->setUserAccountId($userAccountId);
        $entity->setOtp($otp);
        $entity->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(UserOtp $entity, bool $flush = true): void
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
    public function remove(UserOtp $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
    
    public function batchDelete(int $userAccountId): void
    {
        $this->_em->getConnection()->executeQuery(
            "DELETE FROM user_otp WHERE user_account_id = :user_account_id",
            ['user_account_id' => $userAccountId],
        );
    }
}
