<?php

namespace App\Service\Volunteerism;

use App\Common\AppFormatter;
use App\Enum\AuditTrailActions;
use App\Enum\Response as ResponseEnum;
use App\Model\VpaAssociationInitiatedActivities as VpaAssociationInitiatedActivitiesModel;
use App\Repository\VpaAssociationActivityVolunteersRepository;
use App\Repository\VpaAssociationInitiatedActivitiesRepository;
use App\Service\System\AuditTrail;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\Exception\ORMException;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class VpaAssociationInitiatedActivities implements VpaAssociationInitiatedActivitiesInterface
{
    private string $shortName;

    public function __construct(
        private ValidatorInterface                          $validator,
        private AppFormatter                                $appFormatter,
        private VpaAssociationInitiatedActivitiesRepository $repository,
        private AuditTrail                                  $auditTrail,
        private VpaAssociationActivityVolunteersRepository  $vpaAssociationActivityVolunteersRepository,
    ) {
        $class = new \ReflectionClass($this);
        $this->shortName = $class->getShortName();
    }

    public function create(VpaAssociationInitiatedActivitiesModel $data): array
    {
        try {
            $errors = $this->validator->validate($data);

            if (count($errors) > 0) {
                return $this->appFormatter->formatResponse(
                    ResponseEnum::VALIDATING_FAILED,
                    null,
                    $this->appFormatter->formatErrors($errors)
                );
            }

            $id = $this->repository->create($data);

            if ($id == null) {
                return $this->appFormatter->formatResponse(
                    ResponseEnum::CREATING_FAILED,
                    null,
                    ['app' => 'Vpa association initiated activities already exist']
                );
            }

            $this->vpaAssociationActivityVolunteersRepository->batchCreate($id, $data->getVolunteers());

            $this->auditTrail->log(AuditTrailActions::CREATE, $data->jsonSerialize(), $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_SUCCESS, ['id' => $id]);
        } catch (\Exception|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::CREATING_FAILED,
                null,
                ['app' => $exception->getMessage()]
            );
        }
    }

    public function getAll(): array
    {
        try {
            $vpaAssociationInitiatedActivities = $this->repository->list();

            if ($vpaAssociationInitiatedActivities == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_SUCCESS,
                $vpaAssociationInitiatedActivities
            );
        } catch (CacheException|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_FAILED,
                null,
                ['cache' => $exception->getMessage()]
            );
        }
    }

    public function getById(int $id): array
    {
        $vpaAssociationInitiatedActivity = $this->repository->getById($id);

        if (!$vpaAssociationInitiatedActivity) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        $vpaAssociationInitiatedActivity['volunteers'] = $this->vpaAssociationActivityVolunteersRepository->fetchByVpaAssociationId($id);

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $vpaAssociationInitiatedActivity);
    }

    public function deleteById(int $id): array
    {
        try {
            $isDeleted = $this->repository->delete($id);

            if (! $isDeleted) {
                return $this->appFormatter->formatResponse(
                    ResponseEnum::DELETING_FAILED,
                    null,
                    ['app' => ResponseEnum::NO_DATA]
                );
            }

            $this->vpaAssociationActivityVolunteersRepository->deleteByVpaAssociationId($id);

            $this->auditTrail->log(AuditTrailActions::DELETE, [], $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_SUCCESS, null);
        } catch (InvalidArgumentException | ORMException | \Doctrine\ORM\ORMException $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::DELETING_FAILED,
                null,
                ['app' => $exception->getMessage()]
            );
        }
    }

    public function getReport(int $quarterId, int $fieldOfficeId): array
    {
      try {
          $activities = [];
          $vpaAssociationInitiatedActivities = $this->repository->getReport($quarterId, $fieldOfficeId);

          if ($vpaAssociationInitiatedActivities == null) {
              return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
          }

          foreach($vpaAssociationInitiatedActivities as $activity) {
            $activity["volunteers"] = $this->vpaAssociationActivityVolunteersRepository->fetchForReportByVpaAssociationId($activity["vpa_association_initiated_activity_id"]);
            $activities[] = $activity;
          }

          return $this->appFormatter->formatResponse(
              ResponseEnum::FETCHING_SUCCESS,
              $activities
          );
      } catch (\Doctrine\DBAL\Exception | Exception  $e) {
          return $this->appFormatter->formatResponse(
              ResponseEnum::FETCHING_FAILED,
              null,
              ['cache' => $e->getMessage()]
          );
      }
    }

    public function getPaginated(int $page, int $pageSize, int $fieldOfficeId): array
    {
        try {
            $results = $this->repository->paginated($page, $pageSize, $fieldOfficeId);

            if (empty($results)) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $results);
        } catch (CacheException|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_FAILED,
                null,
                ['cache' => $exception->getMessage()]
            );
        }
    }

    public function update(int $id, VpaAssociationInitiatedActivitiesModel $data): array
    {
        try {
            $errors = $this->validator->validate($data);

            if (count($errors) > 0) {
                return $this->appFormatter->formatResponse(
                    ResponseEnum::VALIDATING_FAILED,
                    null,
                    $this->appFormatter->formatErrors($errors)
                );
            }

            $response = $this->repository->update($id, $data);

            if (ResponseEnum::OK != $response) {
                return $this->appFormatter->formatResponse(
                    ResponseEnum::UPDATING_FAILED,
                    null
                );
            }       
  
            $this->vpaAssociationActivityVolunteersRepository->deleteByVpaAssociationId($id);
            $this->vpaAssociationActivityVolunteersRepository->batchCreate($id, $data->getVolunteers());

            $this->auditTrail->log(AuditTrailActions::UPDATE, $data->jsonSerialize(), $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_SUCCESS, ['id' => $id]);
        } catch (\Exception|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::CREATING_FAILED,
                null,
                ['app' => $exception->getMessage()]
            );
        }
    }
}
