<?php

namespace App\Service\TherapeuticCommunity;

use App\Model\Volunteer;

interface VolunteerInterface
{
    public function create(Volunteer $volunteerData): array;

    public function getAll(): array;

    public function deleteById(int $id): array;

    public function updateById(int $id, Volunteer $volunteerData):array;

    public function getById(int $id): array;

    public function getPaginated(int $page, int $pageSize): array;

    public function getByFieldOfficeAndMonthRange(int $fieldOfficeId, int $quarterId): array;

    public function getApplicants(): array;

    public function updateVolunteerStatus(array $data): array;

    public function getConsolidatedSocioDemographic(int $quarterId): array;
}