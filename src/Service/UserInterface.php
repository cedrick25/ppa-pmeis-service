<?php

namespace App\Service;

use \App\Model\UserAccountWithDetails;

interface UserInterface
{
    public function getByID(int $id): array;

    public function register(UserAccountWithDetails $userAccountWithDetails): array;
}