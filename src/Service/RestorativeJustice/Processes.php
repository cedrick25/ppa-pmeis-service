<?php

declare(strict_types=1);

namespace App\Service\RestorativeJustice;

use App\Common\AppFormatter;
use App\Enum\Response as ResponseEnum;
use App\Repository\RJProcessesRepository;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;

class Processes implements ProcessesInterface
{
    public function __construct(
        private AppFormatter           $appFormatter,
        private RJProcessesRepository  $repository,
    ){}

    public function getAll(): array
    {
        try {
            $processes = $this->repository->list();

            if ($processes == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $processes);
        } catch (CacheException|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function getById(int $id): array
    {
        $process = $this->repository->isExistingById($id);

        if (!$process) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $process);
    }
}