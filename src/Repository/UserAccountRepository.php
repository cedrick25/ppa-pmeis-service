<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\UserAccount;
use App\Enum\Response as ResponseEnum;
use App\Model\UserAccountWithDetails;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
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
        if ($this->isExisting($userAccountWithDetails->getEmailAddress())) {
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
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws InvalidArgumentException
     */
    public function softDelete(int $id): bool
    {
        // TODO: Check table constraints
        $user =$this->isExistingById($id);

        if ($user == null) {
            return false;
        }

        $this->cache->delete($this->cacheHelper->getAccountWithDetailsKey($id));
        $this->cache->delete($this->cacheHelper->getAllUsersKey());

        $user->setDeletedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @throws InvalidArgumentException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws Exception
     */
    public function update(int $id, UserAccountWithDetails $userAccountWithDetails): string
    {
        $user = $this->isExistingById($id);

        if (! $user) {
            return ResponseEnum::NO_RECORD;
        }

        if ($this->isConflicted($user, $userAccountWithDetails)) {
            return ResponseEnum::CONFLICTED_INPUT;
        }

        $this->cache->delete($this->cacheHelper->getAccountWithDetailsKey($id));
        $this->cache->delete($this->cacheHelper->getAllUsersKey());

        $user->setEmailAddress($userAccountWithDetails->getEmailAddress());
        $user->setContactNumber($userAccountWithDetails->getContactNumber());
        $user->setUserType($userAccountWithDetails->getUserType());
        $user->setFieldOfficeId($userAccountWithDetails->getFieldOffice());
        $user->setRegionId($userAccountWithDetails->getRegion());
        $user->setStatus($userAccountWithDetails->getStatus());
        $user->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->userDetailsRepository->updateByAccountId($user->getUserAccountId(), $userAccountWithDetails);

        $this->getEntityManager()->flush();

        return ResponseEnum::OK;

    }

    public function isExistingById(int $id): bool | UserAccount
    {
        $user = $this->findOneBy([
            'userAccountId' => $id,
            'deletedAt' => null
        ]);

        return ($user == null) ? false : $user;
    }

    public function isExisting(string $emailAddress): bool
    {
        $user = $this->findOneBy([
            'emailAddress' => $emailAddress,
            'deletedAt' => null
        ]);

        return $user != null;
    }

    private function isConflicted(UserAccount $fetchedUser, UserAccountWithDetails $userData): bool
    {
        // Fetched and user input is the same.
        // It is trying to update itself.
        if ($fetchedUser->getEmailAddress() === $userData->getEmailAddress()) {
            return false;
        }

        // There is an existing record in the database.
        // It is trying to update another record that existing in the database.
        if ($this->isExisting($userData->getEmailAddress())) {
            return true;
        }

        return false;
    }
}
