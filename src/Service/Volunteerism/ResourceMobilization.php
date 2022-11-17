<?php

namespace App\Service\Volunteerism;

use App\Common\AppFormatter;
use App\Enum\AuditTrailActions;
use App\Enum\Response as ResponseEnum;
use App\Model\ResourceMobilization as ResourceMobilizationModel;
use App\Repository\QuartersRepository;
use App\Repository\ResMobCashRepository;
use App\Repository\ResMobMaterialsRepository;
use App\Repository\ResMobSecuredByRepository;
use App\Repository\ResMobTechnicalAssistanceRepository;
use App\Repository\ResourceMobilizationRepository;
use App\Service\System\AuditTrail;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\Exception\ORMException;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ResourceMobilization implements ResourceMobilizationInterface
{
    private string $shortName;

    public function __construct(
        private ValidatorInterface              $validator,
        private AppFormatter                    $appFormatter,
        private ResourceMobilizationRepository  $repository,
        private QuartersRepository              $quartersRepository,
        private AuditTrail                      $auditTrail,
        private ResMobSecuredByRepository       $securedByRepository,
        private ResMobCashRepository            $cashRepository,
        private ResMobMaterialsRepository       $materialsRepository,
        private ResMobTechnicalAssistanceRepository $technicalAssistanceRepository,

    ) {
        $class = new \ReflectionClass($this);
        $this->shortName = $class->getShortName();
    }

    public function create(ResourceMobilizationModel $data): array
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
                    ['app' => 'Resource Mobilization already exist']
                );
            }

            $this->securedByRepository->batchCreate($id, $data->getSecuredBy());
            $this->cashRepository->batchCreate($id, $data->getCash());
            $this->materialsRepository->batchCreate($id, $data->getMaterials());
            $this->technicalAssistanceRepository->batchCreate($id, $data->getTechnicalAssistance());

            $this->auditTrail->log(AuditTrailActions::CREATE, $data->jsonSerialize(), $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_SUCCESS, ['id' => $id]);
        } catch (InvalidArgumentException | \Exception $e) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::CREATING_FAILED,
                null,
                ['app' => $e->getMessage()]
            );
        }
    }

    public function update(int $id, ResourceMobilizationModel $data): array
    {
        try {
            $response = $this->repository->update($id, $data);

            if (ResponseEnum::OK != $response) {
                return $this->appFormatter->formatResponse(
                    ResponseEnum::UPDATING_FAILED,
                    null
                );
            }

            $this->cashRepository->deleteByResMobId($id);
            $this->materialsRepository->deleteByResMobId($id);
            $this->technicalAssistanceRepository->deleteByResMobId($id);
            $this->securedByRepository->deleteByResMobId($id);

            $this->securedByRepository->batchCreate($id, $data->getSecuredBy());
            $this->cashRepository->batchCreate($id, $data->getCash());
            $this->materialsRepository->batchCreate($id, $data->getMaterials());
            $this->technicalAssistanceRepository->batchCreate($id, $data->getTechnicalAssistance());

            $this->auditTrail->log(AuditTrailActions::UPDATE, $data->jsonSerialize(), $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_SUCCESS, ['id' => $id]);
        } catch (InvalidArgumentException | \Exception $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::UPDATING_FAILED,
                null,
                ['app' => $exception->getMessage()]
            );
        }
    }

    public function getAll(): array
    {
        try {
            $resourceMobilizations = $this->repository->list();

            if ($resourceMobilizations == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $resourceMobilizations);
        } catch (CacheException|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_FAILED,
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

            $resMobsId = array_map(
                fn($item) => (int) $item['resource_mobilization_id'],
                $results['items']
            );


            $cash = $this->cashRepository->findByResMobsId($resMobsId);
            $materials = $this->materialsRepository->findByResMobsId($resMobsId);
            $technicalAssistance = $this->technicalAssistanceRepository->findByResMobsId($resMobsId);
            $securedBy = $this->securedByRepository->findByResMobId($resMobsId);

            foreach ($results['items'] as $i => $item) {
                $resMobId = $item['resource_mobilization_id'];
                $results['items'][$i]['cash'] = $cash[$resMobId];
                $results['items'][$i]['materials'] = $materials[$resMobId];
                $results['items'][$i]['technicalAssistance'] = $technicalAssistance[$resMobId];
                $results['items'][$i]['securedBy'] = $securedBy[$resMobId];
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

    public function getById(int $id): array
    {
        $result = $this->repository->getById($id);

        if (! $result) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        $result['cash'] = $this->cashRepository->findByResMobsId([$id])[$id];
        $result['materials'] = $this->materialsRepository->findByResMobsId([$id])[$id];
        $result['technicalAssistance'] = $this->technicalAssistanceRepository->findByResMobsId([$id])[$id];
        $result['securedBy'] = $this->securedByRepository->findByResMobId([$id])[$id];

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $result);
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

            $this->cashRepository->deleteByResMobId($id);
            $this->materialsRepository->deleteByResMobId($id);
            $this->technicalAssistanceRepository->deleteByResMobId($id);
            $this->securedByRepository->deleteByResMobId($id);

            $this->auditTrail->log(AuditTrailActions::DELETE, [], $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_SUCCESS, null);
        } catch (InvalidArgumentException | \Doctrine\ORM\ORMException | ORMException $exception) {
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
            $quarter = $this->quartersRepository->find($quarterId);

            if ($quarter === null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $result = ['TC' => [], 'RJ' => [], 'VPA' => [], 'SC' => [], 'GAD' => [], 'OTHERS' => [],];

            $minMaxDate = $this->quartersRepository->getQuarterMinMaxDate($quarter);
            $resourceMobilizations = $this->repository->findByDateRange($minMaxDate, $fieldOfficeId);

            $resMobsId = array_map(
                fn($item) => (int) $item['resource_mobilization_id'],
                $resourceMobilizations
            );


            $cash = $this->cashRepository->findByResMobsId($resMobsId);
            $materials = $this->materialsRepository->findByResMobsId($resMobsId);
            $technicalAssistance = $this->technicalAssistanceRepository->findByResMobsId($resMobsId);
            $securedBy = $this->securedByRepository->findByResMobId($resMobsId);

            foreach ($resourceMobilizations as $resourceMobilization) {
                $resMobId = $resourceMobilization['resource_mobilization_id'];

                $resourceMobilization['cash'] = $cash[$resMobId];
                $resourceMobilization['materials'] = $materials[$resMobId];
                $resourceMobilization['technicalAssistance'] = $technicalAssistance[$resMobId];
                $resourceMobilization['securedBy'] = $securedBy[$resMobId];

                $result[$resourceMobilization['category']][] = $resourceMobilization;
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $result);
        } catch (\Exception $e) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_SUCCESS,
                null,
                ['app' => $e->getMessage()]
            );
        }
    }
}