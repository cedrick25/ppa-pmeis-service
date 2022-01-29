<?php

namespace App\Service\RestorativeJustice;

interface PaymentModesInterface
{
    public function getAll(): array;

    public function getById(int $id): array;
}