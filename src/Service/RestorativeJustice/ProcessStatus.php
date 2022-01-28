<?php

namespace App\Service\RestorativeJustice;

use App\Common\AppFormatter;
use App\Enum\Response as ResponseEnum;
use App\Repository\RJProcessStatusRepository;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;

class ProcessStatus implements ProcessStatusInterface
{
    public function __construct(
        private AppFormatter              $appFormatter,
        private RJProcessStatusRepository $repository,
    ){}

    public function getAll(): array
    {
        try {
            $processesStatus = $this->repository->list();

            if ($processesStatus == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $processesStatus);
        } catch (CacheException|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function getById(int $id): array
    {
        $processStatus = $this->repository->isExistingById($id);

        if (!$processStatus) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $processStatus);
    }
}