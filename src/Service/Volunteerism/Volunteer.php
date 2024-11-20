<?php

namespace App\Service\Volunteerism;

use App\Common\AppDateHelper;
use App\Common\AppFormatter;
use App\Entity\Quarters;
use App\Enum\AuditTrailActions;
use App\Enum\Response as ResponseEnum;
use App\Enum\SystemSettingNames;
use App\Enum\VolunteerStatus;
use App\Model\Volunteer as VolunteerModel;
use App\Repository\CivilStatusRepository;
use App\Repository\EducationBackgroundRepository;
use App\Repository\FieldOfficesRepository;
use App\Repository\IdSupportRepository;
use App\Repository\JailDecongestionPersonResponsibleRepository;
use App\Repository\JailDecongestionRepository;
use App\Repository\OccupationRepository;
use App\Repository\PmdPersonResponsibleRepository;
use App\Repository\ProgramMaterialsDevelopmentRepository;
use App\Repository\QuartersRepository;
use App\Repository\RegionsRepository;
use App\Repository\ReligionRepository;
use App\Repository\ResMobSecuredByRepository;
use App\Repository\ResourceFacilitatorSessionRepository;
use App\Repository\ResourceMobilizationRepository;
use App\Repository\RjConductedProcessPersonsInvolvedRepository;
use App\Repository\RJConductProcessesRepository;
use App\Repository\SessionsRepository;
use App\Repository\SocialMarketingPersonInvolvedRepository;
use App\Repository\SocialMarketingRepository;
use App\Repository\RJRelatedActivitiesRepository;
use App\Repository\TechnicalAssistancePersonsInvolvedRepository;
use App\Repository\TechnicalAssistanceRepository;
use App\Repository\VolunteerSupervisionClientsRepository;
use App\Repository\VpaAssociationInitiatedActivitiesRepository;
use App\Repository\VolunteerRepository;
use App\Repository\VolunteerSupervisionsRepository;
use App\Service\System\AuditTrail;
use App\Service\System\SystemCodeSettings;
use Doctrine\ORM\Exception\ORMException;
use Exception;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use TCPDF;

class Volunteer implements VolunteerInterface
{
    private string $shortName;

    public function __construct(
        private ValidatorInterface                          $validator,
        private AppFormatter                                $appFormatter,
        private VolunteerRepository                         $repository,
        private QuartersRepository                          $quartersRepository,
        private SessionsRepository                          $sessionsRepository,
        private AppDateHelper                               $appDateHelper,
        private RegionsRepository                           $regionsRepository,
        private CivilStatusRepository                       $civilStatusRepository,
        private ReligionRepository                          $religionRepository,
        private OccupationRepository                        $occupationRepository,
        private EducationBackgroundRepository               $educationBackgroundRepository,
        private FieldOfficesRepository                       $fieldOfficesRepository,
        private VolunteerSupervisionsRepository             $volunteerSupervisionsRepository,
        private VolunteerSupervisionClientsRepository       $supervisionClientsRepository,
        private ResourceFacilitatorSessionRepository        $resourceFacilitatorSessionRepository,
        private SocialMarketingRepository                   $socialMarketingRepository,
        private SocialMarketingPersonInvolvedRepository     $socialMarketingPersonInvolvedRepository,
        private RJRelatedActivitiesRepository               $rjRelatedActivitiesRepository,
        private RJConductProcessesRepository                $conductProcessesRepository,
        private RjConductedProcessPersonsInvolvedRepository $conductedProcessPersonsInvolvedRepository,
        private VpaAssociationInitiatedActivitiesRepository $vpaAssociationRepository,
        private IdSupportRepository                         $idSupportRepository,
        private TechnicalAssistanceRepository               $technicalAssistanceRepository,
        private TechnicalAssistancePersonsInvolvedRepository $technicalAssistancePersonsInvolvedRepository,
        private JailDecongestionRepository                  $jailDecongestionRepository,
        private JailDecongestionPersonResponsibleRepository $jailDecongestionPersonResponsibleRepository,
        private ResourceMobilizationRepository              $resourceMobilizationRepository,
        private ResMobSecuredByRepository                   $resMobSecuredByRepository,
        private ProgramMaterialsDevelopmentRepository       $programMaterialsDevelopmentRepository,
        private PmdPersonResponsibleRepository              $pmdPersonResponsibleRepository,
        private AuditTrail                                  $auditTrail,
        private SystemCodeSettings                          $systemCodeSettings,
    ) {
        $class = new \ReflectionClass($this);
        $this->shortName = $class->getShortName();
    }

