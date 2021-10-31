<?php

namespace App\Service;

use \App\Model\UserAccountWithDetails;

interface UserInterface
{
    public function getAll(): array;

    public function getByID(int $id): array;

    public function register(UserAccountWithDetails $userAccountWithDetails): array;

    public function deleteById(int $id): array;

    public function updateById(int $id, UserAccountWithDetails $userAccountWithDetails): array;
}