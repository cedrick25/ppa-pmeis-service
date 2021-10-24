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
                return $this->appFormatter->formatResponse(TCEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(TCEnum::FETCHING_SUCCESS, $sessionActivities);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function create(string $name): array
    {
        try {
            if ($name === "") {
                return $this->appFormatter->formatResponse(TCEnum::VALIDATING_FAILED, null, ['app' => 'Session activity name cannot be empty.']);
            }

            $sessionActivityId = $this->repository->create($name);

            if ($sessionActivityId == null) {
                return $this->appFormatter->formatResponse(TCEnum::CREATING_FAILED, null, ['app' => 'Session activity already exist.']);
            }

            return $this->appFormatter->formatResponse(TCEnum::CREATING_FAILED, ['id' => $sessionActivityId]);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::CREATING_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (ORMException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::CREATING_FAILED, null, ['orm' => $exception->getMessage()]);
        }
    }

    public function deleteById(int $id): array
    {
        try {
            $isDeleted = $this->repository->delete($id);

            if (! $isDeleted) {
                return $this->appFormatter->formatResponse(TCEnum::DELETING_FAILED, null, ['app' => TCEnum::NO_DATA]);
            }

            return $this->appFormatter->formatResponse(TCEnum::DELETING_SUCCESS, null);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::DELETING_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (ORMException $exception) {
            return $this->appFormatter->formatResponse(TCEnum::DELETING_FAILED, null, ['orm' => $exception->getMessage()]);
        }
    }
}