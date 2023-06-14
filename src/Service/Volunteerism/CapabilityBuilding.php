<?php

namespace App\Service\Volunteerism;

use App\Common\AppFormatter;
use App\Enum\AuditTrailActions;
use App\Enum\Response as ResponseEnum;
use App\Repository\CapabilityBuildingParticipantsRepository;
use App\Repository\CapabilityBuildingRepository;
use App\Repository\QuartersRepository;
use App\Service\System\AuditTrail;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\Exception\ORMException;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;

class CapabilityBuilding implements CapabilityBuildingInterface
{
    private string $shortName;

    public function __construct(
        private AppFormatter                 $appFormatter,
        private CapabilityBuildingRepository $repository,
        private QuartersRepository           $quartersRepository,
        private AuditTrail                   $auditTrail,
        private CapabilityBuildingParticipantsRepository $capabilityBuildingParticipantsRepository,
    ) {
        $class = new \ReflectionClass($this);
        $this->shortName = $class->getShortName();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): array
    {
        try {
            $id = $this->repository->create($data);

            if (null == $id) {
                return $this->appFormatter->formatResponse(
                    ResponseEnum::CREATING_FAILED,
                    null,
                    ['app' => 'Capability Building already exist']
                );
            }

            $this->capabilityBuildingParticipantsRepository->batchCreate($id, $data['participants']);
            $this->auditTrail->log(AuditTrailActions::CREATE, $data, $this->shortName);

            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_SUCCESS, []);
        } catch (InvalidArgumentException | \Exception $e) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::CREATING_FAILED,
                null,
                ['app' => $e->getMessage()]
            );
        }
    }

    public function getAll(): array
    {
        try {
            $capabilityBuildings = $this->repository->list();

            if ($capabilityBuildings == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $capabilityBuildings);
        } catch (CacheException|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_FAILED,
                null,
                ['cache' => $exception->getMessage()]
            );
        }
    }

    public function getReport(int $quarterId, int $fieldOfficeId, string $type): array
    {
        try {
            $data = [];
            $quarter = $this->quartersRepository->find($quarterId);

            if ($quarter === null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $minMaxDate = $this->quartersRepository->getQuarterMinMaxDate($quarter);
            $capabilityBuildings = $this->repository->findReport($minMaxDate, $fieldOfficeId, $type);
            $capabilityBuildingsId = array_map(
                fn($item) => (int) $item['capability_building_id'],
                $capabilityBuildings
            );
            $participants = $this->capabilityBuildingParticipantsRepository
                ->findParticipantsByCapabilityBuildingsId($capabilityBuildingsId, $type);

            foreach ($capabilityBuildings as $capabilityBuilding) {
                if (! isset($data[$capabilityBuilding['subtype']])) {
                    $data[$capabilityBuilding['subtype']] = [];
                }

                $capabilityBuilding['participants'] = $participants[$capabilityBuilding['capability_building_id']];
                $data[$capabilityBuilding['subtype']][] = $capabilityBuilding;
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_SUCCESS,
                null,
                ['app' => $e->getMessage()]
            );
        }
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

            $this->capabilityBuildingParticipantsRepository->deleteByCapabilityBuildingId($id);
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

    public function getPaginated(int $page, int $pageSize): array
    {
        try {
            $results = $this->repository->paginated($page, $pageSize);

            if (empty($results)) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $capabilityBuildingsId = array_map(
                fn($item) => (int) $item['capability_building_id'],
                $results['items']
            );
            $participants = $this->capabilityBuildingParticipantsRepository
                ->findParticipantsByCapabilityBuildingsId($capabilityBuildingsId);

            foreach ($results['items'] as $i => $item) {
                $capabilityBuildingId = $item['capability_building_id'];
                $results['items'][$i]['participants'] = $participants[$capabilityBuildingId] ?? [];
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

    public function update(int $id, array $data): array
    {
        try {
            $response = $this->repository->update($id, $data);

            if (ResponseEnum::OK != $response) {
                return $this->appFormatter->formatResponse(
                    ResponseEnum::UPDATING_FAILED,
                    null
                );
            }

            $this->capabilityBuildingParticipantsRepository->deleteByCapabilityBuildingId($id);
            $this->capabilityBuildingParticipantsRepository->batchCreate($id, $data['participants']);

            $this->auditTrail->log(AuditTrailActions::UPDATE, $data, $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_SUCCESS, ['id' => $id]);
        } catch (InvalidArgumentException | \Exception $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::UPDATING_FAILED,
                null,
                ['app' => $exception->getMessage()]
            );
        }
    }

    public function getById(int $id): array
    {
        $result = $this->repository->getById($id);

        if (! $result) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        $result['participants'] = $this->transformParticipants($this->capabilityBuildingParticipantsRepository
            ->findParticipantsByCapabilityBuildingsId([$id])[$id]);

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $result);
    }

    private function transformParticipants(array $participants): array
    {
        $transformed = [];

        foreach ($participants as $participant) {
            $transformed[] = [
                'id' => [
                    'label' => $participant['personnel_name'],
                    'value' => $participant['personnel_id'],
                ],
                'remarks' => $participant['remarks'],
            ];
        }

        return $transformed;
    }
}