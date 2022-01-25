<?php

namespace App\Service\RestorativeJustice;

interface OffensesInterface
{
    public function create(string $name, string $type): array;

    public function getAll(): array;

    public function getById(int $id): array;

    public function updateById(int $id, string $name, string $type): array;

    public function deleteById(int $id): array;
}