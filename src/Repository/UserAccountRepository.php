<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\CacheHelper;
use App\Entity\UserAccount;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;;
use App\Model\UserAccount as UserAccountModel;

/**
 * @method UserAccount|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserAccount|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserAccount[]    findAll()
 * @method UserAccount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserAccountRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private CacheInterface $cache,
        private CacheHelper $cacheHelper,
        private UserPasswordHasherInterface $userPasswordHasher,
    ) {
        parent::__construct($registry, UserAccount::class);
    }

    /**
     * @param int $id
     * @return array<string, mixed>|null
     * @throws InvalidArgumentException
     */
    public function findAccountWithDetailsByID(int $id): ?array
    {
        $cacheKey = $this->cacheHelper->getAccountWithDetailsKey($id);
        $expiration = $this->cacheHelper->getExpirationDateTime(1);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($cacheKey, $id, $expiration) {
            $item->expiresAt($expiration);

            $conn = $this->getEntityManager()->getConnection();
            $sql = "SELECT ua.*, ud.*, rg.name as region_name, fe.name as field_office_name FROM user_account ua 
                    LEFT JOIN user_details ud ON ud.user_account_id = ua.user_account_id
                    LEFT JOIN regions rg ON rg.region_id = ua.region_id
                    LEFT JOIN field_offices fe ON fe.field_office_id = ua.field_office_id
                    WHERE ua.user_account_id = {$id}";
            $stmt = $conn->prepare($sql);
            $result = $stmt->executeQuery();

            return $result->fetchAssociative();
        });
    }

    /**
     * @param int $id
     * @return UserAccount|null
     * @throws InvalidArgumentException
     */
    public function findAccountByID(int $id): ?UserAccount
    {
        $cacheKey = $this->cacheHelper->getAccountKey($id);
        $expiration = $this->cacheHelper->getExpirationDateTime(1);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($cacheKey, $id, $expiration) {
            $item->expiresAt($expiration);

            return $this->createQueryBuilder('ua')
                ->andWhere('ua.userAccountId = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getOneOrNullResult();
        });
    }

    /**
     * @throws ORMException
     */
    public function createUser(UserAccountModel $userAccount): int
    {
        $currentDateTime = new DateTimeImmutable();
        $currentDateTime->format("Y-m-d H:m:s");

        $user = new UserAccount();
        $hashedPassword = $this->userPasswordHasher->hashPassword($user, $userAccount->getPassword());
        $user->setEmailAddress($userAccount->getEmailAddress());
        $user->setContactNumber($userAccount->getContactNumber());
        $user->setPassword($hashedPassword);
        $user->setUserType($userAccount->getUserType());
        $user->setFieldOfficeId($userAccount->getFieldOffice());
        $user->setRegionId($userAccount->getRegion());
        $user->setStatus($userAccount->getStatus());
        $user->setCreatedAt($currentDateTime);

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return $user->getUserAccountId();
    }
}
