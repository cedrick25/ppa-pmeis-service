<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\CacheHelper;
use App\Entity\UserAccount;
use App\Model\UserAccountWithDetails;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;;

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
        private UserDetailsRepository $userDetailsRepository,
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
     * @throws Exception
     */
    public function createUser(UserAccountWithDetails $userAccountWithDetails): int|null
    {
        $userByEmail = $this->getByEmail($userAccountWithDetails->getEmailAddress());
        if ($userByEmail != null) {
            return null;
        }

        $currentDateTime = new DateTimeImmutable();
        $currentDateTime->format("Y-m-d H:m:s");

        $user = new UserAccount();
        $hashedPassword = $this->userPasswordHasher->hashPassword($user, $userAccountWithDetails->getPassword());
        $user->setEmailAddress($userAccountWithDetails->getEmailAddress());
        $user->setContactNumber($userAccountWithDetails->getContactNumber());
        $user->setPassword($hashedPassword);
        $user->setUserType($userAccountWithDetails->getUserType());
        $user->setFieldOfficeId($userAccountWithDetails->getFieldOffice());
        $user->setRegionId($userAccountWithDetails->getRegion());
        $user->setStatus($userAccountWithDetails->getStatus());
        $user->setCreatedAt($currentDateTime);

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        $this->userDetailsRepository->create($user->getUserAccountId(), $userAccountWithDetails);

        return $user->getUserAccountId();
    }


    /**
     * @param string $emailAddress
     * @return UserAccount|null
     * @throws NonUniqueResultException
     */
    public function getByEmail(string $emailAddress): ?UserAccount
    {
        return $this->createQueryBuilder('ua')
            ->andWhere('ua.emailAddress = :emailAddress')
            ->setParameter('emailAddress', $emailAddress)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
