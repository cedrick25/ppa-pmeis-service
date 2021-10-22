<?php

namespace App\Service;

use \App\Model\UserAccountWithDetails;

interface UserServiceInterface
{
    public function getUserByID(int $id): array;

    public function register(UserAccountWithDetails $userAccountWithDetails): array;
}