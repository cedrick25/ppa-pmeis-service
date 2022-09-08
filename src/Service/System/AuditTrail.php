<?php

namespace App\Service\System;

use App\Entity\UserAccount;
use App\Repository\AuditTrailRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuditTrail
{
    public function __construct(
        private AuditTrailRepository $repository,
        private TokenStorageInterface $tokenStorage,
        private AuditTrailActionDetailsTransformer $actionDetailsTransformer,
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

        $this->repository->create($action, $this->getUserId(), $actionDetails);
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

    private function getUserId(): int
    {
        $user = $this->tokenStorage->getToken()->getUser();
        \assert($user instanceof UserAccount);

        return $user->getUserAccountId();
    }
}