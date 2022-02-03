<?php

declare(strict_types=1);

namespace App\Service\Volunteerism;

use App\Common\AppDateHelper;
use App\Common\AppFormatter;
use App\Entity\Volunteer;
use App\Enum\Response as ResponseEnum;
use App\Model\VolunteerOperations as VolunteerOperationsModel;
use App\Repository\QuartersRepository;
use App\Repository\VolunteerOperationsRepository;
use App\Repository\VolunteerRepository;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\Exception\ORMException;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Operations implements OperationsInterface
{
    public function __construct(
        private ValidatorInterface                   $validator,
        private AppFormatter                         $appFormatter,
        private VolunteerOperationsRepository        $repository,
        private QuartersRepository                   $quartersRepository,
        private AppDateHelper                        $appDateHelper,
        private VolunteerRepository                  $volunteerRepository,
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

    public function getByFieldOfficeAndMonthRange(int $fieldOfficeId, int $quarterId): array
    {
        try {
            $quarter = $this->quartersRepository->find($quarterId);
            $months = $this->appDateHelper->getMonthsByQuarterString($quarter->getName());

            $inactive = $this->volunteerRepository
                ->findInactiveVolunteersByFieldOfficeAndMonthRange($fieldOfficeId, $quarterId, intval($quarter->getYear()), $months);

            $appointed = $this->getAppointedVolunteers($fieldOfficeId, intval($quarter->getYear()), $months);
            $reAppointed = $this->getReAppointedVolunteers($fieldOfficeId, intval($quarter->getYear()), $months);
            $dropped = $this->getDroppedVolunteers($fieldOfficeId, intval($quarter->getYear()), $months, $inactive);

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, [
                'APPOINTED' => $appointed,
                'REAPPOINTED' => $reAppointed,
                'INACTIVE' => $inactive,
                'DROPPED' => $dropped
            ]);
        } catch (\Exception | Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, null, ['app' => $e->getMessage()]);
        }
    }

    /**
     * @param int $fieldOfficeId
     * @param int $year
     * @param int[] $months
     * @return Volunteer[]
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function getAppointedVolunteers(int $fieldOfficeId, int $year, array $months): array
    {
        $volunteerList = [];
        $volunteers = $this->repository->findVolunteerIdsByMonthRange(
            $year,
            $months,
            'APPOINTED'
        );

        foreach ($volunteers as $volunteer) {
            $volunteer = $this->volunteerRepository->find($volunteer['volunteer_id']);

            if ($volunteer->getFieldOfficeId() === $fieldOfficeId) {
                $volunteerList[] = $volunteer;
            }
        }

        return $volunteerList;
    }

    /**
     * @param int $fieldOfficeId
     * @param int $year
     * @param int[] $months
     * @return Volunteer[]
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function getReAppointedVolunteers(int $fieldOfficeId, int $year, array $months): array
    {
        $volunteerList = [];
        $volunteers = $this->repository->findVolunteerIdsByMonthRange(
            $year,
            $months,
            'REAPPOINTED'
        );

        foreach ($volunteers as $volunteer) {
            $volunteer = $this->volunteerRepository->find($volunteer['volunteer_id']);

            if ($volunteer->getFieldOfficeId() === $fieldOfficeId) {
                $volunteerList[] = $volunteer;
            }
        }

        return $volunteerList;
    }

    /**
     * @param int $fieldOfficeId
     * @param int $year
     * @param int[] $months
     * @param Volunteer[] $inactive
     * @return Volunteer[]
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function getDroppedVolunteers(int $fieldOfficeId, int $year, array $months, array $inactive): array
    {
        $volunteerList = [];
        $inactiveVolunteerIds = [];
        $volunteers = $this->repository->findVolunteerIdsByMonthRange(
            $year,
            $months,
            'DROPPED'
        );


        foreach ($inactive as $inactiveVolunteer) {
            $inactiveVolunteerIds[] = $inactiveVolunteer->getVolunteerId();
        }

        foreach ($volunteers as $volunteer) {
            if (in_array(intval($volunteer['volunteer_id']), $inactiveVolunteerIds)) {
                continue;
            }
            $volunteerList[$volunteer['volunteer_id']] = [
                'reason' => $volunteer['reason'],
                'date' => $volunteer['date'],
            ];
        }

        $droppedVolunteers = [];
        foreach ($volunteerList as $volunteerId=> $volunteerData) {
            $volunteer = $this->volunteerRepository->find($volunteerId);

            if ($volunteer->getFieldOfficeId() === $fieldOfficeId) {
                $volunteerData['volunteer'] = $volunteer;
                $droppedVolunteers[] = $volunteerData;
            }
        }

        return $droppedVolunteers;
    }
}