<?php

namespace App\Service\RestorativeJustice;

use App\Common\AppFormatter;
use App\Enum\Response as ResponseEnum;
use App\Repository\RJProcessStatusRepository;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;

class RJProcessStatus implements RJProcessStatusInterface
{
    public function __construct(
        private AppFormatter              $appFormatter,
        private RJProcessStatusRepository $repository,
    ){}

    public function getAll(): array
    {
        try {
            $RJProcessesStatus = $this->repository->list();

            if ($RJProcessesStatus == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $RJProcessesStatus);
        } catch (CacheException|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function getById(int $id): array
    {
        $RJProcessStatus = $this->repository->isExistingById($id);

        if (!$RJProcessStatus) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $RJProcessStatus);
    }
}