<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppDateHelper;
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
        private AppDateHelper $appDateHelper,
    ) {
        parent::__construct($registry, UserAccount::class);
    }

    /**
     * @param int|null $id
     * @return array<string, mixed>|null
     * @throws InvalidArgumentException
     */
    public function findWithDetails(?int $id = null): array | null
    {
        $singleUser = "";
        $cacheKey = $this->cacheHelper->getAllUsersKey();

        if ($id != null) {
            $singleUser = "ua.user_account_id = {$id} AND";
            $cacheKey = $this->cacheHelper->getAccountWithDetailsKey($id);
        }

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($cacheKey, $id, $singleUser) {
            $dateTimeExpiration = new \DateTime();

            $conn = $this->getEntityManager()->getConnection();
            $sql = "SELECT ua.*, ud.*, rg.name as region_name, fe.name as field_office_name FROM user_account ua 
                    LEFT JOIN user_details ud ON ud.user_account_id = ua.user_account_id
                    LEFT JOIN regions rg ON rg.region_id = ua.region_id
                    LEFT JOIN field_offices fe ON fe.field_office_id = ua.field_office_id
                    WHERE {$singleUser} ua.deleted_at IS NULL";
            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();
            $result = ($id != null) ? $query->fetchAssociative() : $query->fetchAllAssociative();

            if (! $result) {
                $dateTimeExpiration->add(new \DateInterval("PT1S"));
                return null;
            }

            $dateTimeExpiration->add(new \DateInterval("PT1H"));
            $item->expiresAt($dateTimeExpiration);

            return $result;
        });
    }

    /**
     * @throws ORMException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function create(UserAccountWithDetails $userAccountWithDetails): int|null
    {
        $userByEmail = $this->getByEmail($userAccountWithDetails->getEmailAddress());
        if ($userByEmail != null) {
            return null;
        }

        $user = new UserAccount();
        $hashedPassword = $this->userPasswordHasher->hashPassword($user, $userAccountWithDetails->getPassword());
        $user->setEmailAddress($userAccountWithDetails->getEmailAddress());
        $user->setContactNumber($userAccountWithDetails->getContactNumber());
        $user->setPassword($hashedPassword);
        $user->setUserType($userAccountWithDetails->getUserType());
        $user->setFieldOfficeId($userAccountWithDetails->getFieldOffice());
        $user->setRegionId($userAccountWithDetails->getRegion());
        $user->setStatus($userAccountWithDetails->getStatus());
        $user->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        $this->userDetailsRepository->create($user->getUserAccountId(), $userAccountWithDetails);

        $this->cache->delete($this->cacheHelper->getAccountWithDetailsKey($user->getUserAccountId()));
        $this->cache->delete($this->cacheHelper->getAllUsersKey());

        return $user->getUserAccountId();
    }


    /**
     * @param string $emailAddress
     * @return UserAccount|null
     */
    public function getByEmail(string $emailAddress): ?UserAccount
    {
        return $this->findOneBy([
            'emailAddress' => $emailAddress,
            'deletedAt' => null
        ]);
    }
}
