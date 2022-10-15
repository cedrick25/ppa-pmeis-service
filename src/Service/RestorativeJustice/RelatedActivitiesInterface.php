<?php

namespace App\Service\RestorativeJustice;

use App\Model\RJRelatedActivities as RJRelatedActivitiesModel;

interface RelatedActivitiesInterface
{
    public function create(RJRelatedActivitiesModel $activities): array;

    public function getAll(): array;

    public function getById(int $id): array;

    public function deleteById(int $id): array;

    public function getRJIB2Data(int $quarterId, int $fieldOfficeId): array;

    public function update(int $id, RJRelatedActivitiesModel $activities): array;
}