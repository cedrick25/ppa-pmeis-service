<?php

declare(strict_types=1);

namespace App\Service\Volunteerism;

use App\Common\AppDateHelper;
use App\Common\AppFormatter;
use App\Entity\Volunteer;
use App\Enum\Response as ResponseEnum;
use App\Model\VolunteerOperations as VolunteerOperationsModel;
use App\Repository\QuartersRepository;
use App\Repository\ResourceFacilitatorSessionRepository;
use App\Repository\VolunteerOperationsRepository;
use App\Repository\VolunteerRepository;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\Exception\ORMException;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Operations implements OperationsInterface
{
    const APPOINTED = 'APPOINTED';
    const REAPPOINTED = 'REAPPOINTED';
    const DROPPED = 'DROPPED';
    const INACTIVE = 'INACTIVE';

    public function __construct(
        private ValidatorInterface                   $validator,
        private AppFormatter                         $appFormatter,
        private VolunteerOperationsRepository        $repository,
        private QuartersRepository                   $quartersRepository,
        private AppDateHelper                        $appDateHelper,
        private VolunteerRepository                  $volunteerRepository,
        private ResourceFacilitatorSessionRepository $resourceFacilitatorSessionRepository,
    ){}

    public function create(VolunteerOperationsModel $operation): array
    {
        try {
            $errors = $this->validator->validate($operation);

            if (count($errors) > 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::VALIDATING_FAILED, null, $this->appFormatter->formatErrors($errors));
            }

            $id = $this->repository->create($operation);

            if ($id == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['app' => 'Volunteer operations already exist']);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_SUCCESS, ['id' => $id]);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (ORMException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['orm' => $exception->getMessage()]);
        } catch (\Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['app' => $e->getMessage()]);
        }
    }

    public function getAll(): array
    {
        try {
            $operations = $this->repository->list();

            if ($operations == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $operations);
        } catch (CacheException|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function getById(int $id): array
    {
        $operation = $this->repository->isExistingById($id);

        if (!$operation) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $operation);
    }

    public function getVPA2(int $fieldOfficeId, int $quarterId): array
    {
        try {
            $quarter = $this->quartersRepository->find($quarterId);
            if ($quarter === null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $months = $this->appDateHelper->getMonthsByQuarterString($quarter->getName());
            $activeVolunteers = $this->resourceFacilitatorSessionRepository->getVolunteerIdsByQuarterAndFieldOfficeId($fieldOfficeId, $quarterId);
            $params = [
                $fieldOfficeId,
                intval($quarter->getYear()),
                $months,
                $activeVolunteers
            ];

            $appointed = $this->getVolunteersByStatus(self::APPOINTED, ...$params);
            $reAppointed = $this->getVolunteersByStatus(self::REAPPOINTED, ...$params);
            $dropped = $this->getVolunteersByStatus(self::DROPPED, ...$params);
            $inactive = $this->volunteerRepository->findInactiveVolunteersByFieldOfficeAndMonthRange(...$params);

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, [
                self::APPOINTED => $appointed,
                self::REAPPOINTED => $reAppointed,
                self::DROPPED => $dropped,
                self::INACTIVE => $inactive
            ]);
        } catch (\Exception | Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, null, ['app' => $e->getMessage()]);
        }
    }

    /**
     * @param int $fieldOfficeId
     * @param int $year
     * @param int[] $months
     * @param string $status
     * @param array<int, array<string, mixed>> $activeVolunteers
     * @return array<int, array<string, mixed>>
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function getVolunteersByStatus(
        string $status,
        int $fieldOfficeId,
        int $year,
        array $months,
        array $activeVolunteers
    ): array
    {
        $volunteerList = [];
        $activeVolunteersIds = [];
        $volunteers = $this->repository->findVolunteerIdsByMonthRange($year, $months, $status);

        foreach ($activeVolunteers as $activeVolunteer) {
            $activeVolunteersIds[] = $activeVolunteer['resource_facilitator_id'];
        }

        foreach ($volunteers as $volunteer) {
            if (! in_array(intval($volunteer['volunteer_id']), $activeVolunteersIds)) {
                continue;
            }
            $volunteerList[$volunteer['volunteer_id']] = [
                'reason' => $volunteer['reason'],
                'date' => $volunteer['date'],
                'date_endorsed' => $volunteer['date_endorsed'],
            ];
        }

        $newVolunteers = [];
        foreach ($volunteerList as $volunteerId=> $volunteerData) {
            $volunteer = $this->volunteerRepository->find($volunteerId);

            if ($volunteer->getFieldOfficeId() === $fieldOfficeId) {
                $volunteerData['volunteer'] = $volunteer;
                $newVolunteers[] = $volunteerData;
            }
        }

        return $newVolunteers;
    }
}