    public function create(VolunteerModel $volunteerData): array
    {
        try {
            $errors = $this->validator->validate($volunteerData);

            if (count($errors) > 0) {
                return $this->appFormatter->formatResponse(
                    ResponseEnum::VALIDATING_FAILED,
                    null,
                    $this->appFormatter->formatErrors($errors)
                );
            }

            $id = $this->repository->create($volunteerData);

            if ($id == null) {
                return $this->appFormatter->formatResponse(
                    ResponseEnum::CREATING_FAILED,
                    null,
                    ['app' => 'Volunteer already exist']
                );
            }

            $this->auditTrail->log(AuditTrailActions::CREATE, $volunteerData->jsonSerialize(), $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_SUCCESS, ['id' => $id]);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::CREATING_FAILED,
                null,
                ['cache' => $exception->getMessage()]
            );
        } catch (ORMException $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::CREATING_FAILED,
                null,
                ['orm' => $exception->getMessage()]
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
            $volunteers = $this->repository->list();

            if ($volunteers == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $volunteers);
        } catch (CacheException | InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_FAILED,
                null,
                ['cache' => $exception->getMessage()]
            );
        }
    }

    public function getByFieldOffice(int $fieldOfficeId): array
    {
        $volunteers = $this->repository->findByFieldOffice($fieldOfficeId);

        if ($volunteers == null) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $volunteers);
    }

    public function deleteById(int $id): array
    {
        try {
            $isDeleted = $this->repository->softDelete($id);

            if (!$isDeleted) {
                return $this->appFormatter->formatResponse(
                    ResponseEnum::DELETING_FAILED,
                    null,
                    ['app' => ResponseEnum::NO_DATA]
                );
            }

            $this->auditTrail->log(AuditTrailActions::DELETE, [], $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_SUCCESS, null);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::DELETING_FAILED,
                null,
                ['cache' => $exception->getMessage()]
            );
        } catch (\Doctrine\ORM\ORMException $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::DELETING_FAILED,
                null,
                ['orm' => $exception->getMessage()]
            );
        }
    }

    public function updateById(int $id, VolunteerModel $volunteerData): array
    {
        try {
            $isUpdated = $this->repository->update($id, $volunteerData);

            if ($isUpdated !== ResponseEnum::OK) {
                return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['app' => $isUpdated]);
            }

            $this->auditTrail->log(AuditTrailActions::UPDATE, $volunteerData->jsonSerialize(), $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_SUCCESS, null);
        } catch (Exception $e) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::UPDATING_FAILED,
                null,
                ['app' => $e->getMessage()]
            );
        } catch (InvalidArgumentException $e) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::UPDATING_FAILED,
                null,
                ['cache' => $e->getMessage()]
            );
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
            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_SUCCESS,
                null,
                ['app' => $e->getMessage()]
            );
        }
    }

    public function getPaginated(string $status, int $page, int $pageSize, int $fieldOfficeId): array
    {
        try {
            $status = strtoupper($status);
            $volunteers = (VolunteerStatus::EXPIRING == $status) ?
                $this->repository->paginatedExpiring($page, $pageSize, $fieldOfficeId) :
                $this->repository->paginated($status, $page, $pageSize, $fieldOfficeId);

            if ($volunteers == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $volunteers);
        } catch (CacheException | InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_FAILED,
                null,
                ['cache' => $exception->getMessage()]
            );
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
            $volunteers = $this->repository->findByFieldOfficeAndMonthRange(
                $fieldOfficeId,
                intval($quarter->getYear()),
                $months
            );

            if (sizeof($volunteers) <= 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $volunteers);
        } catch (\Exception $e) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::UPDATING_FAILED,
                null,
                ['app' => $e->getMessage()]
            );
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
            return $this->appFormatter->formatResponse(
                ResponseEnum::UPDATING_FAILED,
                null,
                ['app' => $e->getMessage()]
            );
        }
    }

    public function updateVolunteerStatus(array $data): array
    {
        try {
            $isUpdated = $this->repository->updateVolunteerStatus($data);

            if ($isUpdated !== ResponseEnum::OK) {
                return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['app' => $isUpdated]);
            }

            $this->auditTrail->log(AuditTrailActions::UPDATE, $data, $this->shortName, (int) $data['id']);

            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_SUCCESS, null);
        } catch (Exception $e) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::UPDATING_FAILED,
                null,
                ['app' => $e->getMessage()]
            );
        } catch (InvalidArgumentException $e) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::UPDATING_FAILED,
                null,
                ['cache' => $e->getMessage()]
            );
        }
    }

    public function getConsolidatedSocioDemographic(int $regionId): array
    {
        try {
            $volunteers = $this->repository->findByRegionId($regionId);
            // dd($volunteers, $regionId);
            $region = $this->regionsRepository->find($regionId);
            $regionsName = $region->getName();
            if (sizeof($volunteers) <= 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $educationBackgrounds = $this->getEducationBackgrounds();
            $civilStatuses = $this->getCivilStatuses();
            $occupations = $this->getOccupations();
            $religions = $this->getReligions();

            $data = [];

            $fieldOffices = $this->fieldOfficesRepository->findBy(['regionId' => $regionId]);

            if ($fieldOffices) {
                foreach ($fieldOffices as $fieldOffice) {
                    $data[$fieldOffice->getName()] = [
                        'gender' => [],
                        'civil_status' => [],
                        'religion' => [],
                        'occupation' => [],
                        'education_attainment' => [],
                        'age' => [],
                    ];
                }
            }

            $ageRanges = [
                '15-24' => ['min' => 15, 'max' => 24],
                '25-34' => ['min' => 25, 'max' => 34],
                '35-44' => ['min' => 35, 'max' => 44],
                '45-54' => ['min' => 45, 'max' => 54],
                '55-64' => ['min' => 55, 'max' => 64],
            ];
            foreach ($volunteers as $volunteer) {
                $fieldOffice = $volunteer['field_office'];
                $civilStatus = $civilStatuses[$volunteer['civil_status']];
                $religion = $religions[$volunteer['religion']];
                $occupation = $occupations[$volunteer['occupation']];
                $educationBackground = $educationBackgrounds[$volunteer['education_attainment']];

                if (!isset($data[$fieldOffice]['gender'][$volunteer['gender']])) {
                    $data[$fieldOffice]['gender'][$volunteer['gender']] = 0;
                }

                if (!isset($data[$fieldOffice]['civil_status'][$civilStatus])) {
                    $data[$fieldOffice]['civil_status'][$civilStatus] = 0;
                }

                if (!isset($data[$fieldOffice]['religion'][$religion])) {
                    $data[$fieldOffice]['religion'][$religion] = 0;
                }

                if (!isset($data[$fieldOffice]['occupation'][$occupation])) {
                    $data[$fieldOffice]['occupation'][$occupation] = 0;
                }

                if (!isset($data[$fieldOffice]['education_attainment'][$educationBackground])) {
                    $data[$fieldOffice]['education_attainment'][$educationBackground] = 0;
                }

                $data[$fieldOffice]['gender'][$volunteer['gender']]++;
                $data[$fieldOffice]['civil_status'][$civilStatus]++;
                $data[$fieldOffice]['religion'][$religion]++;
                $data[$fieldOffice]['occupation'][$occupation]++;
                $data[$fieldOffice]['education_attainment'][$educationBackground]++;

                $dob = $this->appDateHelper->convertStringToImmutableDate($volunteer['date_of_birth']);

                if (null == $dob) {
                    if (!isset($data[$fieldOffice]['age'][0])) {
                        $data[$fieldOffice]['age'][0] = 0;
                    }

                    $data[$fieldOffice]['age'][0]++;
                    continue;
                }

                $age = $dob->diff(new \DateTime());

                if ($age->y > 65) {
                    if (!isset($data[$fieldOffice]['age'][65])) {
                        $data[$fieldOffice]['age'][65] = 0;
                    }

                    $data[$fieldOffice]['age'][65]++;
                    continue;
                }

                foreach ($ageRanges as $key => $range) {
                    if ($range['max'] >= $age->y && $range['min'] <= $age->y) {
                        if (!isset($data[$fieldOffice]['age'][$key])) {
                            $data[$fieldOffice]['age'][$key] = 0;
                        }

                        $data[$fieldOffice]['age'][$key]++;
                    }
                }
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

    public function getVPADatabase(int $regionId, int $fieldOfficeId = null): array
    {
        try {
            $data = [
                'header' => [],
                'volunteers' => []
            ];
            
            $region = $this->regionsRepository->find($regionId);
            $data['header']['region'] = $region->getName();
            if (null !== $fieldOfficeId) {
                $fieldOffice = $this->fieldOfficesRepository->find($fieldOfficeId);
                $data['header']['field_office'] = $fieldOffice->getName();
            }
            $volunteers = (null != $fieldOfficeId) ? $this->repository->findByFieldOffice($fieldOfficeId)
                : $this->repository->findByRegionId($regionId);

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
        } catch (\Exception $e) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_SUCCESS,
                null,
                ['app' => $e->getMessage()]
            );
        }
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getVpaMonitoring(int $quarterId, int $fieldOfficeId): array
    {
        $quarterData = $this->quartersRepository->find($quarterId);

        if ($quarterData === null) {
            return [];
        }

        $startOfQuarterVpa = $this->repository->findAppointedVpaByFieldOffice($fieldOfficeId);
        $minMaxDate = $this->quartersRepository->getQuarterMinMaxDate($quarterData);
        $volunteerIds = array_map(fn ($volunteer) => $volunteer->getVolunteerId(), $startOfQuarterVpa);
        $appointedDuringQuarter = $this->getAppointedVpaDuringQuarterCount($minMaxDate, $fieldOfficeId);
        $dropped = $this->getDroppedVolunteers($minMaxDate, $fieldOfficeId);
        // we change the criteria of startOfQuarterVpa and included all vpa that is appointed regardless if it is new/
        $totalNumberOfVpa = count($startOfQuarterVpa) - $dropped;

        // to get number 7 wherein volunteers is in table
        // select * VPA I.C.3 (Name/volunteer id in table column name)
        // to get number 13 here, load volunteer supervision as a whole
        $supervisionActivities = $this->volunteerSupervisionsRepository->findByVolunteerIds($volunteerIds);
        $supervisionActivitiesId = array_map(
            fn ($supervisionActivity) => $supervisionActivity['volunteer_supervisions_id'],
            $supervisionActivities
        );
        $supervisionActivityClients = $this->supervisionClientsRepository
            ->findBySupervisionId($supervisionActivitiesId);
        $totalNumberOfClientsSupervised = \array_unique(
            array_map(
                fn ($supervisionActivityClient) => $supervisionActivityClient['client_id'],
                $supervisionActivityClients
            )
        );
        $supervisionActivitiesVolunteerIds = array_unique(
            array_map(
                fn ($supervisionActivity) => $supervisionActivity['volunteer_id'],
                $supervisionActivities
            )
        );

        // Table 1.A.1
        $sessions = $this->sessionsRepository->getSessionDataByQuarterAndFieldOfficeId($fieldOfficeId, $quarterData);
        $sessionIds = array_map(fn ($session) => intval($session['session_id']), $sessions);
        $vpaActingAsResourceIndividuals = $this->getVpaActingAsResourceIndividuals(
            $quarterData,
            $minMaxDate,
            $fieldOfficeId,
            $sessionIds
        );
        $activeVolunteersId = \array_unique(
            array_merge($supervisionActivitiesVolunteerIds, $vpaActingAsResourceIndividuals)
        );

        $inactiveVolunteersId = \count(\array_diff($volunteerIds, $activeVolunteersId));

        $vpaActingBothSupervisingAndResourceIndividual = \array_intersect(
            $supervisionActivitiesVolunteerIds,
            $vpaActingAsResourceIndividuals
        );
        $noOfVpaActingAsResourceIndividuals = \count(\array_filter(
            $vpaActingAsResourceIndividuals,
            function ($id) use ($vpaActingBothSupervisingAndResourceIndividual) {
                return !in_array($id, $vpaActingBothSupervisingAndResourceIndividual);
            }
        ));

        $noOfVpaSupervisingClients = \count(\array_filter(
            $supervisionActivitiesVolunteerIds,
            function ($id) use ($vpaActingBothSupervisingAndResourceIndividual) {
                return !in_array($id, $vpaActingBothSupervisingAndResourceIndividual);
            }
        ));

        $totalActiveVpa = $totalNumberOfVpa - $inactiveVolunteersId;

        $totalNumberOfVpaMobilize = ($noOfVpaSupervisingClients + $noOfVpaActingAsResourceIndividuals) +
            \count($vpaActingBothSupervisingAndResourceIndividual);
        $percentOfVpaMobilized = $totalActiveVpa > 0 ? ($totalNumberOfVpaMobilize / $totalActiveVpa) * 100 : 0;
        $noOfServicesRenderedByVpaDuringQuarter = $this->getNumberOfServicesRenderedByVpaDuringQuarter(
            $minMaxDate,
            $quarterData->getQuarterId(),
            $volunteerIds,
            $sessionIds,
            $fieldOfficeId,
        );

        return [
            'start_of_quarter_vpa' => \count($startOfQuarterVpa),
            'new_appointed' => $appointedDuringQuarter,
            'dropped' => $dropped,
            'total_number_of_vpa_during_quarter' => $totalNumberOfVpa,
            'inactive' => $inactiveVolunteersId,
            'total_active_vpa' => $totalActiveVpa,
            'no_of_vpa_supervising_clients' => $noOfVpaSupervisingClients,
            'total_number_of_clients_supervised' => \count($totalNumberOfClientsSupervised),
            'no_of_vpa_acting_as_resource_individuals' => $noOfVpaActingAsResourceIndividuals,
            'vpa_acting_both_supervising_and_resource_individual' =>
            \count($vpaActingBothSupervisingAndResourceIndividual),
            'total_number_of_vpa_mobilize' => $totalNumberOfVpaMobilize,
            'percent_of_vpa_mobilized' => $percentOfVpaMobilized,
            'no_of_services_rendered_during_quarter' => $noOfServicesRenderedByVpaDuringQuarter,
            'no_of_services_rendered_by_vpa' => $totalActiveVpa > 0 ?
                ($noOfServicesRenderedByVpaDuringQuarter / $totalActiveVpa) : 0,
        ];
    }

    public function getCertificate(array $data): string
    {
        $volunteer = $this->repository->find($data['volunteer_id']);
        $address = $volunteer->getPresentAddress();
        $middleInitial = $volunteer->getMiddleName() != null ? substr($volunteer->getMiddleName(), 0, 1) : '';
        $fullName = strtoupper($volunteer->getFirstName()) . ' ' . strtoupper($middleInitial) . '. ' . strtoupper($volunteer->getLastName());
        $fieldOffice = $this->fieldOfficesRepository->find($volunteer->getFieldOfficeId());
        $fieldOfficeName = $fieldOffice->getName();
        $dateOfAppointment = $volunteer->getDateAppointed()->format('F d, Y');
        $region = $this->regionsRepository->find($fieldOffice->getRegionId());
        $regionName = $region->getName();
        $code = $this->systemCodeSettings->getByName(SystemSettingNames::VPA_CERTIFICATE_REPORT_CODE);
        $administrator = $this->systemCodeSettings->getByName(SystemSettingNames::OIC_ADMINISTRATOR);

        $pdf = new TCPDF();
        $pdf->setCreator(PDF_CREATOR);
        $pdf->setAuthor('PPA');
        $pdf->setTitle('Certificate');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->startPage();
        $logo = dirname(__DIR__) . '/../../assets/ppa.png';
        $heading = <<<EOD
            <h5 style="text-align: right;">$code</h5>
            <h3 style="text-align: center;line-height: 5px;">Republic of the Philippines</h3>
            <h3 style="text-align: center;line-height: 5px;">Department of Justice</h3>
            <h2 style="text-align: center;line-height: 5px;">PAROLE AND PROBATION ADMINISTRATION</h2>
            <h5 style="text-align: center;line-height: 5px;">DOJ Agencies Building</h5>
            <h5 style="text-align: center;line-height: 5px;">NIA Road corner East Avenue, Diliman</h5>
            <h5 style="text-align: center;line-height: 5px;">1110 Quezon City</h5>
        EOD;

        $pdf->writeHTMLCell(0, 0, '', '', $heading);
        $pdf->Image($logo,  85, 55, 40, 40, '', '', 'T', false, 300, '', false, false, 1);
        $body = <<<EOD
            <div>
                <h1 style="text-align: center;"><i>Certificate of Appointment</i></h1>
                <h1 style="text-align: center;font-size: 40px;font-weight: bold">$fullName</h1>
                <h2 style="text-align: center;font-weight: normal;"><i>of</i></h2>
                <h2 style="text-align: center;font-size: 20px;font-weight: normal">$address</div>
                <h3 style="text-align: center;font-weight: normal;">
                    is hereby appointed as
                    <span style="font-size: 13px;font-weight: bold;">VOLUNTEER PROBATION ASSISTANT</span> of the
                </h3>
                <h2 style="text-align: center;font-weight: normal;line-height: 5px;"><i>$fieldOfficeName</i></h2>
                <h2 style="text-align: center;font-weight: normal;line-height: 5px;"><i>$regionName</i></h2>
                <div></div>
                <div style="text-align: center;">
                    <span style="text-align: center;font-weight: bold;font-size: 18px;">$dateOfAppointment</span>
                    <br/>
                    <span style="font-weight: bold;font-size: 18px;">Date of Appointment</span>
                </div>
                <div></div>
                <div></div>
                <h1 style="text-align: center;font-size: 20px;line-height: 5px;">$administrator</h1>
                <h2 style="text-align: center;font-weight:normal;line-height: 5px;">Administrator</h2>
            </div>
        EOD;

        $pdf->SetXY(110, 200);
        $pdf->setMargins(20, 0, 0);
        $pdf->writeHTMLCell(0, 0, 0, 95, $body);
        $pdf->endPage();

        $this->auditTrail->log(AuditTrailActions::DOWNLOAD, $data, $this->shortName, $data['volunteer_id']);

        return $pdf->Output('mark.pdf', 'E');
        // return $pdf->Output('mark.pdf', 'D');
    }

    public function getId(array $data): string
    {
        if (\count($data['volunteer_ids']) > 4) {
            return 'Invalid ID count';
        }
        $volunteersId = $data['volunteer_ids'];
        $volunteers = $this->repository->findByIds($volunteersId);

        if (count($volunteers) == 0) {
          return "No volunteers found.";
        }

        $fieldOffice = $this->fieldOfficesRepository->find($volunteers[0]->getFieldOfficeId());

        if ($fieldOffice == null) {
            return 'No field office id';
        }

        $region = $this->regionsRepository->find($fieldOffice->getRegionId());

        $code = $this->systemCodeSettings->getByName(SystemSettingNames::VPA_CERTIFICATE_REPORT_CODE);
        $administrator = $this->systemCodeSettings->getByName(SystemSettingNames::OIC_ADMINISTRATOR);

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setAutoPageBreak(true);
        $pdf->setCreator(PDF_CREATOR);
        $pdf->setAuthor('PPA');
        $pdf->setTitle('ID');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->startPage();
        $logo = dirname(__DIR__) . '/../../assets/ppa.png';
        $picture = dirname(__DIR__) . '/../../assets/placeholder-1x1.gif';

        $pdf->writeHTMLCell(0, 0, '', '');
        foreach ($volunteersId as $key => $id) {
            switch ($key) {
                case 0:
                    $pdf->Image($logo, 2.5, 7.5, 15, 15, '', '', 'T', false, 300, '', false, false, 1);
                    $pdf->Image($picture, 41, 30, 25.4, 25.4, '', '', 'T', false, 300, '', false, false, 1);
                    break;
                case 1:
                    $pdf->Image($logo, 107.5, 7.5, 15, 15, '', '', 'T', false, 300, '', false, false, 1);
                    $pdf->Image($picture, 148, 30, 25.4, 25.4, '', '', 'T', false, 300, '', false, false, 1);
                    break;
                case 2:
                    $pdf->Image($logo, 2.5, 151.5, 15, 15, '', '', 'T', false, 300, '', false, false, 1);
                    $pdf->Image($picture, 41, 173, 25.4, 25.4, '', '', 'T', false, 300, '', false, false, 1);
                    break;
                default:
                    $pdf->Image($logo, 107.5, 151.5, 15, 15, '', '', 'T', false, 300, '', false, false, 1);
                    $pdf->Image($picture, 148, 173, 25.4, 25.4, '', '', 'T', false, 300, '', false, false, 1);
                    break;
            }
        }
        $front = <<<EOD
            <style>
                .full-name-container {
                    background-color: #f7ef4d;
                    height: 20px;
                }
                .full-name {
                    text-align: center;
                    font-weight: bold;
                }
                .full-name-small {
                    font-size: 10;
                }
                .full-name-large {
                    font-size: 20;
                }
                .title-container {
                    background-color: #e9ad63;
                    height: 20px;
                }
                .title {
                    text-align: center;
                    font-weight: bold;
                    color: #fff;
                    font-size: 15px;
                }
                .admin-name {
                    text-align: center;
                    line-height: 5px;
                }
                .admin-title {
                    text-align: center;
                    font-weight: normal;
                }
            </style>
            <table width="100%" cellpadding="0" border="0">
                <tr><td></td></tr>

        EOD;

        foreach ($volunteers as $index => $volunteer) {
            $middleInitial = $volunteer->getMiddleName() != null ? substr($volunteer->getMiddleName(), 0, 1) : '';
            $fullName = strtoupper($volunteer->getFirstName()) . ' ' . strtoupper($middleInitial) . '. ' . strtoupper($volunteer->getLastName());
            $volunteerId = 'RO-' . date('ym') . '-' . str_pad($volunteer->getVolunteerId(), 4, '0', STR_PAD_LEFT);
        
            $front .= $this->createFrontId(
                $index % 2 == 0 ? 'start' : 'end',
                $volunteer->getIdNumber() ?? $volunteerId,
                $fullName,
                $fieldOffice->getName(),
                $region->getName(),
                $administrator,
                count($volunteersId),
                $index
            );
        }

        $front .= <<<EOD
        
            </table>
        EOD;

        $pdf->writeHTMLCell(0, 0, 0, 0, $front);

        $pdf->AddPage();
        $pdf->setPage(2);

        $back = <<<EOD
            <style>
                table.back-page > tr > td {
                    border: 1px solid #000000;
                    text-align: center;
                    font-size: 10px;
                }
                table.back-page > tr > td.back-page-title {
                    text-align: left !important;
                    font-size: 11px;
                    font-weight: bold;
                }
                table.back-page > tr > td.force-center {
                    text-align: center;
                }
                table.back-page > tr > td.force-left {
                    text-align: left;
                }
                table.back-page > tr > td.no-border {
                    border: none;
                }
            </style>
            <table>
                <tr>
                    <td style="width: 6%"></td>
                    <td style="width: 94%"><table>
        EOD;

        foreach ($volunteers as $index => $volunteer) {
            $back .= $this->createBackId(
                $index % 2 == 0 ? 'start' : 'end',
                $code,
                $volunteer->getPresentAddress(),
                $volunteer->getBloodType(),
                $volunteer->getWeight(),
                $volunteer->getHeight(),
                $volunteer->getEmergencyName(),
                $volunteer->getEmergencyNumber(),
                $volunteer->getDateAppointed()->format('F d, Y'),
                date('F d, Y', strtotime($volunteer->getDateAppointed()->format('Y-m-d') . ' +2 years')),
                count($volunteersId),
                $index
            );
        }

        $back .= <<<EOD
                </table></td></tr>
            </table>
        EOD;

        $pdf->writeHTMLCell(0, 0, 0, 0, $back);
        $pdf->endPage();

        foreach ($volunteersId as $id) {
            $this->auditTrail->log(AuditTrailActions::DOWNLOAD, $data, $this->shortName, $id);
        }

        return $pdf->Output('mark.pdf', 'E');
        // return $pdf->Output('mark.pdf', 'D');
    }

    public function updateDateAppointedById(int $id): array
    {
        try {
            $isUpdated = $this->repository->updateDateAppointedToNow($id);

            if ($isUpdated !== ResponseEnum::OK) {
                return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['app' => $isUpdated]);
            }

            $this->auditTrail->log(AuditTrailActions::UPDATE, [], $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_SUCCESS, null);
        } catch (Exception $e) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::UPDATING_FAILED,
                null,
                ['app' => $e->getMessage()]
            );
        } catch (InvalidArgumentException $e) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::UPDATING_FAILED,
                null,
                ['cache' => $e->getMessage()]
            );
        }
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

    private function getOccupations(): array
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

    private function getAppointedVpaDuringQuarterCount(
        array $minMaxDate,
        int $fieldOfficeId
    ): int {
        return \count(
            $this->repository->getAppointedVpaDuringQuarter($minMaxDate, $fieldOfficeId)
        );
    }

    private function getDroppedVolunteers(
        array $minMaxDate,
        int $fieldOfficeId
    ): int {
        return count($this->repository->getDroppedVolunteer($minMaxDate, $fieldOfficeId));
    }

    private function getVpaActingAsResourceIndividuals(
        Quarters $quarterData,
        array $minMaxDate,
        int $fieldOfficeId,
        array $sessionIds,
    ): array {
        $volunteerIds = [];

        $vpaInSessions = $this->resourceFacilitatorSessionRepository->getDistinctVolunteerIdsBySessionIds($sessionIds);
        $vpaIdsInSessions = $vpaInSessions ?
            array_map(fn ($vpa) => (int) $vpa['resourceFacilitatorId'], $vpaInSessions) : [];

        // Table 1.B.1
        $vpaInRjConductedProcess = $this->conductProcessesRepository
            ->getVolunteerIdsByQuarterAndFieldOffice($quarterData->getQuarterId(), $fieldOfficeId);
        $vpaIdsInRjConductedProcess = $vpaInRjConductedProcess ?
            array_map(fn ($vpa) =>  (int) $vpa['persons_involved_id'], $vpaInRjConductedProcess) : [];

        // Table 1.B.2
        $vpasInvolvedInRJActivities = $this->rjRelatedActivitiesRepository
            ->getVolunteerIdsByQuarterAndFieldOffice($quarterData->getQuarterId(), $fieldOfficeId);
        $vpaInvolvedInRJActivitiesIds = $vpasInvolvedInRJActivities ?
            array_map(fn ($vpa) => (int) $vpa['persons_involved_id'], $vpasInvolvedInRJActivities) : [];

        // Table 1.C.4
        $vpasInvolvedInAssociationActivities = $this->vpaAssociationRepository
            ->getVolunteerIdsByDateRange($quarterData->getQuarterId(), $fieldOfficeId);
        $vpaInvolvedInAssociationActivitiesIds = $vpasInvolvedInAssociationActivities ?
            array_map(fn ($vpa) => $vpa['volunteer_id'], $vpasInvolvedInAssociationActivities) : [];

        // Support ID
        $vpaInvolvedInSupportId = $this->idSupportRepository->getVolunteerIdsByDateRange($minMaxDate, $fieldOfficeId);
        $vpaIdsInvolvedInSupportId = $vpaInvolvedInSupportId ?
            array_map(fn ($vpa) => (int) $vpa['vpa_personnel_id'], $vpaInvolvedInSupportId) : [];

        // Table 3.A.1 - 3
        $vpasInvolvedInSocialMarketing = $this->socialMarketingRepository
            ->getVolunteerIdsByDateRange($minMaxDate, $fieldOfficeId, 'INFORMATION_DISSEMINATION');
        $vpasInvolvedInSocialMarketingIds = $vpasInvolvedInSocialMarketing ?
            array_map(fn ($vpa) => (int) $vpa, $vpasInvolvedInSocialMarketing) : [];

        // SM 3
        $vpaInvolvedInTechnicalAssistants = $this->technicalAssistanceRepository
            ->getVolunteerIdsByDateRange($minMaxDate, $fieldOfficeId);
        $vpaInvolvedInTechnicalAssistantsId = $vpaInvolvedInTechnicalAssistants ?
            array_map(fn ($vpa) => (int) $vpa['persons_involved_id'], $vpaInvolvedInTechnicalAssistants) : [];

        //	RM IV,
        $vpaInvolvedInResourceMobilizations = $this->resourceMobilizationRepository
            ->getVolunteerIdsByDateRange($minMaxDate, $fieldOfficeId);
        $vpaInvolvedInResourceMobilizationsId = $vpaInvolvedInResourceMobilizations ?
            array_map(fn ($vpa) => (int) $vpa['secured_by_id'], $vpaInvolvedInResourceMobilizations) : [];

        //	PMD V,
        $vpaInvolvedInProgramMaterials = $this->programMaterialsDevelopmentRepository
            ->getVolunteerIdsByDateRange($minMaxDate, $fieldOfficeId);
        $vpaInvolvedInProgramMaterialsIds = $vpaInvolvedInProgramMaterials ?
            array_map(fn ($vpa) => (int) $vpa['person_responsible_id'], $vpaInvolvedInProgramMaterials) : [];

        //	JD VI.A.1
        $vpaInvolvedInJailDecongestions = $this->jailDecongestionRepository
            ->getVolunteerIdsByDateRange($minMaxDate, $fieldOfficeId);
        $vpaInvolvedInJailDecongestionsId = $vpaInvolvedInJailDecongestions ?
            array_map(fn ($vpa) => (int) $vpa['person_responsible_id'], $vpaInvolvedInJailDecongestions) : [];

        $volunteerIds = \array_merge(
            $volunteerIds,
            $vpaInvolvedInJailDecongestionsId,
            $vpaInvolvedInProgramMaterialsIds,
            $vpaInvolvedInTechnicalAssistantsId,
            $vpaInvolvedInResourceMobilizationsId,
            $vpasInvolvedInSocialMarketingIds,
            $vpaIdsInvolvedInSupportId,
            $vpaInvolvedInAssociationActivitiesIds,
            $vpaInvolvedInRJActivitiesIds,
            $vpaIdsInRjConductedProcess,
            $vpaIdsInSessions,
        );

        return \array_unique($volunteerIds);
    }

    private function getNumberOfServicesRenderedByVpaDuringQuarter(
        array $minMaxDate,
        int $quarterId,
        array $volunteerIds,
        array $sessionIds,
        int $fieldOfficeId,
    ): int {
        $services = 0;
        //  TC I.A.1.,
        // distinct session_id in resource facilitator session table will serve as count of activity / services rendered
        $sessionsIdFromFacilitators = \array_map(
            fn ($facilitatorSessionId) => $facilitatorSessionId['sessionId'],
            $this->resourceFacilitatorSessionRepository
                ->getDistinctSessionIdsByVolunteerIds($volunteerIds)
        );
        $sessions = \array_intersect($sessionIds, $sessionsIdFromFacilitators);

        //	RJ I.B.1,
        //	RJ I.B.2,
        $conductedProcessIds = \array_map(
            fn ($conductedProcess) => (int) $conductedProcess['rj_conduct_process_id'],
            $this->conductProcessesRepository
                ->findByQuarterAndFieldOffice($quarterId, $fieldOfficeId)
        );
        $conductedProcessFromPersonsInvolved = \array_map(
            fn ($conductedProcessFromPersonInvolved) => $conductedProcessFromPersonInvolved['rjConductedProcessId'],
            $this->conductedProcessPersonsInvolvedRepository
                ->getDistinctConductedProcessIdsByVolunteerIds($volunteerIds)
        );
        $conductedProcess = \array_intersect($conductedProcessIds, $conductedProcessFromPersonsInvolved);

        //	VPA I.C.3,
        $supervisions = $this->volunteerSupervisionsRepository
            ->getServicesRenderedByVolunteersId($quarterId, $volunteerIds);

        //	VPA I.C.4,
        $associations = $this->vpaAssociationRepository->getServicesRenderedByVolunteersId($quarterId, $volunteerIds);

        //	Support I.D.,
        $idSupport = $this->idSupportRepository->getServicesByDateRangeAndVolunteerIds($minMaxDate, $volunteerIds);

        //	SM III.A.1,
        //	SM III.A.2,
        $socialMarketingIds = \array_map(
            fn ($socialMarketing) => (int) $socialMarketing['social_marketing_id'],
            $this->socialMarketingRepository->findByDateRangeWithoutType($minMaxDate, $fieldOfficeId)
        );
        $personInvolvedSocialMarketingsId = \array_map(
            fn ($personInvolvedSM) => $personInvolvedSM['socialMarketingId'],
            $this->socialMarketingPersonInvolvedRepository
                ->getDistinctSocialMarketingsIdByVolunteerIds($volunteerIds)
        );
        $socialMarketings = \array_intersect($socialMarketingIds, $personInvolvedSocialMarketingsId);

        //	SM III.A.3,
        $technicalAssistancesId = \array_map(
            fn ($ta) => (int) $ta['id'],
            $this->technicalAssistanceRepository->findByDateRange($minMaxDate, $fieldOfficeId)
        );
        $personInvolvedTechnicalAssistanceId = \array_map(
            fn ($personInvolvedTA) => $personInvolvedTA['technicalAssistanceId'],
            $this->technicalAssistancePersonsInvolvedRepository
                ->getDistinctTechnicalAssistanceIdByVolunteerIds($volunteerIds)
        );
        $technicalAssistance = \array_intersect($technicalAssistancesId, $personInvolvedTechnicalAssistanceId);

        //	RM IV,
        $resMobIds = \array_map(
            fn ($resMobSB) => (int) $resMobSB['resource_mobilization_id'],
            $this->resourceMobilizationRepository->findByDateRange($minMaxDate, $fieldOfficeId)
        );
        $securedByResMobIds = \array_map(
            fn ($securedBy) => $securedBy['resMobId'],
            $this->resMobSecuredByRepository->getDistinctResMobIdByVolunteerIds($volunteerIds)
        );
        $resMob = \array_intersect($resMobIds, $securedByResMobIds);

        //	PMD V,
        $pmdIds = \array_map(
            fn ($pmd) => (int) $pmd['program_materials_development_id'],
            $this->programMaterialsDevelopmentRepository->findByDateRange($minMaxDate, $fieldOfficeId)
        );
        $personResponsiblePmdIds = \array_map(
            fn ($personResponsiblePmd) => $personResponsiblePmd['pmdId'],
            $this->pmdPersonResponsibleRepository->getDistinctPmdIdByVolunteerIds($volunteerIds)
        );
        $pmds = \array_intersect($pmdIds, $personResponsiblePmdIds);

        //	JD VI.A.1
        $jdids = \array_map(
            fn ($jd) => (int) $jd['jail_decongestion_id'],
            $this->jailDecongestionRepository->findByDateRange($minMaxDate, $fieldOfficeId)
        );
        $personResponsibleJdIds = \array_map(
            fn ($personResponsibleJd) => $personResponsibleJd['jailDecongestionId'],
            $this->jailDecongestionPersonResponsibleRepository->getDistinctJDIdByVolunteerIds($volunteerIds)
        );
        $jds = \array_intersect($jdids, $personResponsibleJdIds);

        $services += \count($supervisions)
            + \count($sessions)
            + \count($conductedProcess)
            + \count($associations)
            + \count($idSupport)
            + \count($socialMarketings)
            + \count($technicalAssistance)
            + \count($resMob)
            + \count($pmds)
            + \count($jds);

        return $services;
    }

    private function createFrontId(
        string $trPosition,
        string $idNo,
        string $fullName,
        string $fieldOfficeName,
        string $regionName,
        string $administrator,
        int $volunteerCount,
        int $index,
    ): string {
        $nameStyleClass = strlen($fullName) > 40 ? 'full-name-small' : 'full-name-large';
        $body = <<<EOD
                    <td width="52%" style="border: 1px dashed #000000;text-align:center;">
                        <span style="text-align: center;font-weight: 400;margin-top: 0;">Republic of the Philippines</span>
                        <br/>
                        <span style="text-align: center;line-height: 1px;font-weight: 400;">Department of Justice</span>
                        <br/>
                        <span style="font-weight: bold;font-size: 10px;">
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            &nbsp;&nbsp;&nbsp;
                            PAROLE AND PROBATION ADMINISTRATION
                        </span>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <h3 style="text-align: left;">
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            ID No. $idNo
                        </h3>
                        <table>
                            <tr>
                                <td class="full-name-container" ><span class="$nameStyleClass">$fullName</span></td>
                            </tr>
                            <tr>
                                <td class="title-container"><span class="title">Volunteer Probation Assistant</span></td>
                            </tr>
                        </table>
                        <h3 style="text-align: center;">$fieldOfficeName</h3>
                        <h3 style="text-align: center">$regionName</h3>
                        <h2 class="admin-name">$administrator</h2>
                        <h3 class="admin-title">Administrator</h3>
                    </td>
        EOD;

        if ('start' == $trPosition) {
            if ($volunteerCount == 1 || (2 == $index && $volunteerCount == 3)) {
                return '<tr>' . $body . '</tr>';
            }

            return '<tr>' . $body;
        }

        return $body . '</tr>';
    }

    private function createBackId(
        string $trPosition,
        string $code,
        string $address,
        string $bloodType,
        string $weight,
        string $height,
        string $emergencyName,
        string $emergencyNumber,
        string $validFrom,
        string $validUntil,
        int $volunteerCount,
        int $index,
    ): string {
        $isEven = $index % 2 == 0;
        $startFiller = $isEven ? '<td width="5%">&nbsp;</td>' : '';
        $endFiller = !$isEven ? '<td width="2%">&nbsp;</td>' : '';
        $body = <<<EOD
            {$endFiller}
            <td width="50%">
                <table class="back-page">
                    <tr>
                        <td width="90%" colspan="3" style="text-align: right;border: none;">
                            &nbsp;
                            <br/>
                            <br/>
                            <br/>
                            <br/>
                            $code
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" class="back-page-title">&nbsp;ADDRESS: </td>
                    </tr>
                    <tr>
                        <td colspan="3" style="height: 60px;">&nbsp;$address</td>
                    </tr>
                    <tr>
                        <td class="back-page-title force-center">BLOOD TYPE</td>
                        <td class="back-page-title force-center">HEIGHT</td>
                        <td class="back-page-title force-center">WEIGHT</td>
                    </tr>
                    <tr>
                        <td style="height: 40px;">$bloodType</td>
                        <td style="height: 40px;">$weight kg</td>
                        <td style="height: 40px;">$height cm</td>
                    </tr>
                    <tr><td colspan="3"></td></tr>
                    <tr><td colspan="3" class="back-page-title">&nbsp;IN CASE OF EMERGENCY, NOTIFY:</td></tr>
                    <tr><td colspan="3" style="height: 60px;">&nbsp;$emergencyName</td></tr>
                    <tr>
                        <td class="back-page-title">&nbsp;TEL. NO.:</td>
                        <td colspan="2">&nbsp;$emergencyNumber</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="no-border">
                            * This card is non-transferable and must be worn at all times when supervising clients.
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" class="no-border">
                            * Heavy penalty for unlawful use pursuant to Article 177 and 179, RPC
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" class="no-border"></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="no-border">_________________________________</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="no-border">Signature</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="no-border" style="font-weight: bold;">This ID valid from: $validFrom</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="no-border" style="font-weight: bold;">
                            until: $validUntil
                            <div></div>
                        </td>
                    </tr>
                </table>
            </td>
            {$startFiller}
        EOD;

        if ('start' == $trPosition) {
            if ($volunteerCount == 1 || (2 == $index && $volunteerCount == 3)) {
                return '<tr>' . $body . '</tr>';
            }

            return '<tr>' . $body;
        }

        return $body . '</tr>';
    }
}
