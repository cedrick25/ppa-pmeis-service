<?php

namespace App\Service\Volunteerism;

use App\Common\AppFormatter;
use App\Common\AppHydrator;
use App\Enum\AuditTrailActions;
use App\Enum\Response as ResponseEnum;
use App\Model\SpecialAssignment as SpecialAssignmentModel;
use App\Repository\QuartersRepository;
use App\Repository\ResourceMobilizationRepository;
use App\Repository\SpecialAssignmentRepository;
use App\Service\System\AuditTrail;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\Exception\ORMException;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SpecialAssignment implements SpecialAssignmentInterface
{
    private string $shortName;

    public function __construct(
        private ValidatorInterface              $validator,
        private AppFormatter                    $appFormatter,
        private SpecialAssignmentRepository     $repository,
        private QuartersRepository              $quartersRepository,
        private AuditTrail                      $auditTrail,
        private AppHydrator                     $hydrator,
    ) {
        $class = new \ReflectionClass($this);
        $this->shortName = $class->getShortName();
    }

    public function create(SpecialAssignmentModel $data): array
    {
        try {
            $errors = $this->validator->validate($data);

            if (count($errors) > 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::VALIDATING_FAILED, null, $this->appFormatter->formatErrors($errors));
            }

            $id = $this->repository->create($data);

            if ($id == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['app' => 'Special Assignment already exist']);
            }

            $this->auditTrail->log(
                AuditTrailActions::CREATE,
                $this->hydrator->convertObjectToArray($data),
                $this->shortName,
                $id
            );

            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_SUCCESS, ['id' => $id]);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (\Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['app' => $e->getMessage()]);
        }
    }

    public function getAll(): array
    {
        try {
            $specialAssignments = $this->repository->list();

            if ($specialAssignments == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $specialAssignments);
        } catch (CacheException|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function getById(int $id): array
    {
        $specialAssignment = $this->repository->isExistingById($id);

        if (!$specialAssignment) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $specialAssignment);
    }

    public function deleteById(int $id): array
    {
        try {
            $isDeleted = $this->repository->delete($id);

            if (! $isDeleted) {
                return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['app' => ResponseEnum::NO_DATA]);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_SUCCESS, null);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (\Doctrine\ORM\ORMException | ORMException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['orm' => $exception->getMessage()]);
        }
    }

    public function getReport(int $quarterId, int $fieldOfficeId): array
    {
        try {
            $quarter = $this->quartersRepository->find($quarterId);
            if ($quarter === null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $result = [
                'SPECIAL_ASSIGNMENT' => ['national' => [], 'regional' => [], 'field_office' => []],
                'MISCELLANEOUS_ACTIVITIES' => []
            ];
            $minMaxDate = $this->quartersRepository->getQuarterMinMaxDate($quarter);
            $specialAssignments = $this->repository->findByDateRange($minMaxDate, $fieldOfficeId);
            foreach ($specialAssignments as $specialAssignment) {
                if ('SPECIAL_ASSIGNMENT' === $specialAssignment['category_type']) {
                    $result['SPECIAL_ASSIGNMENT'][$specialAssignment['sub_type']][] = $specialAssignment;
                    continue;
                }

                $result['MISCELLANEOUS_ACTIVITIES'][] = $specialAssignment;
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $result);
        } catch (\Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, null, ['app' => $e->getMessage()]);
        } catch (Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, null, ['orm' => $e->getMessage()]);
        }
    }
}