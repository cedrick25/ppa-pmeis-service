<?php

declare(strict_types=1);

namespace App\Service\TherapeuticCommunity;

use App\Common\AppFormatter;
use App\Enum\TherapeuticCommunity as TCEnum;
use App\Repository\PhasesRepository;
use Psr\Cache\InvalidArgumentException;

class Phases implements PhasesInterface
{
    public function __construct(
        private AppFormatter     $appFormatter,
        private PhasesRepository $phasesRepository,
    ){}

    public function getAll(): array
    {
        try {
            $phases = $this->phasesRepository->list();

            if (sizeof($phases) == 0) {
                return $this->appFormatter->formatResponse(TCEnum::NO_PHASES_DATA, null);
            }

            return $this->appFormatter->formatResponse(TCEnum::FETCHING_PHASES_SUCCESS, $phases);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::FETCHING_PHASES_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }
}