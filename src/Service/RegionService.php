<?php

namespace App\Service;

use App\Repository\RegionsRepository;

class RegionService
{
    public function __construct(
        private RegionsRepository $repository,
    ) {
    }

    /**
     * @param int[] $ids
     * @return array<string, string>
     */
    public function getRegionNamesByIds(array $ids): array
    {
        return $this->repository->getRegionNamesByIds($ids);
    }
}