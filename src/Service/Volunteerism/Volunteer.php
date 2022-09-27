<?php

namespace App\Service\Volunteerism;

use App\Common\AppDateHelper;
use App\Common\AppFormatter;
use App\Entity\Quarters;
use App\Enum\AuditTrailActions;
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
use App\Repository\SocialMarketingRepository;
use App\Repository\RJRelatedActivitiesRepository;
use App\Repository\VpaAssociationInitiatedActivitiesRepository;
use App\Repository\VolunteerOperationsRepository;
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

    const APPOINTED = 'APPOINTED';
    const REAPPOINTED = 'REAPPOINTED';

    public function __construct(
        private ValidatorInterface                          $validator,
        private AppFormatter                                $appFormatter,
        private VolunteerRepository                         $repository,
        private QuartersRepository                          $quartersRepository,
        private AppDateHelper                               $appDateHelper,
        private RegionsRepository                           $regionsRepository,
        private CivilStatusRepository                       $civilStatusRepository,
        private ReligionRepository                          $religionRepository,
        private OccupationRepository                        $occupationRepository,
        private EducationBackgroundRepository               $educationBackgroundRepository,
        private VolunteerOperationsRepository               $volunteerOperationsRepository,
        private FieldOfficesRepository                       $fieldOfficesRepository,
        private VolunteerSupervisionsRepository             $volunteerSupervisionsRepository,
        private ResourceFacilitatorSessionRepository        $resourceFacilitatorSessionRepository,
        private SocialMarketingRepository                   $socialMarketingRepository,
        private RJRelatedActivitiesRepository               $rjRelatedActivitiesRepository,
        private VpaAssociationInitiatedActivitiesRepository $vpaAssociationRepository,
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
                return $this->appFormatter->formatResponse(ResponseEnum::VALIDATING_FAILED, null, $this->appFormatter->formatErrors($errors));
            }

            $id = $this->repository->create($volunteerData);

            if ($id == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['app' => 'Volunteer already exist']);
            }

            $this->auditTrail->log(AuditTrailActions::CREATE, $volunteerData->jsonSerialize(), $this->shortName, $id);

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

            $this->auditTrail->log(AuditTrailActions::DELETE, [], $this->shortName, $id);

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

            $this->auditTrail->log(AuditTrailActions::UPDATE, $volunteerData->jsonSerialize(), $this->shortName, $id);

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

            $this->auditTrail->log(AuditTrailActions::UPDATE, $data, $this->shortName, $data['id']);

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

        $sessions = $this->quartersRepository->getSessionDataByQuarterAndFieldOfficeId($quarterId, $fieldOfficeId);
        $sessionIds = array_map(fn($session) => intval($session['session_id']), $sessions);

        $quarterYear = intval($quarterData->getYear());
        $startOfQuarterVpa = $this->getStartOfQuarterVpa($quarterData, $fieldOfficeId);
        $newAppointed = $this->getMonitoringByStatus($quarterYear, self::APPOINTED, $months, $startOfQuarterVpa);
        $reappointed = $this->getMonitoringByStatus($quarterYear, self::REAPPOINTED, $months, $startOfQuarterVpa);
        $dropped = $this->getDroppedVolunteers($quarterData, $fieldOfficeId);
        $totalNumberOfVpa = (count($startOfQuarterVpa) + $newAppointed) - $dropped;
        $inactive = $this->repository->findInactiveVolunteersByFieldOfficeAndMonthRangeV2(
            $fieldOfficeId,
            intval($quarterData->getYear()),
            $months,
            $activeVolunteers
        );
        $totalActiveVpa = $totalNumberOfVpa - count($inactive);
        $percentOfVpaMobilized = $totalNumberOfVpa > 0 ? ($totalActiveVpa / $totalNumberOfVpa) * 100 : 0;
        $vpaSupervisingClients = $this->volunteerSupervisionsRepository->findVpaInvolveByQuarter($quarterId, $fieldOfficeId);
        $noOfVpaSupervisingClients = count($vpaSupervisingClients);
        $noOfVpaSupervisingClientsPercentage = $totalActiveVpa > 0 ? ($noOfVpaSupervisingClients / $totalActiveVpa) * 100 : 0;

        // Column 10 = 1.A.1 + 1.B.2 + 1.C.4 + 3.A.1
        // Table 1.A.1
        $vpaActingAsResourceIndividualsInSessions = $this->resourceFacilitatorSessionRepository->getDistinctVolunteerIdsBySessionIds($sessionIds);
        $vpaActingAsResourceIndividualsInSessionsIds = $vpaActingAsResourceIndividualsInSessions ? array_map(fn($vpa) => $vpa['resourceFacilitatorId'], $vpaActingAsResourceIndividualsInSessions) : [];
        
        // Table 1.B.2
        $vpasInvolvedInRJActivities = $this->rjRelatedActivitiesRepository->getVolunteerIdsByDateRange($quarterId, $fieldOfficeId);
        $vpasInvolvedInRJActivitiesIds = $vpasInvolvedInRJActivities ? array_map(fn($vpa) => $vpa['volunteer_id'], $vpasInvolvedInRJActivities) : [];

        // Table 1.C.4
        $vpasInvolvedInAssociationActivities = $this->vpaAssociationRepository->getVolunteerIdsByDateRange($quarterId, $fieldOfficeId);
        $vpasInvolvedInAssociationActivitiesIds = $vpasInvolvedInAssociationActivities ? array_map(fn($vpa) => $vpa['volunteer_id'], $vpasInvolvedInAssociationActivities) : [];

        // Table 3.A.1
        $minMaxDate = $this->quartersRepository->getQuarterMinMaxDate($quarterData);
        $vpasInvolvedInSocialMarketing = $this->socialMarketingRepository->getVolunteerIdsByDateRange($minMaxDate, $fieldOfficeId, 'INFORMATION_DISSEMINATION');
        $vpasInvolvedInSocialMarketingIds = $vpasInvolvedInSocialMarketing ? array_map(fn($vpa) => $vpa['volunteer_id'], $vpasInvolvedInSocialMarketing) : [];
        
        $vpaActingAsResourceIndividuals = array_unique(array_merge($vpaActingAsResourceIndividualsInSessionsIds, $vpasInvolvedInRJActivitiesIds, $vpasInvolvedInAssociationActivitiesIds, $vpasInvolvedInSocialMarketingIds));
        $noOfVpaActingAsResourceIndividuals = count($vpaActingAsResourceIndividuals);
        $noOfVpaActingAsResourceIndividualsPercentage = $totalActiveVpa > 0 ? ($noOfVpaActingAsResourceIndividuals / $totalActiveVpa) * 100 : 0;

        $vpaActingBothSupervisingAndResourceIndividual = count($this->getVpaActingBothSupervisingAndResourceIndividual($vpaSupervisingClients, $vpaActingAsResourceIndividuals));
        $percentageOfVpaActingBothSupervisingAndResourceIndividual = $totalActiveVpa > 0 ?
            ($vpaActingBothSupervisingAndResourceIndividual / $totalActiveVpa) * 100 : 0;
        $totalNumberOfClientsSupervised = count($this->volunteerSupervisionsRepository->findClientsSupervisedByQuarter($quarterId, $fieldOfficeId));
        $noOfServicesRenderedByVpa = count($this->volunteerSupervisionsRepository->findServicesRenderedByQuarter($quarterId, $fieldOfficeId));
        $noOfServicesRenderedByVpaPercentage = $totalActiveVpa > 0 ? ($noOfServicesRenderedByVpa / $totalActiveVpa) * 100 : 0;

        return [
            'start_of_quarter_vpa' => count($startOfQuarterVpa),
            'new_appointed' => $newAppointed,
            'reappointed' => $reappointed,
            'dropped' => $dropped,
            'total_number_of_vpa_during_quarter' => $totalNumberOfVpa,
            'inactive' => count($inactive),
            'total_active_vpa' => $totalActiveVpa,
            'percentage_of_vpa_mobilized' => $percentOfVpaMobilized,
            'no_of_vpa_supervising_clients' => $noOfVpaSupervisingClients,
            'no_of_vpa_supervising_clients_percentage' => $noOfVpaSupervisingClientsPercentage,
            'no_of_vpa_acting_as_resource_individuals' => $noOfVpaActingAsResourceIndividuals,
            'no_of_vpa_acting_as_resource_individuals_percentage' => $noOfVpaActingAsResourceIndividualsPercentage,
            'vpa_acting_both_supervising_and_resource_individual' => $vpaActingBothSupervisingAndResourceIndividual,
            'percentage_of_vpa_acting_both_supervising_and_resource_individual' => $percentageOfVpaActingBothSupervisingAndResourceIndividual,
            'total_number_of_clients_supervised' => $totalNumberOfClientsSupervised,
            'no_of_services_rendered_by_vpa' => $noOfServicesRenderedByVpa,
            'no_of_services_rendered_by_vpa_percentage' => $noOfServicesRenderedByVpaPercentage,
        ];
    }

    public function getCertificate(array $data): string
    {
        $volunteer = $this->repository->find($data['volunteer_id']);
        $address = $volunteer->getPresentAddress();
        $fullName = $volunteer->getFirstName() . ' ' . $volunteer->getMiddleName() . ' ' . $volunteer->getLastName();
        $fieldOffice = $this->fieldOfficesRepository->find($volunteer->getFieldOfficeId());
        $fieldOfficeName = $fieldOffice->getName();
        $dateOfAppointment = $volunteer->getDateAppointed()->format('F d, Y');
        $region = $this->regionsRepository->find($fieldOffice->getRegionId());
        $regionName = $region->getName();
        $code = $this->systemCodeSettings->getByName('vpa_certificate_report_code');
        $administrator = $this->systemCodeSettings->getByName('oic_administrator');

        $pdf = new TCPDF();
        $pdf->setCreator(PDF_CREATOR);
        $pdf->setAuthor('PPA');
        $pdf->setTitle('Testing');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->startPage();
        $logo = dirname(__DIR__ ) . '/../../assets/ppa.png';
        $heading = <<<EOD
            <h3 style="text-align: right;">$code</h3>
            <h3 style="text-align: center;line-height: 5px;">Republic of the Philippines</h3>
            <h3 style="text-align: center;line-height: 5px;">Department of Justice</h3>
            <h2 style="text-align: center;line-height: 5px;">PAROLE AND PROBATION ADMINISTRATION</h2>
            <h5 style="text-align: center;line-height: 5px;">DOJ Agencies Building</h5>
            <h5 style="text-align: center;line-height: 5px;">NIA Road corner East Avenue, Diliman</h5>
            <h5 style="text-align: center;line-height: 5px;">110 Quezon City</h5>
        EOD;

        $pdf->writeHTMLCell(0, 0, '', '', $heading);
        $pdf->Image($logo,  85, 75, 40, 40, '', '', 'T', false, 300, '', false, false, 1, false, false, false);
        $body = <<<EOD
            <div>
                <h2 style="text-align: center;"><i>Certificate of Appointment</i></h2>
                <h2 style="text-align: center;font-size: 15px;font-weight: normal">$fullName</h2>
                <h2 style="text-align: center"><i>of</i></h2>
                <h2 style="text-align: center;font-size: 15px;font-weight: normal">$address</div>
                <h2 style="text-align: center">Department</h2>
                <h4 style="text-align: center">is hereby appointed as <span style="font-size: 13px">VOLUNTEER PROBATION ASSISTANT</span> of the</h4>
                <h3 style="text-align: center;line-height: 5px;"><i>$fieldOfficeName</i></h3>
                <h3 style="text-align: center;line-height: 5px;"><i>$regionName</i></h3>
                <div></div>
                <h2 style="text-align: center">$dateOfAppointment</h2>
                <div></div>
                <div></div>
                <h2 style="text-align: center;line-height: 5px;">$administrator</h2>
                <h2 style="text-align: center;line-height: 5px;">Administrator</h2>
            </div>
        EOD;

        $pdf->SetXY(110, 200);
        $pdf->setMargins(50, 0, 0);
        $pdf->writeHTMLCell(0, 0, 0, 130, $body);
        $pdf->endPage();

        $this->auditTrail->log(AuditTrailActions::DOWNLOAD, $data, $this->shortName, $data['volunteer_id']);

        return $pdf->Output('mark.pdf', 'E');
    }

    public function getId(array $data): string
    {
        $volunteer = $this->repository->find($data['volunteer_id']);
        $idNo = $volunteer->getVolunteerId();
        $fullName = $volunteer->getFirstName() . ' ' . $volunteer->getMiddleName() . ' ' . $volunteer->getLastName();
        $fieldOffice = $this->fieldOfficesRepository->find($volunteer->getFieldOfficeId());
        $fieldOfficeName = $fieldOffice->getName();
        $region = $this->regionsRepository->find($fieldOffice->getRegionId());
        $regionName = $region->getName();
        $code = $this->systemCodeSettings->getByName('vpa_certificate_report_code');
        $address = $volunteer->getPresentAddress();
        $bloodType = $volunteer->getBloodType();
        $weight = $volunteer->getWeight();
        $height = $volunteer->getHeight();
        $emergencyName = $volunteer->getEmergencyName();
        $emergencyNumber = $volunteer->getEmergencyNumber();
        $administrator = $this->systemCodeSettings->getByName('oic_administrator');

        $pdf = new TCPDF();
        $pdf->setCreator(PDF_CREATOR);
        $pdf->setAuthor('PPA');
        $pdf->setTitle('Testing');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->startPage();
        $logo = dirname(__DIR__ ) . '/../../assets/ppa.png';
        $picture = dirname(__DIR__ ) . '/../../assets/placeholder-1x1.gif';

        $pdf->writeHTMLCell(0, 0, '', '');
        $pdf->Image($logo,  2.5, 12.5, 15, 15, '', '', 'T', false, 300, '', false, false, 1, false, false, false);
        $pdf->Image($picture,  37.5, 40, 25.4, 25.4, '', '', 'T', false, 300, '', false, false, 1, false, false, false);
        $body = <<<EOD
            <style>
                table.back-page {
                    border-collapse: collapse;
                }
                table.back-page > tr {}        
                table.back-page > tr > td {
                    border: 1px solid #000000;
                    text-align: center;
                }
                table.back-page > tr > td.back-page-title {
                    text-align: left !important;
                    font-size: 12px;
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
            <table width="100%" cellpadding="0" border="0">
                <tr>
                    <td width="50%" style="border: 1px solid #000000;">
                        <h4 style="text-align: center">Republic of the Philippines</h4>
                        <h4 style="text-align: center;line-height: 1px">Department of Justice</h4>
                        <h3 style="text-align: center">PAROLE AND PROBATION ADMINISTRATION</h3>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <h3>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            ID No.: $idNo
                        </h3>
                        <h2 style="text-align: center;font-size: 15px;font-weight: normal;background-color: #f7ef4d;">$fullName</h2>
                        <h2 style="text-align: center;font-weight: bold;background-color: #e9ad63;color: #fff;">Volunteer Probation Assistant</h2>
                        <h3 style="text-align: center;"><i>$fieldOfficeName</i></h3>
                        <h3 style="text-align: center"><i>$regionName</i></h3>
                        <div></div>
                        <h2 style="text-align: center;line-height: 5px;">$administrator</h2>
                        <h3 style="text-align: center;font-weight: normal;">Administrator</h3>
                    </td>
                    <td width="50%">
                        <table class="back-page">
                            <tr>
                                <td colspan="3" style="text-align: right;border: none;">$code</td>
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
                                <td style="height: 50px;">$bloodType</td>
                                <td style="height: 50px;">$weight</td>
                                <td style="height: 50px;">$height</td>
                            </tr>
                            <tr><td colspan="3"></td></tr>
                            <tr><td colspan="3" class="back-page-title">&nbsp;IN CASE OF EMERGENCY, NOTIFY:</td></tr>
                            <tr><td colspan="3" style="height: 60px;">&nbsp;$emergencyName</td></tr>
                            <tr>
                                <td class="back-page-title">&nbsp;TEL. NO.:</td>
                                <td colspan="2">&nbsp;$emergencyNumber</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="no-border">* This card is non-transferable and must be worn at all times when supervising clients.</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="no-border">* Heavy penalty for unlawful use pursuant to Article 177 and 179, RPC</td>
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
                                <td colspan="3" class="no-border force-left" style="font-weight: bold;">This ID valid from:</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="no-border force-left" style="font-weight: bold;">until:</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr><td width="100%" colspan="2"></td></tr>
            </table>
        EOD;

        $pdf->writeHTMLCell(0, 0, 0, 0, $body);
        $pdf->endPage();

        $this->auditTrail->log(AuditTrailActions::DOWNLOAD, $data, $this->shortName, $data['volunteer_id']);

        return $pdf->Output('mark.pdf', 'E');
    }

    /**
     * @param Quarters|null $quarterData
     * @param int $fieldOfficeId
     * @return int[]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function getStartOfQuarterVpa(?Quarters $quarterData, int $fieldOfficeId): array
    {
        $previousQuarter = $this->quartersRepository->fetchPreviousQuarterByNameAndYear($quarterData->getName(), intval($quarterData->getYear()));

        if ($previousQuarter === null) {
            return [];
        }
        $previousMonths = $this->appDateHelper->getMonthsByQuarterString($previousQuarter->getName());
        $currentMonths = $this->appDateHelper->getMonthsByQuarterString($quarterData->getName());
        $previousActiveVolunteers = $this->repository
            ->findByFieldOfficeAndMonthRange($fieldOfficeId, intval($previousQuarter->getYear()), $previousMonths);
        $reappointedVolunteersId = $this->getVolunteersIdByStatus(self::REAPPOINTED, intval($quarterData->getYear()), $currentMonths);

        $results = [];
        foreach ($previousActiveVolunteers as $previousActiveVolunteer) {
            if (in_array($previousActiveVolunteer->getVolunteerId(), $reappointedVolunteersId)) {
                continue;
            }

            $results[] = $previousActiveVolunteer->getVolunteerId();
        }

        return $results;
    }

    /**
     * @param int[] $months
     * @param int[] $startOfQuarterVpa
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function getMonitoringByStatus(
        int $year,
        string $status,
        array $months,
        array $startOfQuarterVpa,
    ): int {
        $newVolunteersId = $this->getVolunteersIdByStatus(
            $status,
            $year,
            $months
        );

        $count = 0;
        foreach ($newVolunteersId as $newVolunteerId) {
            if (in_array($newVolunteerId, $startOfQuarterVpa)) {
                continue;
            }
            $count++;
        }

        return $count;
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
     * @return int[]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function getVolunteersIdByStatus(
        string $status,
        int $year,
        array $months
    ): array {
        $volunteerOperations = $this->volunteerOperationsRepository->findVolunteerIdsByMonthRange($year, $months, $status);

        return array_map(fn($volunteerOperation) => intval($volunteerOperation['volunteer_id']), $volunteerOperations);
    }

    private function getVpaActingBothSupervisingAndResourceIndividual(
        array $vpaSupervisingClients,
        array $vpaActingAsResourceIndividuals,
    ): array {
        $result = [];

        foreach ($vpaActingAsResourceIndividuals as $actingAsResourceIndividual) {
            if (! in_array($actingAsResourceIndividual, $vpaSupervisingClients)) {
                continue;
            }

            $result[] = $actingAsResourceIndividual;
        }

        return $result;
    }

    private function getDroppedVolunteers(
        Quarters $quarterData,
        int $fieldOfficeId
    ): int {
        $minMaxDate = $this->quartersRepository->getQuarterMinMaxDate($quarterData);

        return count($this->repository->getDroppedVolunteer($minMaxDate, $fieldOfficeId));
    }
}