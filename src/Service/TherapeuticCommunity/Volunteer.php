<?php

namespace App\Service\TherapeuticCommunity;

use App\Common\AppDateHelper;
use App\Common\AppFormatter;
use App\Enum\Response as ResponseEnum;
use App\Repository\CivilStatusRepository;
use App\Repository\EducationBackgroundRepository;
use App\Repository\FieldOfficesRepository;
use App\Repository\OccupationRepository;
use App\Repository\QuartersRepository;
use App\Repository\RegionsRepository;
use App\Repository\ReligionRepository;
use App\Repository\ResourceFacilitatorSessionRepository;
use App\Repository\SessionsRepository;
use App\Repository\VolunteerRepository;
use Doctrine\ORM\Exception\ORMException;
use Exception;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use \App\Model\Volunteer as VolunteerModel;

class Volunteer implements VolunteerInterface
{
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

    public function getConsolidatedSocioDemographic(int $quarterId): array
    {
        try {
            $quarterData = $this->quartersRepository->find($quarterId);

            if ($quarterData === null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $sessionIds = $this->sessionsRepository->findSessionsIdsByQuarter($quarterData);
            $sessionIds = array_map(fn($sessionId) => $sessionId['session_id'], $sessionIds);

            $volunteerIds = $this->resourceFacilitatorSessionRepository->getVolunteerIdsBySessionIds($sessionIds);
            $volunteerIds = array_map(fn($volunteerId) => $volunteerId['resourceFacilitatorId'], $volunteerIds);

            $volunteers = $this->repository->findByIds($volunteerIds);

            $civilStatuses = [];
            $rawCivilStatuses = $this->civilStatusRepository->findAll();
            foreach ($rawCivilStatuses as $civilStatus) {
                $civilStatuses[$civilStatus->getCivilStatusId()] = $civilStatus->getName();
            }

            $religions = [];
            $rawReligions = $this->religionRepository->findAll();
            foreach ($rawReligions as $religion) {
                $religions[$religion->getReligionId()] = $religion->getName();
            }

            $occupations = [];
            $rawOccupations = $this->occupationRepository->findAll();
            foreach ($rawOccupations as $occupation) {
                $occupations[$occupation->getOccupationIdId()] = $occupation->getName();
            }

            $educationBackgrounds = [];
            $rawEducationBackgrounds = $this->educationBackgroundRepository->findAll();
            foreach ($rawEducationBackgrounds as $educationBackground) {
                $educationBackgrounds[$educationBackground->getEducationBackgroundId()] = $educationBackground->getName();
            }

            $data = [];
            foreach ($volunteers as $volunteer) {
                $regionName = $this->fieldOfficesRepository->getRegionByFieldOfficeId($volunteer['fieldOfficeId'])['region_name'];
                $civilStatus = $civilStatuses[$volunteer['civilStatus']];
                $religion = $religions[$volunteer['religion']];
                $occupation = $occupations[$volunteer['occupation']];
                $educationBackground = $educationBackgrounds[$volunteer['educationAttainment']];

                if (! isset($data[$regionName]['gender'][$volunteer['gender']])) {
                    $data[$regionName]['gender'][$volunteer['gender']] = 0;
                }

                if (! isset($data[$regionName]['civilStatus'][$civilStatus])) {
                    $data[$regionName]['civilStatus'][$civilStatus] = 0;
                }

                if (! isset($data[$regionName]['religion'][$religion])) {
                    $data[$regionName]['religion'][$religion] = 0;
                }

                if (! isset($data[$regionName]['occupation'][$occupation])) {
                    $data[$regionName]['occupation'][$occupation] = 0;
                }

                if (! isset($data[$regionName]['educationAttainment'][$educationBackground])) {
                    $data[$regionName]['educationAttainment'][$educationBackground] = 0;
                }

                $data[$regionName]['gender'][$volunteer['gender']]++;
                $data[$regionName]['civilStatus'][$civilStatus]++;
                $data[$regionName]['religion'][$religion]++;
                $data[$regionName]['occupation'][$occupation]++;
                $data[$regionName]['educationAttainment'][$educationBackground]++;
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $data);
        } catch (\Exception | \Doctrine\DBAL\Driver\Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, null, ['app' => $e->getMessage()]);
        }
    }

    public function getVPADatabase(int $quarterId, int $fieldOfficeId): array
    {
        try {
            $data = [
                'header' => [],
                'volunteers' => []
            ];
            $quarterData = $this->quartersRepository->find($quarterId);
            $fieldOffice = $this->fieldOfficesRepository->find($fieldOfficeId);
            $region = $this->regionsRepository->find($fieldOffice->getRegionId());
            $data['header']['fieldOffice'] = $fieldOffice->getName();
            $data['header']['region'] = $region->getName();

            if ($quarterData === null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $civilStatuses = [];
            $rawCivilStatuses = $this->civilStatusRepository->findAll();
            foreach ($rawCivilStatuses as $civilStatus) {
                $civilStatuses[$civilStatus->getCivilStatusId()] = $civilStatus->getName();
            }

            $religions = [];
            $rawReligions = $this->religionRepository->findAll();
            foreach ($rawReligions as $religion) {
                $religions[$religion->getReligionId()] = $religion->getName();
            }

            $occupations = [];
            $rawOccupations = $this->occupationRepository->findAll();
            foreach ($rawOccupations as $occupation) {
                $occupations[$occupation->getOccupationIdId()] = $occupation->getName();
            }

            $educationBackgrounds = [];
            $rawEducationBackgrounds = $this->educationBackgroundRepository->findAll();
            foreach ($rawEducationBackgrounds as $educationBackground) {
                $educationBackgrounds[$educationBackground->getEducationBackgroundId()] = $educationBackground->getName();
            }

            $sessionIds = $this->sessionsRepository->findSessionsIdsByQuarter($quarterData);
            $sessionIds = array_map(fn($sessionId) => $sessionId['session_id'], $sessionIds);

            $volunteerIds = $this->resourceFacilitatorSessionRepository->getVolunteerIdsBySessionIds($sessionIds);
            $volunteerIds = array_map(fn($volunteerId) => $volunteerId['resourceFacilitatorId'], $volunteerIds);

            $volunteers = $this->repository->findByIdsV2($volunteerIds);

            foreach ($volunteers as $volunteer) {
                if ($volunteer['fieldOfficeId'] === $fieldOfficeId) {
                    $volunteer['religion'] = $religions[$volunteer['religion']];
                    $volunteer['occupation'] = $occupations[$volunteer['occupation']];
                    $volunteer['educationAttainment'] = $educationBackgrounds[$volunteer['educationAttainment']];
                    $volunteer['civilStatus'] = $civilStatuses[$volunteer['civilStatus']];
                    $data['volunteers'][] = $volunteer;
                }
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $data);
        } catch (\Exception | \Doctrine\DBAL\Driver\Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, null, ['app' => $e->getMessage()]);
        }
    }
}