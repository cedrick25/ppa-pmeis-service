<?php

namespace App\Service;

use App\Common\AppFormatter;
use App\Enum\Response as ResponseEnum;
use App\Repository\PositionRepository;
use Doctrine\ORM\ORMException;
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

    public function create(string $name): array
    {
        try {
            if ($name === "") {
                return $this->appFormatter->formatResponse(ResponseEnum::VALIDATING_FAILED, null, ['app' => 'Position name cannot be empty.']);
            }

            $id = $this->repository->create($name);

            if ($id == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['app' => 'Position exist']);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_SUCCESS, ['id' => $id]);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (ORMException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['orm' => $exception->getMessage()]);
        }
    }
}