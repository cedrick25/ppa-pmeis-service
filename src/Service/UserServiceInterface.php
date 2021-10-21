<?php

namespace App\Service;

interface UserServiceInterface
{
    public function getUserByID(int $id): array;

    public function register(string $emailAddress, string $password): string;
}