<?php

namespace App\Service\RestorativeJustice;

interface RJOutcomesInterface
{
    public function getAll(): array;

    public function getById(int $id): array;
}