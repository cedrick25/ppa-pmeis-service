<?php

namespace App\Service\TherapeuticCommunity;

use App\Model\Volunteer;

interface VolunteerInterface
{
    public function create(Volunteer $volunteerData): array;

    public function getAll(): array;

    public function deleteById(int $id): array;

    public function updateById(int $id, Volunteer $volunteerData):array;
}