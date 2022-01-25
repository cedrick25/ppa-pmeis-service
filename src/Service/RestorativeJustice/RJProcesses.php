<?php

declare(strict_types=1);

namespace App\Service\RestorativeJustice;

use App\Common\AppFormatter;
use App\Enum\Response as ResponseEnum;
use App\Repository\RJProcessesRepository;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;

class RJProcesses implements RJProcessesInterface
{
    public function __construct(
        private AppFormatter           $appFormatter,
        private RJProcessesRepository  $repository,
    ){}

    public function getAll(): array
    {
        try {
            $RJProcesses = $this->repository->list();

            if ($RJProcesses == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $RJProcesses);
        } catch (CacheException|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function getById(int $id): array
    {
        $RJProcess = $this->repository->isExistingById($id);

        if (!$RJProcess) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $RJProcess);
    }
}