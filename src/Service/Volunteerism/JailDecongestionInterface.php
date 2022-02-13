<?php

namespace App\Service\Volunteerism;

use App\Model\JailDecongestion as JailDecongestionModel;

interface JailDecongestionInterface
{
    public function create(JailDecongestionModel $data): array;

    public function getAll(): array;

    public function getById(int $id): array;

    public function deleteById(int $id): array;

    public function getReport(int $quarterId, int $fieldOfficeId): array;
}