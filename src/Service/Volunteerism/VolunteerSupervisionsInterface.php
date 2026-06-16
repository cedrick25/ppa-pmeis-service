<?php

namespace App\Service\Volunteerism;

use App\Model\VolunteerSupervisions as VolunteerSupervisionsModel;

interface VolunteerSupervisionsInterface
{
    public function create(VolunteerSupervisionsModel $data): array;

    public function getAll(): array;

    public function getById(int $id): array;

    public function deleteById(int $id): array;

    public function getReport(int $quarterId, int $fieldOfficeId): array;

    public function getPaginated(int $page, int $pageSize, int $fieldOfficeId): array;

    public function update(int $id, VolunteerSupervisionsModel $data): array;
}