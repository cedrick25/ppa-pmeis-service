<?php

namespace App\Service;

interface UserServiceInterface
{
    public function getUserByID(int $id): array;
}