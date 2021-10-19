<?php

namespace App\Service;

use App\Model\UserAccount;

interface UserServiceInterface
{
    public function getUserAccount(): UserAccount;
    public function getUserByID(int $id): array;
}