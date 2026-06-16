<?php

namespace App\Service\Volunteerism;

use App\Common\AppFormatter;
use App\Enum\AuditTrailActions;
use App\Enum\Response as ResponseEnum;
use App\Model\SpecialAssignment as SpecialAssignmentModel;
use App\Repository\QuartersRepository;
use App\Repository\SpecialAssignmentPersonnelInvolvedRepository;
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
        private SpecialAssignmentPersonnelInvolvedRepository $personnelInvolvedRepository,
    ) {
        $class = new \ReflectionClass($this);
        $this->shortName = $class->getShortName();
    }

    public function create(SpecialAssignmentModel $data): array
    {
        try {
            $errors = $this->validator->validate($data);

            if ($errors->count() > 0) {
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
                    ['app' => 'Special Assignment already exist']
                );
            }

            $this->personnelInvolvedRepository->batchCreate($id, $data->getPersonnelInvolved());
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

    public function update(int $id, SpecialAssignmentModel $data): array
    {
        try {
            $isUpdated = $this->repository->update($id, $data);

            if ($isUpdated !== ResponseEnum::OK) {
                return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['app' => $isUpdated]);
            }

            $this->auditTrail->log(
                AuditTrailActions::UPDATE,
                $data->jsonSerialize(),
                $this->shortName,
                $id
            );

            $this->personnelInvolvedRepository->deleteBySpecialAssignmentId($id);
            $this->personnelInvolvedRepository->batchCreate($id, $data->getPersonnelInvolved());

            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_SUCCESS, null);
        } catch (\Exception | InvalidArgumentException $exception) {
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
            $specialAssignments = $this->repository->list();

            if ($specialAssignments == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $specialAssignments);
        } catch (CacheException|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_FAILED,
                null,
                ['cache' => $exception->getMessage()]
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

            $saIds = array_map(
                fn($item) => (int) $item['special_assignment_id'],
                $results['items']
            );
            $personsResponsible = $this->personnelInvolvedRepository->findBySpecialAssignmentId($saIds);

            foreach ($results['items'] as $i => $item) {
                $saId = $item['special_assignment_id'];
                $results['items'][$i]['personnelInvolved'] = $personsResponsible[$saId];
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $results);
        } catch (CacheException|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_FAILED,
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

        $result['personnelInvolved'] = $this->personnelInvolvedRepository->findBySpecialAssignmentId([$id])[$id];

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

            $this->personnelInvolvedRepository->deleteBySpecialAssignmentId($id);
            $this->auditTrail->log(AuditTrailActions::DELETE, [], $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_SUCCESS, null);
        } catch (\Doctrine\ORM\ORMException | ORMException | InvalidArgumentException $exception) {
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

            $result = [
                'SPECIAL_ASSIGNMENT' => ['national' => [], 'regional' => [], 'field_office' => []],
                'MISCELLANEOUS_ACTIVITIES' => []
            ];
            $minMaxDate = $this->quartersRepository->getQuarterMinMaxDate($quarter);

            $specialAssignments = $this->repository->findByDateRange($minMaxDate, $fieldOfficeId);

            $saIds = array_map(
                fn($item) => (int) $item['special_assignment_id'],
                $specialAssignments
            );
            $personsResponsible = $this->personnelInvolvedRepository->findBySpecialAssignmentId($saIds);

            foreach ($specialAssignments as $specialAssignment) {
                $saId = $specialAssignment['special_assignment_id'];
                $specialAssignment['personnelInvolved'] = $personsResponsible[$saId];

                if ('SPECIAL_ASSIGNMENT' === $specialAssignment['category_type']) {
                    $result['SPECIAL_ASSIGNMENT'][$specialAssignment['sub_type']][] = $specialAssignment;
                    continue;
                }

                $result['MISCELLANEOUS_ACTIVITIES'][] = $specialAssignment;
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