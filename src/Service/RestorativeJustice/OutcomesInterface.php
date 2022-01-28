<?php

namespace App\Service\RestorativeJustice;

interface OutcomesInterface
{
    public function getAll(): array;

    public function getById(int $id): array;
}