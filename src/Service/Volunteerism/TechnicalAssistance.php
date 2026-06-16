<?php

namespace App\Service\Volunteerism;

use App\Common\AppFormatter;
use App\Enum\AuditTrailActions;
use App\Enum\Response as ResponseEnum;
use App\Repository\QuartersRepository;
use App\Repository\TechnicalAssistanceParticipantsRepository;
use App\Repository\TechnicalAssistancePersonsInvolvedRepository;
use App\Repository\TechnicalAssistanceRepository;
use App\Model\TechnicalAssistance as TechnicalAssistanceModel;
use App\Service\System\AuditTrail;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\Exception\ORMException;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TechnicalAssistance implements TechnicalAssistanceInterface
{
    private string $shortName;

    public function __construct(
        private ValidatorInterface              $validator,
        private AppFormatter                    $appFormatter,
        private TechnicalAssistanceRepository   $repository,
        private QuartersRepository              $quartersRepository,
        private AuditTrail                      $auditTrail,
        private TechnicalAssistancePersonsInvolvedRepository $technicalAssistancePersonsInvolvedRepository,
        private TechnicalAssistanceParticipantsRepository $participantsRepository,
    ) {
        $class = new \ReflectionClass($this);
        $this->shortName = $class->getShortName();
    }

    public function create(TechnicalAssistanceModel $technicalAssistanceData): array
    {
        try {
            $errors = $this->validator->validate($technicalAssistanceData);

            if ($errors->count() > 0) {
                return $this->appFormatter->formatResponse(
                    ResponseEnum::VALIDATING_FAILED,
                    null,
                    $this->appFormatter->formatErrors($errors)
                );
            }

            $id = $this->repository->create($technicalAssistanceData);

            if ($id == null) {
                return $this->appFormatter->formatResponse(
                    ResponseEnum::CREATING_FAILED,
                    null,
                    ['app' => 'Technical Assistance already exist']
                );
            }

            $this->technicalAssistancePersonsInvolvedRepository
                ->batchCreate($id, $technicalAssistanceData->getPersonsInvolved());
            $this->participantsRepository->batchCreate($id, $technicalAssistanceData->getParticipants());

            $this->auditTrail->log(
                AuditTrailActions::CREATE,
                $technicalAssistanceData->jsonSerialize(),
                $this->shortName,
                $id
            );

            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_SUCCESS, ['id' => $id]);
        } catch (\Exception | InvalidArgumentException $exception) {
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
            $technicalAssistance = $this->repository->list();

            if ($technicalAssistance == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $technicalAssistance);
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

            $technicalAssistanceIds = array_map(
                fn($item) => (int) $item['id'],
                $results['items']
            );
            $personInvolved = $this->technicalAssistancePersonsInvolvedRepository
                ->findPersonsInvolvedByTechnicalAssistanceId($technicalAssistanceIds);
            $participants = $this->participantsRepository->findByTechnicalAssistanceId($technicalAssistanceIds);

            foreach ($results['items'] as $i => $item) {
                $technicalAssistanceId = $item['id'];
                $results['items'][$i]['assistance_type'] = [
                    'label' => $item['assistance_type'],
                    'value' => $item['assistance_type'],
                ];
                $results['items'][$i]['participants'] = $participants[$technicalAssistanceId];
                $results['items'][$i]['personInvolved'] = $personInvolved[$technicalAssistanceId];
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
        $technicalAssistance = $this->repository->getById($id);

        if (!$technicalAssistance) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        $technicalAssistance['assistance_type'] = [
            'label' => $technicalAssistance['assistance_type'],
            'value' => $technicalAssistance['assistance_type'],
        ];
        $technicalAssistance['participants'] = $this->participantsRepository
            ->findByTechnicalAssistanceId([$id])[$id];
        $technicalAssistance['personsInvolved'] = $this->technicalAssistancePersonsInvolvedRepository
            ->findPersonsInvolvedByTechnicalAssistanceId([$id])[$id];

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $technicalAssistance);
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

            $this->technicalAssistancePersonsInvolvedRepository->deleteByTechnicalAssistanceId($id);
            $this->participantsRepository->deleteByTechnicalAssistanceId($id);
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

            $minMaxDate = $this->quartersRepository->getQuarterMinMaxDate($quarter);
            $technicalAssistance = $this->repository->findByDateRange($minMaxDate, $fieldOfficeId);

            $technicalAssistanceIds = array_map(
                fn($item) => (int) $item['id'],
                $technicalAssistance
            );
            $personsInvolved = $this->technicalAssistancePersonsInvolvedRepository
                ->findPersonsInvolvedByTechnicalAssistanceId($technicalAssistanceIds);
            $participants = $this->participantsRepository->findByTechnicalAssistanceId($technicalAssistanceIds);

            foreach ($technicalAssistance as $i => $item) {
                $technicalAssistanceId = $item['id'];
                $technicalAssistance[$i]['participants'] = $participants[$technicalAssistanceId];
                $technicalAssistance[$i]['personsInvolved'] = $personsInvolved[$technicalAssistanceId];
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $technicalAssistance);
        } catch (\Exception $e) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_SUCCESS,
                null,
                ['app' => $e->getMessage()]
            );
        }
    }

    public function update(int $id, TechnicalAssistanceModel $technicalAssistanceData): array
    {
        try {
            $isUpdated = $this->repository->update($id, $technicalAssistanceData);

            if ($isUpdated !== ResponseEnum::OK) {
                return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['app' => $isUpdated]);
            }

            $this->auditTrail->log(
                AuditTrailActions::UPDATE,
                $technicalAssistanceData->jsonSerialize(),
                $this->shortName,
                $id
            );

            $this->technicalAssistancePersonsInvolvedRepository->deleteByTechnicalAssistanceId($id);
            $this->participantsRepository->deleteByTechnicalAssistanceId($id);
            $this->technicalAssistancePersonsInvolvedRepository
                ->batchCreate($id, $technicalAssistanceData->getPersonsInvolved());
            $this->participantsRepository->batchCreate($id, $technicalAssistanceData->getParticipants());

            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_SUCCESS, null);
        } catch (\Exception | InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::UPDATING_FAILED,
                null,
                ['app' => $exception->getMessage()]
            );
        }
    }
}