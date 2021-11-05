<?php

namespace App\Service;

interface PositionInterface
{
    public function getAll(): array;

    public function create(string $name): array;

    public function getById(int $id): array;

    public function getPaginated(int $page, int $pageSize): array;
}