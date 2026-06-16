<?php

namespace App\Service\Volunteerism;

use App\Model\VolunteerId as VolunteerIdModel;

interface IdInterface
{
    public function create(VolunteerIdModel $idData): array;

    public function getAll(): array;

    public function getById(string $idData): array;
}