<?php

declare(strict_types=1);

namespace App\Service\TherapeuticCommunity;

use App\Common\AppFormatter;
use App\Enum\TherapeuticCommunity as TCEnum;
use App\Repository\SessionActivitiesRepository;
use Doctrine\ORM\ORMException;
use Psr\Cache\InvalidArgumentException;

class SessionActivities implements SessionActivitiesInterface
{
    public function __construct(
        private AppFormatter                $appFormatter,
        private SessionActivitiesRepository $repository,
    ){}

    public function getAll(): array
    {
        try {
            $sessionActivities = $this->repository->list();

            if (sizeof($sessionActivities) == 0) {
                return $this->appFormatter->formatResponse(TCEnum::NO_SESSION_ACTIVITIES_DATA, null);
            }

            return $this->appFormatter->formatResponse(TCEnum::FETCHING_SESSION_ACTIVITIES_SUCCESS, $sessionActivities);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::FETCHING_SESSION_ACTIVITIES_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function create(string $name): array
    {
        try {
            if ($name === "") {
                return $this->appFormatter->formatResponse(TCEnum::VALIDATING_SESSION_ACTIVITY_FAILED, null, ['app' => 'Session activity name cannot be empty.']);
            }

            $sessionActivityId = $this->repository->create($name);

            if ($sessionActivityId == null) {
                return $this->appFormatter->formatResponse(TCEnum::CREATING_SESSION_ACTIVITY_FAILED, null, ['app' => 'Session activity already exist.']);
            }

            return $this->appFormatter->formatResponse(TCEnum::CREATING_SESSION_ACTIVITY_SUCCESS, ['id' => $sessionActivityId]);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::CREATING_SESSION_ACTIVITY_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (ORMException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::DELETING_SESSION_ACTIVITY_FAILED, null, ['orm' => $exception->getMessage()]);
        }
    }


    public function deleteById(int $id): array
    {
        try {
            $isSessionActivityDeleted = $this->repository->delete($id);

            if (! $isSessionActivityDeleted) {
                return $this->appFormatter->formatResponse(TCEnum::DELETING_SESSION_ACTIVITY_FAILED, null, ['app' => TCEnum::NO_SESSION_ACTIVITIES_DATA]);
            }

            return $this->appFormatter->formatResponse(TCEnum::DELETING_SESSION_ACTIVITY_SUCCESS, null);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::DELETING_SESSION_ACTIVITY_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (ORMException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::DELETING_SESSION_ACTIVITY_FAILED, null, ['orm' => $exception->getMessage()]);
        }
    }
}