<?php

namespace App\Service;

use App\Model\UserAccount as UserAccountModel;

interface UserServiceInterface
{
    public function getUserByID(int $id): array;

    public function register(UserAccountModel $userAccount): array;
}