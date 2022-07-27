<?php

namespace App\Service\Volunteerism;

use App\Model\SupportOfRegionToFieldOffice as SupportOfRegionToFieldOfficeModel;

interface SupportOfRegionToFieldOfficeInterface
{
    public function create(SupportOfRegionToFieldOfficeModel $data): array;

    public function getAll(): array;

    public function getById(int $id): array;

    public function deleteById(int $id): array;

    public function getReport(int $quarterId, int $fieldOfficeId, string $category): array;

    public function getAllCategoryReport(int $quarterId, int $fieldOfficeId): array;
}