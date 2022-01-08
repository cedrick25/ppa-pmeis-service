<?php

namespace App\Service\TherapeuticCommunity;

interface GenerateTableInterface
{
    public function generate(array $data): array;
}