<?php

declare(strict_types=1);

namespace App\Service\TherapeuticCommunity;

use App\Common\AppDateHelper;
use App\Common\AppFormatter;
use App\Common\AppHydrator;
use App\Enum\AuditTrailActions;
use App\Enum\Response as ResponseEnum;
use App\Model\ClientSessions as ClientSessionModel;
use App\Model\Sessions as SessionsModel;
use App\Repository\ClientSessionsRepository;
use App\Repository\ClientsRepository;
use App\Repository\ClientTypesRepository;
use App\Repository\FieldOfficesRepository;
use App\Repository\QuartersRepository;
use App\Repository\ResourceFacilitatorSessionRepository;
use App\Repository\SessionsRepository;
use App\Repository\TclpComputationFormRepository;
use App\Repository\UserDetailsRepository;
use App\Service\FieldOfficeService;
use App\Service\RegionService;
use App\Service\System\AuditTrail;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Exception;
use Psr\Cache\CacheException;
use ReflectionClass;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Sessions implements SessionsInterface
{
    private string $shortName;

    public function __construct(
        private AppFormatter                         $appFormatter,
        private SessionsRepository                   $repository,
        private ValidatorInterface                   $validator,
        private AppDateHelper                        $appDateHelper,
        private QuartersRepository                   $quartersRepository,
        private ClientSessionsRepository             $clientSessionsRepository,
        private ClientsRepository                    $clientsRepository,
        private ClientTypesRepository                $clientTypesRepository,
        private ResourceFacilitatorSessionRepository $resourceFacilitatorSessionRepository,
        private AuditTrail                           $auditTrail,
        private FieldOfficesRepository               $fieldOfficesRepository,
        private RegionService                        $regionService,
        private FieldOfficeService                   $fieldOfficeService,
        private UserDetailsRepository                $userDetailsRepository,
        private AppHydrator                          $hydrator,
        private TclpComputationFormRepository        $tclpComputationFormRepository,
    )
    {
        $class = new ReflectionClass($this);
        $this->shortName = $class->getShortName();
    }

    public function create(SessionsModel $sessionData): array
    {
        try {
            $errors = $this->validator->validate($sessionData);

            if (count($errors) > 0) {
                return $this->appFormatter->formatResponse(
                    ResponseEnum::VALIDATING_FAILED,
                    null,
                    $this->appFormatter->formatErrors($errors)
                );
            }

            $id = $this->repository->create($sessionData);

            if ($id == null) {
                return $this->appFormatter->formatResponse(
                    ResponseEnum::CREATING_FAILED,
                    null,
                    ['app' => 'Session already exist.']
                );
            }

            $this->auditTrail->log(AuditTrailActions::CREATE, $sessionData->jsonSerialize(), $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_SUCCESS, ['id' => $id]);
        } catch (\Psr\Cache\InvalidArgumentException | InvalidArgumentException | Exception $e) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::CREATING_FAILED,
                null,
                ['app' => $e->getMessage()]
            );
        }
    }

    public function createWithClientsAndFacilitators(SessionsModel $sessionData): array
    {
        try {
            $errors = $this->validator->validate($sessionData);

            if (count($errors) > 0) {
                return $this->appFormatter->formatResponse(
                    ResponseEnum::VALIDATING_FAILED,
                    null,
                    $this->appFormatter->formatErrors($errors)
                );
            }

            $id = $this->repository->createWithClientsAndFacilitators($sessionData);

            if ($id == null) {
                return $this->appFormatter->formatResponse(
                    ResponseEnum::CREATING_FAILED,
                    null,
                    ['app' => 'Session already exist.']
                );
            }

            $this->auditTrail->log(AuditTrailActions::CREATE, $sessionData->jsonSerialize(), $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_SUCCESS, ['id' => $id]);
        } catch (\Psr\Cache\InvalidArgumentException | InvalidArgumentException | Exception $e) {
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
            $sessions = $this->repository->list();

            if ($sessions == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $sessions);
        } catch (CacheException|\Psr\Cache\InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_FAILED,
                null,
                ['cache' => $exception->getMessage()]
            );
        }
    }

    public function getAllWithClientsAndFacilitators(): array
    {
        try {
            $sessions = $this->repository->listWithClientsAndFacilitators();

            if ($sessions == null) { 
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $sessionsCreatedByIds = array_unique(array_map(fn($session) => intval($session['created_by']), $sessions));
            $createdBys = $this->userDetailsRepository->getCreatedBys($sessionsCreatedByIds);
            
            foreach($sessions as $index=>$session) {
                $sessions[$index]['created_by'] = $createdBys[intval($session['created_by'])];
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $sessions);
        } catch (CacheException|\Psr\Cache\InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_FAILED,
                null,
                ['cache' => $exception->getMessage()]
            );
        }
    }

    public function deleteById(int $id): array
    {
        try {
            $isDeleted = $this->repository->delete($id);

            if (!$isDeleted) {
                return $this->appFormatter->formatResponse(
                    ResponseEnum::DELETING_FAILED,
                    null,
                    ['app' => ResponseEnum::NO_DATA]
                );
            }

            $this->auditTrail->log(AuditTrailActions::DELETE, [], $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_SUCCESS, null);
        } catch (\Psr\Cache\InvalidArgumentException | \Doctrine\DBAL\Driver\Exception|Exception $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::DELETING_FAILED,
                null,
                ['app' => $exception->getMessage()]
            );
        }
    }

    public function updateById(int $id, SessionsModel $sessionData): array
    {
        try {
            $isUpdated = $this->repository->update($id, $sessionData);

            if ($isUpdated !== ResponseEnum::OK) {
                return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['app' => $isUpdated]);
            }

            $this->auditTrail->log(AuditTrailActions::UPDATE, $sessionData->jsonSerialize(), $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_SUCCESS, null);
        } catch (\Psr\Cache\InvalidArgumentException|InvalidArgumentException|Exception $e) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::UPDATING_FAILED,
                null,
                ['app' => $e->getMessage()]
            );
        }
    }

    public function updateByIdWithClientAndFacilitators(int $id, SessionsModel $sessionData): array
    {
        try {
            $isUpdated = $this->repository->updateWithClientAndFacilitators($id, $sessionData);

            if ($isUpdated !== ResponseEnum::OK) {
                return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['app' => $isUpdated]);
            }

            $this->auditTrail->log(AuditTrailActions::UPDATE, $sessionData->jsonSerialize(), $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_SUCCESS, null);
        } catch (\Doctrine\DBAL\Driver\Exception $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['orm' => $exception->getMessage()]);
        } catch (InvalidArgumentException|Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['app' => $e->getMessage()]);
        } catch (\Psr\Cache\InvalidArgumentException $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['cache' => $e->getMessage()]);
        }
    }

    public function getById(int $id): array
    {
        try {
            $session = $this->repository->fetchById($id);

            if (!$session) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $session);
        } catch (\Psr\Cache\InvalidArgumentException|CacheException $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['cache' => $e->getMessage()]);
        }
    }

    public function getPaginated(int $page, int $pageSize, int $fieldOfficeId): array
    {
        try {
            $sessions = $this->repository->paginated($page, $pageSize, $fieldOfficeId);

            if ($sessions == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $return = [];
            $userIds = array_unique(array_map(fn($session) => intval($session['created_by']), $sessions['items']));
            $createdBys = $this->userDetailsRepository->getCreatedBys($userIds);

            foreach ($sessions['items'] as $session) {
                $session['createdBy'] = $createdBys[$session['created_by']];

                $return[] = $session;
            }

            $sessions['items'] = $return;

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $sessions);
        } catch (CacheException|\Psr\Cache\InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function getTCA1Part1(\App\Entity\Quarters $quarterData, int $fieldOfficeId): array
    {
        try {
            $sessions = $this->repository->fetchTCA1Part1($fieldOfficeId, $quarterData);

            return $sessions ?? [];
        } catch (CacheException $e) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_FAILED,
                null,
                ['cache' => $e->getMessage()]
            );
        }
    }

    public function getTCA1Part2(\App\Entity\Quarters $quarterData, int $fieldOfficeId): array
    {
        try {
            $quarterData = $this->repository->fetchTCA1Part2($fieldOfficeId, $quarterData);

            return array_values($quarterData ?? []);
        } catch (CacheException $e) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_FAILED,
                null,
                ['cache' => $e->getMessage()]
            );
        }
    }

    public function getTCIA2(int $quarterId, int $fieldOfficeId, string $role): array
    {
        /**
         * CRITERIA
         * Per Client in current quarter
         * Session activities from selected quarter and previous quarter in the same year
         */
        try {
            $sessions = $this->repository->fetchTCIA2($quarterId, $fieldOfficeId, strtoupper($role));

            if ($sessions == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $rows = [];

            foreach ($sessions as $session) {
                $month = explode('-', $session['date'])[1];
                $quarter = $this->appDateHelper->getQuarterByMonth(intval($month));

                $middleInitial = $session['middle_name'] != null ? substr($session['middle_name'], 0, 1) : '';
                $fullName = $session['last_name'] . '_' . $session['first_name'] . '_' . $middleInitial;
                $monthInitial = $this->appDateHelper->getFirstLetterOfMonthFromDateString($session['date']);
                // The only criteria needed is the fullName and client type (for sorting)
                // Because if we add phase and quarter then whenever there is new quarter or phase with the same name
                // it will produce another row with same name
                $rowIdentifier = $session['client_type'] . '_' . $fullName;
                $monthIdentifier = $quarter . '_' . $monthInitial;

                if (!isset($rows[$rowIdentifier])) {
                    $session['month_quarter'] = [];
                    $rows[$rowIdentifier] = $session;
                }

                // override based on the latest record, key is the order by from the query
                $rows[$rowIdentifier]['phase'] = $session['phase'];
                $rows[$rowIdentifier]['remarks'] = $session['remarks'];
                $rows[$rowIdentifier]['other_remarks'] = $session['other_remarks'];
                $rows[$rowIdentifier]['fsi'] = false;

                if ($session['fsi']) {
                    $rows[$rowIdentifier]['fsi'] = true;
                }

                if (null !== $session['remarks']) {
                    continue;
                }

                $rows[$rowIdentifier]["month_quarter"][] = $monthIdentifier;
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $rows);
        } catch (\Doctrine\DBAL\Exception $e) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_FAILED,
                null,
                ['orm' => $e->getMessage()]
            );
        }
    }

    public function getTC7(int $quarterId, int $fieldOfficeId): array
    {
        try {
            $currentQuarter = $this->quartersRepository->find($quarterId);

            if ($currentQuarter == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $initialValues = $this->getInitialValues();
            $quarterInitialValues = $this->getCurrentQuarterInitialValues($currentQuarter);
            $less = $this->getLess($currentQuarter, $fieldOfficeId);
            $clientsAttendingTc = $this->getClientsAttendingTC($currentQuarter, $fieldOfficeId);

            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_SUCCESS,
                [
                    'totalSupervisionCaseloadEndOfQuarter' => $initialValues,
                    'activeSupervisions' => $initialValues,
                    'activeCourtesySupervision' => $initialValues,
                    'totalNewSuperVisionReferrals' => $initialValues,
                    'superVisionReferrals' => $quarterInitialValues,
                    'totalNewCourtesySupervisionReferrals' => $initialValues,
                    'courtesySupervisionReferrals' => $quarterInitialValues,
                    'totalSupervisionCasesDropped' => $initialValues,
                    'supervisionCasesDropped' => $quarterInitialValues,
                    'totalSupervisionCasesHandled' => $initialValues,
                    'less' => $less,
                    'totalLess' => $this->getTotalLess($less),
                    'totalAdjustedSupervisionCaseLoad' => $initialValues,
                    'clientsAttendingTC' => $clientsAttendingTc,
                    'percentageOfClientsAttendingTC' => $this->getPercentageOfClientsAttendingTc($clientsAttendingTc),
                ]
            );
        } catch (Exception $e) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_FAILED,
                null,
                ['app' => $e->getMessage()]
            );
        }
    }

    public function getSavedTC7(int $quarterId, int $fieldOfficeId): array
    {
        try {
            $saved = $this->tclpComputationFormRepository->findBy([
                'quarter' => $quarterId,
                'fieldOffice' => $fieldOfficeId,
            ]);

            if (null == $saved) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_SUCCESS,
                $saved
            );
        } catch (Exception $e) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_FAILED,
                null,
                ['app' => $e->getMessage()]
            );
        }
    }    

    public function duplicateWithSessionAndFacilitator(int $id): array
    {
        try {
            $newId = $this->repository->duplicate($id);
            $this->clientSessionsRepository->duplicate($id, intval($newId));
            $this->resourceFacilitatorSessionRepository->duplicate($id, intval($newId));

            return $this->appFormatter->formatResponse("Duplicating Successful", []);
        } catch (\Doctrine\DBAL\Driver\Exception|\Doctrine\DBAL\Exception $e) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::UPDATING_FAILED,
                null,
                ['cache' => $e->getMessage()]
            );
        }
    }


    public function getRegionalTC7(int $quarterId, int $regionId): array
    {
        try {
            $currentQuarter = $this->quartersRepository->find($quarterId);

            if ($currentQuarter == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $results = [];
            $tempResults = [];
            $initialValues = $this->getRegionalTC7ClientRemarksInitialValues();
            $sessionIds = $this->repository->findSessionsIdsByQuarter($currentQuarter);

            if (\count($sessionIds) == 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $fieldOfficesId = $this->repository
                ->findFieldOfficeIdsInSessionByQuarterAndRegionId($currentQuarter, $regionId);

            foreach ($fieldOfficesId as $fieldOfficeId) {
                $tempResults[$fieldOfficeId] = $initialValues;
            }

            $sessionClients = $this->clientSessionsRepository
                ->findAbsenteesRemarksIdAndFieldOfficeIdBySessionId($sessionIds);

            foreach ($sessionClients as $sessionClient) {
                $fieldOfficeId = $sessionClient['field_office_id'];
                $clientRemarksId = (string) $sessionClient['client_remarks_id'];

                if (! isset($tempResults[$fieldOfficeId][$clientRemarksId])) {
                    // Note: Log the error saying no matching field office id
                    // and client remarks id -- but this shouldn't happen
                    continue;
                }

                $tempResults[$fieldOfficeId][$clientRemarksId]++;
            }

            $fieldOfficesName = $this->fieldOfficesRepository->findNamesByFieldOfficeIds($fieldOfficesId);

            foreach ($tempResults as $fieldOfficeId => $tempResult) {
                $fieldOfficeName = $fieldOfficesName[$fieldOfficeId];
                $subtotal = 0;
                // to be filled out by frontend columns
                $result = $this->generateRegionalTC7RowInitialValue();

                foreach ($tempResult as $clientRemarksId => $value) {
                    $clientRemarksId = (int) $clientRemarksId;
                    $equivalentColumnNumber = $this
                        ->getRegionalTC7ClientRemarksIdColumnNumberEquivalent($clientRemarksId);

                    if (3 === $clientRemarksId || 4 === $clientRemarksId || 5 === $clientRemarksId) {
                        $result[11] += $value;
                    }

                    $subtotal += $value;
                    $result[$equivalentColumnNumber] += $value;
                }

                $result[14] = $subtotal;
                $results[$fieldOfficeName] = $result;
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $results);
        } catch (\Doctrine\DBAL\Exception $e) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_FAILED,
                null,
                ['orm' => $e->getMessage()]
            );
        }
    }
    public function getNationalTC7(int $quarterId): array
    {
        $currentQuarter = $this->quartersRepository->find($quarterId);

        if ($currentQuarter == null) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        $results = [];
        $tempResults = [];
        $initialValues = $this->getRegionalTC7ClientRemarksInitialValues();
        $sessionIds = $this->repository->findSessionsIdsByQuarter($currentQuarter);

        if (\count($sessionIds) == 0) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        $fieldOfficesId = $this->repository->findFieldOfficeIdsInSessionByQuarter($currentQuarter);
        $regionIdsWithFieldOffice = $this->getRegionIdsByFieldOfficeIds($fieldOfficesId);
        $regionIds = array_keys($regionIdsWithFieldOffice);
        $regionNamesWithId = $this->getRegionNames($regionIds);

        foreach ($fieldOfficesId as $fieldOfficeId) {
            $tempResults[$fieldOfficeId] = $initialValues;
        }

        $sessionClients = $this->clientSessionsRepository->findAbsenteesRemarksIdAndFieldOfficeIdBySessionId($sessionIds);

        // TODO: group by region
        foreach ($sessionClients as $sessionClient) {
            $fieldOfficeId = $sessionClient['field_office_id'];
            $clientRemarksId = (string) $sessionClient['client_remarks_id'];

            if (! isset($tempResults[$fieldOfficeId][$clientRemarksId])) {
                // Note: Log the error saying no matching field office id and client remarks id -- but this shouldn't happen
                continue;
            }

            $tempResults[$fieldOfficeId][$clientRemarksId]++;
        }

        foreach ($tempResults as $fieldOfficeId=>$tempResult) {
            $regionName = $this->extractRegionName($regionNamesWithId, $regionIdsWithFieldOffice, $fieldOfficeId);
            $subtotal = 0;
            $result = $this->generateRegionalTC7RowInitialValue();
            if (! isset($results[$regionName])) {
                $results[$regionName] = null;
            }

            foreach ($tempResult as $clientRemarksId=>$value) {
                $clientRemarksId = (int) $clientRemarksId;
                $equivalentColumnNumber = $this->getRegionalTC7ClientRemarksIdColumnNumberEquivalent($clientRemarksId);

                if (3 === $clientRemarksId || 4 === $clientRemarksId || 5 === $clientRemarksId) {
                    $result[11] += $value;
                }

                $subtotal += $value;
                $result[$equivalentColumnNumber] += $value;
            }

            $result[14] = $subtotal;

            if (null != $results[$regionName]) {
                $result = $this->mergeResultWithOldResult($result, $results[$regionName]);
            }

            $results[$regionName] = $result;
        }

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $results);
    }

    /**
     * @param \App\Entity\Quarters $quarter
     * @param int $fieldOfficeId
     * @return array<int, int[]>
     * @throws \Doctrine\DBAL\Exception
     */
    private function getLess(\App\Entity\Quarters $quarter, int $fieldOfficeId): array
    {
        /**
         * All absentees(client_session record with remarks)
         *
         * RETURNS [client_remarks_id=>[form =>score]]
         */

        $result = [];
        $initialValues = $this->getInitialValues();
        $sessionIds = $this->repository->findSessionsIdsByQuarter($quarter, $fieldOfficeId);

        if (\count($sessionIds) == 0) {
            return $result;
        }

        /** @var ClientSessionModel[] $sessionClients */
        $sessionClients = $this->clientSessionsRepository->findAbsenteesBySessionIds($sessionIds);
        $clientTypeFormEquivalent = $this->getClientTypeFormEquivalent();

        foreach ($sessionClients as $sessionClient) {
            if (! isset($clientTypeFormEquivalent[$sessionClient->getRole()])) {
                continue;
            }

            $clientTypeForm = $clientTypeFormEquivalent[$sessionClient->getRole()];

            if (!isset($initialValues[$clientTypeForm])) {
                continue;
            }

            if (! isset($result[$sessionClient->getClientRemarksId()])) {
                $result[$sessionClient->getClientRemarksId()] = $initialValues;
            }

            $result[$sessionClient->getClientRemarksId()][$clientTypeForm]++;
        }

        return $result;
    }

    private function getTotalLess(array $less): array
    {
        $total = ['form5' => 0, 'form21' => 0, 'form44' => 0, 'form45' => 0];

        foreach ($less as $forms) {
            foreach ($forms as $formName => $value) {
                $total[$formName] += $value;
            }
        }

        return $total;
    }

    /**
     * @param \App\Entity\Quarters $quarter
     * @param int $fieldOfficeId
     * @return int[]
     * @throws \Doctrine\DBAL\Exception
     */
    private function getClientsAttendingTC(\App\Entity\Quarters $quarter, int $fieldOfficeId): array
    {
        $initialValues = $this->getInitialValues();
        $sessionIds = $this->repository->findSessionsIdsByQuarter($quarter, $fieldOfficeId);

        if (\count($sessionIds) == 0) {
            return $initialValues;
        }

        /** @var ClientSessionModel[] $sessionClients */
        $sessionClients = $this->clientSessionsRepository->findClientAttendeesBySessionIds($sessionIds);
        $clientTypeFormEquivalent = $this->getClientTypeFormEquivalent();

        foreach ($sessionClients as $sessionClient) {
            if (! isset($clientTypeFormEquivalent[$sessionClient->getRole()])) {
                continue;
            }

            $clientTypeForm = $clientTypeFormEquivalent[$sessionClient->getRole()];

            if (!isset($initialValues[$clientTypeForm])) {
                continue;
            }

            $initialValues[$clientTypeForm]++;
        }

        return $initialValues;
    }

    private function getPercentageOfClientsAttendingTc(array $clientsAttendingTc): array
    {
        $data = [];
        $totalClients = [];
        $clientTypeForm = $this->getClientTypeFormEquivalent();
        $clientTypes = $this->clientTypesRepository->list();

        foreach ($clientTypes as $clientType) {
            if ('Pet' == $clientType->getCode() || 'Term' == $clientType->getCode()) {
                continue;
            }
            $clients = $this->clientsRepository->findByClientTypeId($clientType->getClientTypeId());
            $form = $clientTypeForm[$clientType->getCode()];

            if (! isset($totalClients[$form])) {
                $totalClients[$form] = 0;
            }

            $totalClients[$form] += \count($clients);
        }

        foreach ($clientsAttendingTc as $form => $value) {
            $totalClient = $totalClients[$form];
            $data[$form] = $totalClient > 0 ? (($value / $totalClient) * 100) : 0;
        }

        return $data;
    }

    private function getInitialValues(): array
    {
        return ['form5' => 0, 'form21' => 0, 'form44' => 0, 'form45' => 0];
    }

    private function getRegionalTC7ClientRemarksInitialValues(): array
    {
        return [
            '3' => 0,
            '4' => 0,
            '5' => 0,
            '13' => 0,
            '7'=> 0,
            '6'=> 0,
            '8'=> 0,
            '9'=> 0,
            '10' => 0,
            '11' => 0,
            '12' => 0,
        ];
    }

    private function getClientTypeFormEquivalent(): array
    {
        return [
            'PS' => 'form5',
            'PR' => 'form21',
            'PD' => 'form21',
            'JICL' => 'form45',
            'FTMDO' => 'form44',
        ];
    }

    private function getCurrentQuarterInitialValues(\App\Entity\Quarters $quarterData): array
    {
        $values = [];
        $initialValues = $this->getInitialValues();
        $months = $this->appDateHelper->getMonthsByQuarterString($quarterData->getName());

        foreach ($months as $month) {
            $dateObj   = \DateTime::createFromFormat('!m', (string) $month);
            $values[$dateObj->format('F')] = $initialValues;
        }

        return $values;
    }

    private function generateRegionalTC7RowInitialValue(): array
    {
        $result = [];

        for ($i = 1; $i <= 17; $i++) {
            $result[$i] = 0;
        }

        return $result;
    }

    private function getRegionalTC7ClientRemarksIdColumnNumberEquivalent(int $clientRemarksId): int
    {
        // client remarks id => column number
        $equivalents = [
            3 => 3,
            4 => 3,
            5 => 3,
            13 => 5,
            7 => 6,
            6 => 7,
            8 => 8,
            9 => 9,
            10 => 10,
            11 => 12,
            12 => 13,
        ];

        return $equivalents[$clientRemarksId];
    }

    /**
     * @param int[] $regionIds
     * @return array<string, string>
     */
    private function getRegionNames(array $regionIds): array
    {
        return $this->regionService->getRegionNamesByIds($regionIds);
    }

    /**
     * @param int[] $ids
     * @return int[][]
     */
    private function getRegionIdsByFieldOfficeIds(array $ids): array
    {
        return $this->fieldOfficeService->getRegionIdsByFieldOfficeIds($ids);
    }

    private function extractRegionName(array $regionNamesWithId, array $regionIdsWithFieldOffice, int $fieldOfficeId): string
    {
        $regionId = $this->extractRegionId($regionIdsWithFieldOffice, $fieldOfficeId);
        return $regionNamesWithId[$regionId];
    }

    private function extractRegionId(array $regionIdsWithFieldOffice, int $fieldOfficeId): int
    {
        $returnRegionId = 1;

        foreach ($regionIdsWithFieldOffice as $regionId=>$fieldOffice) {
            if ($fieldOffice === $fieldOfficeId) {
                $returnRegionId = $regionId;
                break;
            }
        }

        return $returnRegionId;
    }

    private function mergeResultWithOldResult(array $result, array $oldResult): array
    {
        foreach ($oldResult as $key=>$value) {
            $result[$key] += $value;
        }

        return $result;
    }
}