<?php

namespace App\Service\Volunteerism;

use App\Common\AppFormatter;
use App\Enum\AuditTrailActions;
use App\Enum\Response as ResponseEnum;
use App\Model\SupportOfRegionToFieldOffice as SupportOfRegionToFieldOfficeModel;
use App\Repository\FieldOfficesRepository;
use App\Repository\QuartersRepository;
use App\Repository\SupportOfRegionToFieldOfficeRepository;
use App\Service\System\AuditTrail;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\Exception\ORMException;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SupportOfRegionToFieldOffice implements SupportOfRegionToFieldOfficeInterface
{
    private string $shortName;

    public function __construct(
        private ValidatorInterface                      $validator,
        private AppFormatter                            $appFormatter,
        private SupportOfRegionToFieldOfficeRepository   $repository,
        private QuartersRepository                      $quartersRepository,
        private AuditTrail                              $auditTrail,
        private FieldOfficesRepository                   $fieldOfficesRepository,
    ) {
        $class = new \ReflectionClass($this);
        $this->shortName = $class->getShortName();
    }

    public function create(SupportOfRegionToFieldOfficeModel $data): array
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
                    ['app' => 'Support Of Region To Field Office support already exist']
                );
            }

            $this->auditTrail->log(AuditTrailActions::CREATE, $data->jsonSerialize(), $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_SUCCESS, ['id' => $id]);
        } catch (InvalidArgumentException | \Exception $exception) {
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
            $supportOfRegionToFieldOffices = $this->repository->list();

            if ($supportOfRegionToFieldOffices == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $supportOfRegionToFieldOffices);
        } catch (CacheException|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function getPaginated(int $page, int $pageSize): array
    {
        try {
            $results = $this->repository->paginated($page, $pageSize);

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

    public function getById(int $id): array
    {
        $supportOfRegionToFieldOffice = $this->repository->isExistingById($id);

        if (!$supportOfRegionToFieldOffice) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $supportOfRegionToFieldOffice);
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

            $this->auditTrail->log(AuditTrailActions::DELETE, [], $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_SUCCESS, null);
        } catch (InvalidArgumentException | ORMException $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::DELETING_FAILED,
                null,
                ['cache' => $exception->getMessage()]
            );
        }
    }

    public function getReport(int $quarterId, int $regionId, string $category): array
    {
        try {
            $quarter = $this->quartersRepository->find($quarterId);
            if ($quarter === null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $minMaxDate = $this->quartersRepository->getQuarterMinMaxDate($quarter);
            $fieldOfficeIds = $this->fieldOfficesRepository->getFieldOfficeIdsByRegionId($regionId);

            $supportOfRegionToFieldOffices = $this->repository
                ->findByDateRange($minMaxDate, $fieldOfficeIds, $category);

            if (count($supportOfRegionToFieldOffices) <= 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $supportOfRegionToFieldOffices);
        } catch (\Exception $e) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_SUCCESS,
                null,
                ['app' => $e->getMessage()]
            );
        }
    }

    public function getAllCategoryReport(int $quarterId, int $fieldOfficeId): array
    {
        try {
            $quarter = $this->quartersRepository->find($quarterId);
            if ($quarter === null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $minMaxDate = $this->quartersRepository->getQuarterMinMaxDate($quarter);
            $results = $this->repository->findByDateRangeAndAllCategory($minMaxDate, $fieldOfficeId);

            if (empty($results)) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $results);
        } catch (\Exception $e) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_SUCCESS,
                null,
                ['app' => $e->getMessage()]
            );
        }
    }

    public function update(int $id, SupportOfRegionToFieldOfficeModel $data): array
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
}