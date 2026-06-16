<?php

namespace App\Service\TherapeuticCommunity;

use App\Common\AppFormatter;
use App\Enum\Response as ResponseEnum;
use App\Repository\RegionsRepository;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;

class Regions implements RegionsInterface
{
    public function __construct(
        private AppFormatter      $appFormatter,
        private RegionsRepository $repository,
    ){}

    public function getAll(): array
    {
        try {
            $regions = $this->repository->list();

            if (sizeof($regions) == 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $regions);
        } catch (CacheException|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function getById(int $id): array
    {
        $region = $this->repository->isExistingById($id);

        if (!$region) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $region);
    }

    public function getPaginated(int $page, int $pageSize): array
    {
        try {
            $regions = $this->repository->paginated($page, $pageSize);

            if (sizeof($regions) == 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $regions);
        } catch (CacheException | InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }
}