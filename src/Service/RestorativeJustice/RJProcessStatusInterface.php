<?php

namespace App\Service\RestorativeJustice;

interface RJProcessStatusInterface
{
    public function getAll(): array;

    public function getById(int $id): array;
}