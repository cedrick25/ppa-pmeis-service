<?php

declare(strict_types=1);

namespace App\Service;

use App\Common\AppFormatter;
use App\Model\Quarters as QuartersModel;
use App\Enum\TherapeuticCommunity as TCEnum;
use App\Repository\PhasesRepository;
use App\Repository\QuartersRepository;
use Doctrine\ORM\ORMException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TherapeuticCommunityService implements TherapeuticCommunityServiceInterface
{
    public function __construct(
        private QuartersRepository $quartersRepository,
        private PhasesRepository $phasesRepository,
        private ValidatorInterface    $validator,
        private AppFormatter          $appFormatter,
    ){}

    public function createQuarters(QuartersModel $quarters): array
    {
        try {
            $errors = $this->validator->validate($quarters);

            if (count($errors) > 0) {
                return $this->appFormatter->formatResponse(TCEnum::VALIDATING_QUARTER_FAILED, null, $this->appFormatter->formatErrors($errors));
            }

            $quarterId = $this->quartersRepository->create($quarters);

            if ($quarterId == null) {
                return $this->appFormatter->formatResponse(TCEnum::CREATING_QUARTER_FAILED, null, ['app' => 'Quarter already exist.']);
            }

            return $this->appFormatter->formatResponse(TCEnum::CREATING_QUARTER_SUCCESS, ['id' => $quarterId]);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::CREATING_QUARTER_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (ORMException | \Doctrine\DBAL\Exception\InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::CREATING_QUARTER_FAILED, null, ['orm' => $exception->getMessage()]);
        }
    }

    public function getAllQuarters(): array
    {
        try {
            $quarters = $this->quartersRepository->list();

            if (sizeof($quarters) == 0) {
                return $this->appFormatter->formatResponse(TCEnum::NO_QUARTER_DATA, null);
            }

            return $this->appFormatter->formatResponse(TCEnum::FETCHING_QUARTER_SUCCESS, $quarters);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::FETCHING_QUARTER_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function deleteQuarterById(int $id): array
    {
        try {
            $isQuarterDeleted = $this->quartersRepository->delete($id);

            if (! $isQuarterDeleted) {
                return $this->appFormatter->formatResponse(TCEnum::DELETING_QUARTER_FAILED, null, ['app' => TCEnum::NO_QUARTER_DATA]);
            }

            return $this->appFormatter->formatResponse(TCEnum::DELETING_QUARTER_SUCCESS, null);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::FETCHING_QUARTER_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (ORMException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::DELETING_QUARTER_FAILED, null, ['orm' => $exception->getMessage()]);
        }
    }

    public function getAllPhases(): array
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