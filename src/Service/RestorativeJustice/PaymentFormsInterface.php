<?php

namespace App\Service\RestorativeJustice;

interface PaymentFormsInterface
{
    public function getAll(): array;

    public function getById(int $id): array;
}