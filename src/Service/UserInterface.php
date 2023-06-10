<?php

namespace App\Service;

use \App\Model\UserAccountWithDetails;

interface UserInterface
{
    public function getAll(): array;

    public function getByID(int $id): array;

    public function register(UserAccountWithDetails $userAccountWithDetails): array;

    /**
     * @return mixed[]
     */
    public function login(string $email, string $password, bool $encrypted): array;

    public function verifyOtp(string $email, string $otp): array;

    public function deleteById(int $id): array;

    public function updateById(int $id, UserAccountWithDetails $userAccountWithDetails): array;

    public function getPaginated(int $page, int $pageSize): array;
}