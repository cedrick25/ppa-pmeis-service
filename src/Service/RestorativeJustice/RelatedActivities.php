<?php

declare(strict_types=1);

namespace App\Service\RestorativeJustice;

use App\Common\AppFormatter;
use App\Common\AppHydrator;
use App\Enum\AuditTrailActions;
use App\Enum\Response as ResponseEnum;
use App\Repository\FieldOfficesRepository;
use App\Repository\QuartersRepository;
use App\Repository\RjRelatedActivitiesPersonsInvolvedRepository;
use App\Repository\RJRelatedActivitiesRepository;
use App\Model\RJRelatedActivities as RelatedActivitiesModel;
use App\Service\System\AuditTrail;
use Doctrine\ORM\Exception\ORMException;
use Exception;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RelatedActivities implements RelatedActivitiesInterface
{
    private string $shortName;

    public function __construct(
        private ValidatorInterface                           $validator,
        private AppFormatter                                 $appFormatter,
        private RJRelatedActivitiesRepository                $repository,
        private AuditTrail                                   $auditTrail,
        private RjRelatedActivitiesPersonsInvolvedRepository $relatedActivitiesPersonsInvolvedRepository,
        private AppHydrator                                  $hydrator,
        private FieldOfficesRepository                        $fieldOfficesRepository,
        private QuartersRepository                           $quartersRepository,
    )
    {
        $class = new \ReflectionClass($this);
        $this->shortName = $class->getShortName();
    }

    public function create(RelatedActivitiesModel $activities): array
    {
        try {
            $errors = $this->validator->validate($activities);

            if (count($errors) > 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::VALIDATING_FAILED, null, $this->appFormatter->formatErrors($errors));
            }

            $id = $this->repository->create($activities);

            if ($id == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['app' => 'RJ related activities already exist']);
            }

            $this->auditTrail->log(
                AuditTrailActions::CREATE,
                $activities->jsonSerialize(),
                $this->shortName,
                $id
            );

            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_SUCCESS, ['id' => $id]);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (ORMException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['orm' => $exception->getMessage()]);
        } catch (Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['app' => $e->getMessage()]);
        }
    }

    public function getAll(): array
    {
        try {
            $relatedActivities = $this->repository->list();

            if ($relatedActivities == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $return = [];
            $conductProcessesId = array_map(fn($relatedActivity) => $relatedActivity['rj_related_activity_id'], $relatedActivities);
            $personsInvolved = $this->relatedActivitiesPersonsInvolvedRepository->findByConductedProcessIds($conductProcessesId);

            foreach ($relatedActivities as $relatedActivity) {
                $relatedActivityId = $relatedActivity['rj_related_activity_id'];
                $relatedActivity['personsInvolved'] = $personsInvolved[$relatedActivityId] ?? [];

                $return[] = $relatedActivity;
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $return);
        } catch (CacheException|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function getById(int $id): array
    {
        $relatedActivity = $this->repository->isExistingById($id);

        if (!$relatedActivity) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        $quarter = $this->quartersRepository->find($relatedActivity->getQuarterId());
        $region = $this->fieldOfficesRepository->getRegionByFieldOfficeId($relatedActivity->getFieldOfficeId());

        $arrayVersion = $this->hydrator->convertObjectToArray($relatedActivity);
        $arrayVersion['venueDate'] = $relatedActivity->getVenueDate()->format('Y-m-d');
        $arrayVersion['createdAt'] = $relatedActivity->getCreatedAt()->format('Y-m-d');
        $arrayVersion['regionName'] = $region['region_name'];
        $arrayVersion['regionId'] = $region['region_id'];
        $arrayVersion['year'] = $quarter->getYear();
        $arrayVersion['personsInvolved'] = $this->relatedActivitiesPersonsInvolvedRepository->findByRelatedActivityId($id);

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $arrayVersion);
    }

    public function deleteById(int $id): array
    {
        try {
            $isDeleted = $this->repository->delete($id);

            if (!$isDeleted) {
                return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['app' => ResponseEnum::NO_DATA]);
            }

            $this->relatedActivitiesPersonsInvolvedRepository->deleteByRelatedActivityId($id);
            $this->auditTrail->log(AuditTrailActions::DELETE, [], $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_SUCCESS, null);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (\Doctrine\ORM\ORMException|ORMException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['orm' => $exception->getMessage()]);
        }
    }

    public function getRJIB2Data(int $quarterId, int $fieldOfficeId): array
    {
        try {
            $relatedActivities = $this->repository->getRJIB2Data($quarterId, $fieldOfficeId);

            if ($relatedActivities == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $return = [];
            $conductProcessesId = array_map(fn($relatedActivity) => $relatedActivity['rj_related_activity_id'], $relatedActivities);
            $personsInvolved = $this->relatedActivitiesPersonsInvolvedRepository->findByConductedProcessIds($conductProcessesId);

            foreach ($relatedActivities as $relatedActivity) {
                $relatedActivityId = $relatedActivity['rj_related_activity_id'];
                $relatedActivity['persons_involved'] = $personsInvolved[$relatedActivityId] ?? [];

                $return[] = $relatedActivity;
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $return);
        } catch (InvalidArgumentException|CacheException  $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['cache' => $e->getMessage()]);
        }
    }

    public function update(int $id, RelatedActivitiesModel $activities): array
    {

        try {
            $isUpdated = $this->repository->update($id, $activities);

            if ($isUpdated !== ResponseEnum::OK) {
                return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['app' => $isUpdated]);
            }

            $this->auditTrail->log(AuditTrailActions::UPDATE, $activities->jsonSerialize(), $this->shortName, $id);

            $this->relatedActivitiesPersonsInvolvedRepository->deleteByRelatedActivityId($id);
            $this->relatedActivitiesPersonsInvolvedRepository->batchCreate($id, $activities->getPersonsInvolved());

            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_SUCCESS, null);
        } catch (Exception $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['error' => $exception->getMessage()]);
        }
    }
}
