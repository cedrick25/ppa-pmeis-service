<?php

namespace App\Service\Volunteerism;

use App\Common\AppFormatter;
use App\Enum\AuditTrailActions;
use App\Enum\Response as ResponseEnum;
use App\Model\VolunteerSupervisions as VolunteerSupervisionsModel;
use App\Repository\VolunteerSupervisionClientsRepository;
use App\Repository\VolunteerSupervisionsRepository;
use App\Service\System\AuditTrail;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class VolunteerSupervisions implements VolunteerSupervisionsInterface
{
    private string $shortName;

    public function __construct(
        private ValidatorInterface              $validator,
        private AppFormatter                    $appFormatter,
        private VolunteerSupervisionsRepository $repository,
        private AuditTrail                      $auditTrail,
        private VolunteerSupervisionClientsRepository $supervisionClientsRepository,
    ) {
        $class = new \ReflectionClass($this);
        $this->shortName = $class->getShortName();
    }

    public function create(VolunteerSupervisionsModel $data): array
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
            $this->auditTrail->log(AuditTrailActions::CREATE, $data->jsonSerialize(), $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_SUCCESS, []);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::CREATING_FAILED,
                null,
                ['cache' => $exception->getMessage()]
            );
        } catch (\Exception $e) {
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
            $socialMarketing = $this->repository->list();

            if ($socialMarketing == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $socialMarketing);
        } catch (CacheException|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function getById(int $id): array
    {
        $volunteerSupervision = $this->repository->getById($id);

        if (! $volunteerSupervision) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        $clients = $this->supervisionClientsRepository
            ->findClientsWithDetailsBySupervisionId([$id]);
        $volunteerSupervision['clients'] = $clients[$id];

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $volunteerSupervision);
    }

    public function deleteById(int $id): array
    {
        try {
            $isDeleted = $this->repository->delete($id);

            if (! $isDeleted) {
                return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['app' => ResponseEnum::NO_DATA]);
            }

            $this->auditTrail->log(AuditTrailActions::DELETE, [], $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_SUCCESS, null);
        } catch (\Exception $e) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_SUCCESS,
                null,
                ['app' => $e->getMessage()]
            );
        }
    }

    public function getReport(int $quarterId, int $fieldOfficeId): array
    {
        try {
            $data = [];
            $volunteerSupervisions = $this->repository->findByQuarter($quarterId, $fieldOfficeId);

            foreach ($volunteerSupervisions as $volunteerSupervision) {
                $fullName = $volunteerSupervision['v_firstname'] . ' ' . $volunteerSupervision['v_middlename'] . ' ' . $volunteerSupervision['v_lastname'];
                $clientFullName = $volunteerSupervision['c_firstname'] . ' ' . $volunteerSupervision['c_middlename'] . ' ' . $volunteerSupervision['c_lastname'];
                $clientData = [
                    'full_name' => $clientFullName,
                    'gender' => $volunteerSupervision['c_gender'],
                    'services_rendered' => $volunteerSupervision['service_rendered'],
                    'community_resources_tapped' => $volunteerSupervision['community_resources_tapped'],
                    'assistance_received' => $volunteerSupervision['assistance_received'],
                    'remarks' => $volunteerSupervision['remarks'],
                ];

                if (! isset($data[$fullName])) {
                    $data[$fullName] = [
                        'gender' => $volunteerSupervision['v_gender'],
                        'clients' => [$clientData],
                    ];

                    continue;
                }

                $data[$fullName]['clients'][] = $clientData;
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

    public function getPaginated(int $page, int $pageSize, int $fieldOfficeId): array
    {
        try {
            $results = $this->repository->paginated($page, $pageSize, $fieldOfficeId);

            if (empty($results)) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $volunteerSupervisionsId = array_map(
                fn($item) => (int) $item['volunteer_supervisions_id'],
                $results['items']
            );
            $clients = $this->supervisionClientsRepository
                ->findClientsWithDetailsBySupervisionId($volunteerSupervisionsId);

            foreach ($results['items'] as $i => $item) {
                $volunteerSupervisionsId = $item['volunteer_supervisions_id'];
                $results['items'][$i]['clients'] = $clients[$volunteerSupervisionsId] ?? [];
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

    public function update(int $id, VolunteerSupervisionsModel $data): array
    {
        $this->deleteById($id);

        $response = $this->create($data);

        if (ResponseEnum::CREATING_SUCCESS != $response['message']) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, []);
        }

        return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_SUCCESS, []);
    }
}