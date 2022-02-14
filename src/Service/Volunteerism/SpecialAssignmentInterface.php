<?php

namespace App\Service\Volunteerism;

use App\Model\SpecialAssignment as SpecialAssignmentModel;

interface SpecialAssignmentInterface
{
    public function create(SpecialAssignmentModel $data): array;

    public function getAll(): array;

    public function getById(int $id): array;

    public function deleteById(int $id): array;

    public function getReport(int $quarterId, int $fieldOfficeId): array;
}