<?php

namespace App\Service\Volunteerism;

use App\Common\AppFormatter;
use App\Enum\AuditTrailActions;
use App\Enum\Response as ResponseEnum;
use App\Model\SocialMarketing as SocialMarketingModel;
use App\Repository\QuartersRepository;
use App\Repository\SocialMarketingParticipantRepository;
use App\Repository\SocialMarketingPersonInvolvedRepository;
use App\Repository\SocialMarketingRepository;
use App\Service\System\AuditTrail;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\Exception\ORMException;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SocialMarketing implements SocialMarketingInterface
{
    private string $shortName;

    public function __construct(
        private ValidatorInterface           $validator,
        private AppFormatter                 $appFormatter,
        private SocialMarketingRepository    $repository,
        private QuartersRepository           $quartersRepository,
        private AuditTrail                   $auditTrail,
        private SocialMarketingPersonInvolvedRepository $personInvolvedRepository,
        private SocialMarketingParticipantRepository $participantRepository,
    ) {
        $class = new \ReflectionClass($this);
        $this->shortName = $class->getShortName();
    }

    public function create(SocialMarketingModel $data): array
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
                    ['app' => 'Social Marketing already exist']
                );
            }

            $this->personInvolvedRepository->batchCreate($id, $data->getPersonsInvolved());
            $this->participantRepository->batchCreate($id, $data->getParticipants());
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

    public function update(int $id, SocialMarketingModel $data): array
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

            $isUpdated = $this->repository->update($id, $data);

            if ($isUpdated !== ResponseEnum::OK) {
                return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['app' => $isUpdated]);
            }

            $this->personInvolvedRepository->deleteBySocialMarketingId($id);
            $this->participantRepository->deleteBySocialMarketingId($id);
            $this->personInvolvedRepository->batchCreate($id, $data->getPersonsInvolved());
            $this->participantRepository->batchCreate($id, $data->getParticipants());

            $this->auditTrail->log(
                AuditTrailActions::UPDATE,
                $data->jsonSerialize(),
                $this->shortName,
                $id
            );

            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_SUCCESS, ['id' => $id]);
        } catch (InvalidArgumentException | \Exception $e) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::UPDATING_FAILED,
                null,
                ['app' => $e->getMessage()]
            );
        }
    }

    public function getAll(): array
    {
        try {
            $socialMarketing = $this->repository->list();

            if ($socialMarketing == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $socialMarketing);
        } catch (CacheException|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_FAILED,
                null,
                ['cache' => $exception->getMessage()]
            );
        }
    }

    public function getPaginated(string $type, int $page, int $pageSize, int $fieldOfficeId): array
    {
        try {
            $results = $this->repository->paginated($type, $page, $pageSize, $fieldOfficeId);

            if (empty($results)) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $socialMarketingsId = array_map(
                fn($item) => (int) $item['social_marketing_id'],
                $results['items']
            );
            $personInvolved = $this->personInvolvedRepository->findBySocialMarketingsId($socialMarketingsId);
            $participants = $this->participantRepository->findBySocialMarketingId($socialMarketingsId);

            foreach ($results['items'] as $i => $item) {
                $socialMarketingId = $item['social_marketing_id'];
                $results['items'][$i]['participants'] = $participants[$socialMarketingId] ?? [];
                $results['items'][$i]['personInvolved'] = $personInvolved[$socialMarketingId] ?? [];
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
        $socialMarketing = $this->repository->getById($id);

        if (!$socialMarketing) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        $socialMarketing['personInvolved'] = $this->personInvolvedRepository->findBySocialMarketingsId([$id])[$id] ?? [];
        $socialMarketing['participants'] = $this->participantRepository->findBySocialMarketingId([$id])[$id] ?? [];

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $socialMarketing);
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

            $this->personInvolvedRepository->deleteBySocialMarketingId($id);
            $this->participantRepository->deleteBySocialMarketingId($id);
            $this->auditTrail->log(AuditTrailActions::DELETE, [], $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_SUCCESS, null);
        } catch (\Doctrine\ORM\ORMException | InvalidArgumentException | ORMException $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::DELETING_FAILED,
                null,
                ['app' => $exception->getMessage()]
            );
        }
    }

    public function getReport(int $quarterId, int $fieldOfficeId, string $type): array
    {
        try {
            $quarter = $this->quartersRepository->find($quarterId);
            if ($quarter === null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $minMaxDate = $this->quartersRepository->getQuarterMinMaxDate($quarter);
            $socialMarketing = $this->repository->findByDateRange($minMaxDate, $fieldOfficeId, $type);

            if (empty($socialMarketing)) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $socialMarketingIds = array_map(
                fn($item) => (int) $item['social_marketing_id'],
                $socialMarketing
            );
            $personInvolved = $this->personInvolvedRepository->findBySocialMarketingsId($socialMarketingIds);
            $participants = $this->participantRepository->findBySocialMarketingId($socialMarketingIds);

            foreach ($socialMarketing as $i => $item) {
                $socialMarketingId = (int) $item['social_marketing_id'];

                $socialMarketing[$i]['participants'] = $participants[$socialMarketingId];
                $socialMarketing[$i]['personInvolved'] = $personInvolved[$socialMarketingId];
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $socialMarketing);
        } catch (\Exception $e) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_SUCCESS,
                null,
                ['app' => $e->getMessage()]
            );
        }
    }
}