<?php

namespace App\Service\RestorativeJustice;

use App\Common\AppFormatter;
use App\Enum\Response as ResponseEnum;
use App\Repository\RJOutcomesRepository;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;

class Outcomes implements OutcomesInterface
{
    public function __construct(
        private AppFormatter         $appFormatter,
        private RJOutcomesRepository $repository,
    ){}

    public function getAll(): array
    {
        try {
            $RJOutcomes = $this->repository->list();

            if ($RJOutcomes == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $RJOutcomes);
        } catch (CacheException|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function getById(int $id): array
    {
        $RJOutcome = $this->repository->isExistingById($id);

        if (!$RJOutcome) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $RJOutcome);
    }
}