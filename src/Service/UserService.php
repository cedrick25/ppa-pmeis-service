<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\UserAccountRepository;
use Doctrine\DBAL\Driver\Exception;

class UserService implements UserServiceInterface
{
    public function __construct(
        private UserAccountRepository $userAccountRepository
    ){}

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     * @return array<string, mixed>
     */
    public function getUserByID(int $id): array
    {
        return $this->userAccountRepository->findAccountWithDetailsByID($id);
    }
}