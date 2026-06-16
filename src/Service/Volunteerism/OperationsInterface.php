<?php

namespace App\Service\Volunteerism;

use App\Model\VolunteerOperations as VolunteerOperationsModel;

interface OperationsInterface
{
    public function create(VolunteerOperationsModel $operation): array;

    public function getAll(): array;

    public function getById(int $id): array;

    public function getVPA2(int $quarterId, int $fieldOfficeId): array;
}