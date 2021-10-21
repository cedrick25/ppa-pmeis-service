<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\UserAccount as UserAccountModel;
use App\Repository\UserAccountRepository;
use Psr\Cache\InvalidArgumentException;

class UserService implements UserServiceInterface
{
    public function __construct(
        private UserAccountRepository $userAccountRepository,
    ){}

    /**
     * @return array<string, mixed>
     * @throws InvalidArgumentException
     */
    public function getUserByID(int $id): array
    {
        $userAccount = $this->userAccountRepository->findAccountWithDetailsByID($id);
        unset($userAccount["password"]);
        $userAccount["user_account_id"] = (int) $userAccount["user_account_id"];
        $userAccount["user_detail_id"] = (int) $userAccount["user_detail_id"];
        $userAccount["field_office_id"] = (int) $userAccount["status"];
        $userAccount["region_id"] = (int) $userAccount["region_id"];
        $userAccount["status"] = (int) $userAccount["status"];
        $userAccount["is_pwd"] = (bool) $userAccount["is_pwd"];
        $userAccount["is_senior_citizen"] = (bool) $userAccount["is_senior_citizen"];

        return $userAccount;
    }

    public function register(UserAccountModel $userAccount): int
    {
        return $this->userAccountRepository->createUser($userAccount);
    }
}