<?php

declare(strict_types=1);

namespace App\Service;

use App\Common\AppFormatter;
use App\Model\Quarters as QuartersModel;
use App\Enum\TherapeuticCommunity as TCEnum;
use App\Repository\FieldOfficesRepository;
use App\Repository\PhasesRepository;
use App\Repository\QuartersRepository;
use App\Repository\SessionActivitiesRepository;
use Doctrine\ORM\ORMException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TherapeuticCommunityService implements TherapeuticCommunityServiceInterface
{
    public function __construct(
        private ValidatorInterface    $validator,
        private AppFormatter          $appFormatter,
        private QuartersRepository $quartersRepository,
        private PhasesRepository $phasesRepository,
        private FieldOfficesRepository $fieldOfficesRepository,
        private SessionActivitiesRepository $sessionActivitiesRepository,
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

    public function getAllFieldOffices(): array
    {
        try {
            $fieldOffices = $this->fieldOfficesRepository->list();

            if (sizeof($fieldOffices) == 0) {
                return $this->appFormatter->formatResponse(TCEnum::NO_FIELD_OFFICE_DATA, null);
            }

            return $this->appFormatter->formatResponse(TCEnum::FETCHING_FIELD_OFFICE_SUCCESS, $fieldOffices);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::FETCHING_FIELD_OFFICE_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function getAllSessionActivities(): array
    {
        try {
            $sessionActivities = $this->sessionActivitiesRepository->list();

            if (sizeof($sessionActivities) == 0) {
                return $this->appFormatter->formatResponse(TCEnum::NO_SESSION_ACTIVITIES_DATA, null);
            }

            return $this->appFormatter->formatResponse(TCEnum::FETCHING_SESSION_ACTIVITIES_SUCCESS, $sessionActivities);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::FETCHING_SESSION_ACTIVITIES_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function createSessionActivity(string $name): array
    {
        try {
            if ($name === "") {
                return $this->appFormatter->formatResponse(TCEnum::VALIDATING_SESSION_ACTIVITIES_FAILED, null, ['app' => 'Session activity name cannot be empty.']);
            }

            $sessionActivityId = $this->sessionActivitiesRepository->create($name);

            if ($sessionActivityId == null) {
                return $this->appFormatter->formatResponse(TCEnum::CREATING_SESSION_ACTIVITIES_FAILED, null, ['app' => 'Session activity already exist.']);
            }

            return $this->appFormatter->formatResponse(TCEnum::CREATING_SESSION_ACTIVITIES_SUCCESS, ['id' => $sessionActivityId]);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::CREATING_SESSION_ACTIVITIES_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (ORMException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::CREATING_SESSION_ACTIVITIES_FAILED, null, ['orm' => $exception->getMessage()]);
        }
    }
}