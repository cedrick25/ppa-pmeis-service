<?php

namespace App\Service\Volunteerism;

use App\Model\SocialMarketing as SocialMarketingModel;

interface SocialMarketingInterface
{
    public function create(SocialMarketingModel $data): array;

    public function update(int $id, SocialMarketingModel $data): array;

    public function getAll(): array;

    public function getPaginated(string $type, int $page, int $pageSize, int $fieldOfficeId): array;

    public function getById(int $id): array;

    public function deleteById(int $id): array;

    public function getReport(int $quarterId, int $fieldOfficeId, string $type): array;
}