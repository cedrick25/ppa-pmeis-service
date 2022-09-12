<?php

namespace App\Service\System;

use App\Entity\UserAccount;
use App\Repository\AuditTrailRepository;
use App\Repository\UserAccountRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuditTrail
{
    public function __construct(
        private AuditTrailRepository $repository,
        private TokenStorageInterface $tokenStorage,
        private AuditTrailActionDetailsTransformer $actionDetailsTransformer,
        private UserAccountRepository $userAccountRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $actionDetails
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function log(
        string $action,
        array $actionDetails
    ): void {
        $actionDetails = $this->actionDetailsTransformer->transform($action, $actionDetails);
        $userDetails = $this->getUserDetails();

        $this->repository->create($action, $userDetails, $actionDetails);
    }

    /**
     * @param int[] $ids
     * @throws \Doctrine\DBAL\Exception
     */
    public function deleteLogs(array $ids): void
    {
        $this->repository->batchDelete($ids);
    }

    /**
     * @return array<string, mixed>
     * @throws \Psr\Cache\CacheException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function paginated(int $page = 1, int $pageSize = 10): array
    {
        return $this->repository->paginated($page, $pageSize);
    }

    /**
     * @return array<string, mixed>
     * @throws \Psr\Cache\CacheException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function getUserDetails(): array
    {
        $response = [];
        $user = $this->tokenStorage->getToken()->getUser();
        \assert($user instanceof UserAccount);

        $details = $this->userAccountRepository->findWithDetails($user->getUserAccountId());
        $response['userId'] = (int) $details['user_account_id'];
        $response['email'] = $details['email_address'];
        $response['firstName'] = $details['first_name'];
        $response['middleName'] = $details['middle_name'];
        $response['lastName'] = $details['last_name'];

        return $response;
    }
}