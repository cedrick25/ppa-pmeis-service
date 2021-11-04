<?php

namespace App\Service;

use App\Common\AppFormatter;
use App\Enum\Response as ResponseEnum;
use App\Repository\PositionRepository;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;

class Position implements PositionInterface
{
    public function __construct(
        private AppFormatter       $appFormatter,
        private PositionRepository $repository,
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
}