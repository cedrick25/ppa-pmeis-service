<?php

namespace App\Service\Volunteerism;

use App\Common\AppDateHelper;
use App\Common\AppFormatter;
use App\Entity\Quarters;
use App\Enum\Response as ResponseEnum;
use App\Model\Volunteer as VolunteerModel;
use App\Repository\CivilStatusRepository;
use App\Repository\EducationBackgroundRepository;
use App\Repository\FieldOfficesRepository;
use App\Repository\OccupationRepository;
use App\Repository\QuartersRepository;
use App\Repository\RegionsRepository;
use App\Repository\ReligionRepository;
use App\Repository\ResourceFacilitatorSessionRepository;
use App\Repository\SessionsRepository;
use App\Repository\VolunteerOperationsRepository;
use App\Repository\VolunteerRepository;
use Doctrine\ORM\Exception\ORMException;
use Exception;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Volunteer implements VolunteerInterface
{
    const APPOINTED = 'APPOINTED';
    const REAPPOINTED = 'REAPPOINTED';
    const DROPPED = 'DROPPED';
    const INACTIVE = 'INACTIVE';

    public function __construct(
        private ValidatorInterface                   $validator,
        private AppFormatter                         $appFormatter,
        private VolunteerRepository                  $repository,
        private QuartersRepository                   $quartersRepository,
        private AppDateHelper                        $appDateHelper,
        private SessionsRepository                   $sessionsRepository,
        private ResourceFacilitatorSessionRepository $resourceFacilitatorSessionRepository,
        private FieldOfficesRepository               $fieldOfficesRepository,
        private RegionsRepository                    $regionsRepository,
        private CivilStatusRepository                $civilStatusRepository,
        private ReligionRepository                   $religionRepository,
        private OccupationRepository                 $occupationRepository,
        private EducationBackgroundRepository        $educationBackgroundRepository,
        private VolunteerOperationsRepository        $volunteerOperationsRepository,
    ){}

    public function create(VolunteerModel $volunteerData): array
    {
        try {
            $errors = $this->validator->validate($volunteerData);

            if (count($errors) > 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::VALIDATING_FAILED, null, $this->appFormatter->formatErrors($errors));
            }

            $id = $this->repository->create($volunteerData);

            if ($id == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['app' => 'Volunteer already exist']);
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
            $volunteers = $this->repository->list();

            if ($volunteers == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $volunteers);
        } catch (CacheException|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function deleteById(int $id): array
    {
        try {
            $isDeleted = $this->repository->softDelete($id);

            if (! $isDeleted) {
                return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['app' => ResponseEnum::NO_DATA]);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_SUCCESS, null);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (\Doctrine\ORM\ORMException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['orm' => $exception->getMessage()]);
        }
    }

    public function updateById(int $id, VolunteerModel $volunteerData): array
    {
        try {
            $isUpdated = $this->repository->update($id, $volunteerData);

            if ($isUpdated !== ResponseEnum::OK) {
                return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['app' => $isUpdated]);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_SUCCESS, null);
        } catch (Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['app' => $e->getMessage()]);
        } catch (InvalidArgumentException $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['cache' => $e->getMessage()]);
        }
    }

    public function getById(int $id): array
    {
        try {
            $volunteer = $this->repository->getById($id);

            if (!$volunteer) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $volunteer);
        } catch (\Doctrine\DBAL\Exception | \Doctrine\DBAL\Driver\Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, null, ['app' => $e->getMessage()]);
        }
    }

    public function getPaginated(int $page, int $pageSize): array
    {
        try {
            $volunteers = $this->repository->paginated($page, $pageSize);

            if ($volunteers == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $volunteers);
        } catch (CacheException|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function getByFieldOfficeAndMonthRange(int $fieldOfficeId, int $quarterId): array
    {
        try {
            $quarter = $this->quartersRepository->find($quarterId);
            if ($quarter === null) {
                return [];
            }
            $months = $this->appDateHelper->getMonthsByQuarterString($quarter->getName());
            $volunteers = $this->repository->findByFieldOfficeAndMonthRange($fieldOfficeId, intval($quarter->getYear()), $months);

            if (sizeof($volunteers) <= 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $volunteers);
        } catch (\Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, null, ['app' => $e->getMessage()]);
        }
    }

    public function getApplicants(): array
    {
        try {
            $applicants = $this->repository->findApplicants();

            if (!$applicants) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $applicants);
        } catch (\Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, null, ['app' => $e->getMessage()]);
        }
    }

    public function updateVolunteerStatus(array $data): array
    {
        try {
            $isUpdated = $this->repository->updateVolunteerStatus($data);

            if ($isUpdated !== ResponseEnum::OK) {
                return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['app' => $isUpdated]);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_SUCCESS, null);
        } catch (Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['app' => $e->getMessage()]);
        } catch (InvalidArgumentException $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['cache' => $e->getMessage()]);
        }
    }

    public function getConsolidatedSocioDemographic(int $regionId): array
    {
        try {
            $volunteers = $this->repository->findByRegionId($regionId);

            if (sizeof($volunteers) <= 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $educationBackgrounds = $this->getEducationBackgrounds();
            $civilStatuses = $this->getCivilStatuses();
            $occupations = $this->getOccupations();
            $religions = $this->getReligions();

            $data = [];
            foreach ($volunteers as $volunteer) {
                $fieldOffice = $volunteer['field_office'];
                $civilStatus = $civilStatuses[$volunteer['civil_status']];
                $religion = $religions[$volunteer['religion']];
                $occupation = $occupations[$volunteer['occupation']];
                $educationBackground = $educationBackgrounds[$volunteer['education_attainment']];

                if (! isset($data[$fieldOffice]['gender'][$volunteer['gender']])) {
                    $data[$fieldOffice]['gender'][$volunteer['gender']] = 0;
                }

                if (! isset($data[$fieldOffice]['civil_status'][$civilStatus])) {
                    $data[$fieldOffice]['civil_status'][$civilStatus] = 0;
                }

                if (! isset($data[$fieldOffice]['religion'][$religion])) {
                    $data[$fieldOffice]['religion'][$religion] = 0;
                }

                if (! isset($data[$fieldOffice]['occupation'][$occupation])) {
                    $data[$fieldOffice]['occupation'][$occupation] = 0;
                }

                if (! isset($data[$fieldOffice]['education_attainment'][$educationBackground])) {
                    $data[$fieldOffice]['education_attainment'][$educationBackground] = 0;
                }

                $data[$fieldOffice]['gender'][$volunteer['gender']]++;
                $data[$fieldOffice]['civil_status'][$civilStatus]++;
                $data[$fieldOffice]['religion'][$religion]++;
                $data[$fieldOffice]['occupation'][$occupation]++;
                $data[$fieldOffice]['education_attainment'][$educationBackground]++;
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $data);
        } catch (\Exception | \Doctrine\DBAL\Driver\Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, null, ['app' => $e->getMessage()]);
        }
    }

    public function getVPADatabase(int $regionId): array
    {
        try {
            $data = [
                'header' => [],
                'volunteers' => []
            ];
            $region = $this->regionsRepository->find($regionId);
            $data['header']['region'] = $region->getName();
            $volunteers = $this->repository->findByRegionId($regionId);

            if (sizeof($volunteers) <= 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $educationBackgrounds = $this->getEducationBackgrounds();
            $civilStatuses = $this->getCivilStatuses();
            $occupations = $this->getOccupations();
            $religions = $this->getReligions();

            foreach ($volunteers as $volunteer) {
                $volunteer['religion'] = $religions[$volunteer['religion']];
                $volunteer['occupation'] = $occupations[$volunteer['occupation']];
                $volunteer['education_attainment'] = $educationBackgrounds[$volunteer['education_attainment']];
                $volunteer['civil_status'] = $civilStatuses[$volunteer['civil_status']];
                $data['volunteers'][] = $volunteer;
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $data);
        } catch (\Exception | \Doctrine\DBAL\Driver\Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, null, ['app' => $e->getMessage()]);
        }
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getVpaMonitoring(int $quarterId, int $fieldOfficeId): array
    {
        $quarterData = $this->quartersRepository->find($quarterId);
        if ($quarterData === null) {
            return [];
        }

        $months = $this->appDateHelper->getMonthsByQuarterString($quarterData->getName());
        $activeVolunteers = $this->repository
            ->findByFieldOfficeAndMonthRange($fieldOfficeId, intval($quarterData->getYear()), $months);

        $quarterYear = intval($quarterData->getYear());
        $startOfQuarterVpa = $this->getStartOfQuarterVpa($quarterData, $fieldOfficeId);
        $newAppointed = $this->getMonitoringByStatus($quarterYear, self::APPOINTED, $months, $activeVolunteers);
        $reappointed = $this->getMonitoringByStatus($quarterYear, self::REAPPOINTED, $months, $activeVolunteers);
        $dropped = $this->getMonitoringByStatus($quarterYear, self::DROPPED, $months, $activeVolunteers);
        $totalNumberOfVpa = ($startOfQuarterVpa + $newAppointed) - $dropped;
        $inactive = $this->repository->findInactiveVolunteersByFieldOfficeAndMonthRangeV2(
            $fieldOfficeId,
            intval($quarterData->getYear()),
            $months,
            $activeVolunteers
        );
        $totalActiveVpa = $totalNumberOfVpa - count($inactive);
        $percentOfVpaMobilized = ($totalActiveVpa / $totalNumberOfVpa) * 100;

        return [
            'start_of_quarter_vpa' => $startOfQuarterVpa,
            'new_appointed' => $newAppointed,
            'reappointed' => $reappointed,
            'dropped' => $dropped,
            'total_number_of_vpa_during_quarter' => $totalNumberOfVpa,
            'inactive' => count($inactive),
            'total_active_vpa' => $totalActiveVpa,
            'percentage_of_vpa_mobilized' => $percentOfVpaMobilized,
        ];
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function getStartOfQuarterVpa(?Quarters $quarterData, int $fieldOfficeId): int
    {
        $previousQuarter = $this->quartersRepository->fetchPreviousQuarterByNameAndYear($quarterData->getName(), intval($quarterData->getYear()));

        if ($previousQuarter === null) {
            return 0;
        }
        $previousMonths = $this->appDateHelper->getMonthsByQuarterString($previousQuarter->getName());
        $previousActiveVolunteers = $this->repository
            ->findByFieldOfficeAndMonthRange($fieldOfficeId, intval($previousQuarter->getYear()), $previousMonths);

        $previousDroppedVolunteerIds = $this->getVolunteersIdByStatus(
            self::DROPPED,
            intval($previousQuarter->getYear()),
            $previousMonths,
            $previousActiveVolunteers
        );

        $results = 0;
        foreach ($previousActiveVolunteers as $previousActiveVolunteer) {
            if (in_array($previousActiveVolunteer->getVolunteerId(), $previousDroppedVolunteerIds)) {
                continue;
            }

            $results++;
        }

        return $results;
    }

    /**
     * @param int[] $months
     * @param \App\Entity\Volunteer[] $activeVolunteers
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function getMonitoringByStatus(
        int $year,
        string $status,
        array $months,
        array $activeVolunteers,
    ): int {
        $newVolunteersId = $this->getVolunteersIdByStatus(
            $status,
            $year,
            $months,
            $activeVolunteers
        );

        $results = 0;
        foreach ($activeVolunteers as $activeVolunteer) {
            if (in_array($activeVolunteer->getVolunteerId(), $newVolunteersId)) {
                $results++;
            }
        }

        return $results;
    }

    private function getCivilStatuses(): array
    {
        $civilStatuses = [];
        $rawCivilStatuses = $this->civilStatusRepository->findAll();
        foreach ($rawCivilStatuses as $civilStatus) {
            $civilStatuses[$civilStatus->getCivilStatusId()] = $civilStatus->getName();
        }

        return $civilStatuses;
    }

    private function getReligions(): array
    {
        $religions = [];
        $rawReligions = $this->religionRepository->findAll();
        foreach ($rawReligions as $religion) {
            $religions[$religion->getReligionId()] = $religion->getName();
        }

        return $religions;
    }

    private function getOccupations():array
    {
        $occupations = [];
        $rawOccupations = $this->occupationRepository->findAll();
        foreach ($rawOccupations as $occupation) {
            $occupations[$occupation->getOccupationIdId()] = $occupation->getName();
        }

        return $occupations;
    }

    private function getEducationBackgrounds(): array
    {
        $educationBackgrounds = [];
        $rawEducationBackgrounds = $this->educationBackgroundRepository->findAll();
        foreach ($rawEducationBackgrounds as $educationBackground) {
            $educationBackgrounds[$educationBackground->getEducationBackgroundId()] = $educationBackground->getName();
        }

        return $educationBackgrounds;
    }

    /**
     * @param int $year
     * @param int[] $months
     * @param string $status
     * @param \App\Entity\Volunteer[] $activeVolunteers
     * @return array<int, array<string, mixed>>
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function getVolunteersIdByStatus(
        string $status,
        int $year,
        array $months,
        array $activeVolunteers
    ): array {
        $volunteersId = [];
        $activeVolunteersIds = [];
        $volunteerOperations = $this->volunteerOperationsRepository->findVolunteerIdsByMonthRange($year, $months, $status);

        foreach ($activeVolunteers as $activeVolunteer) {
            $activeVolunteersIds[] = $activeVolunteer->getVolunteerId();
        }

        foreach ($volunteerOperations as $volunteerOperation) {
            if (! in_array(intval($volunteerOperation['volunteer_id']), $activeVolunteersIds)) {
                continue;
            }
            $volunteersId[] = $volunteerOperation['volunteer_id'];
        }

        return $volunteersId;
    }
}