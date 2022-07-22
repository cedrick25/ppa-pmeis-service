<?php

namespace App\Service\Volunteerism;

use App\Model\VpaAssociationInitiatedActivities as VpaAssociationInitiatedActivitiesModel;

interface VpaAssociationInitiatedActivitiesInterface
{
    public function create(VpaAssociationInitiatedActivitiesModel $data): array;

    public function getAll(): array;

    public function getById(int $id): array;

    public function deleteById(int $id): array;

    public function getReport(int $quarterId, int $fieldOfficeId): array;
}