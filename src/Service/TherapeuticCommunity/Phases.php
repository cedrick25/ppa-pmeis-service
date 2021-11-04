<?php

declare(strict_types=1);

namespace App\Service\TherapeuticCommunity;

use App\Common\AppFormatter;
use App\Enum\Response as ResponseEnum;
use App\Repository\PhasesRepository;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;

class Phases implements PhasesInterface
{
    public function __construct(
        private AppFormatter     $appFormatter,
        private PhasesRepository $repository,
    ){}

    public function getAll(): array
    {
        try {
            $phases = $this->repository->list();

            if (sizeof($phases) == 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $phases);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function getPaginated(int $page, int $pageSize): array
    {
        try {
            $phases = $this->repository->paginated($page, $pageSize);

            if (sizeof($phases) == 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $phases);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function getById(int $id): array
    {
        $phase = $this->repository->isExistingById($id);

        if (!$phase) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $phase);
    }
}