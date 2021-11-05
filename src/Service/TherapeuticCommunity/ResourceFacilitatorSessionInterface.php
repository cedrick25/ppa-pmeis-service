<?php

namespace App\Service\TherapeuticCommunity;

use App\Model\ResourceFacilitatorSession as ResourceFacilitatorSessionModel;

interface ResourceFacilitatorSessionInterface
{
    public function create(ResourceFacilitatorSessionModel $resourceFacilitatorSessionData): array;

    public function getAll(): array;

    public function deleteById(int $id): array;

    public function updateById(int $id, ResourceFacilitatorSessionModel $resourceFacilitatorSessionData):array;

    public function getById(int $id): array;

    public function getPaginated(int $page, int $pageSize): array;
}