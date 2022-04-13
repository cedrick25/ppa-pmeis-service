<?php

declare(strict_types=1);

namespace App\Service\TherapeuticCommunity;

use App\Common\AppDateHelper;
use App\Common\AppFormatter;
use App\Enum\Response as ResponseEnum;
use App\Model\Sessions as SessionsModel;
use App\Repository\ClientSessionsRepository;
use App\Repository\ClientsRepository;
use App\Repository\QuartersRepository;
use App\Repository\SessionsRepository;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\ORMException;
use Exception;
use Psr\Cache\CacheException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Sessions implements SessionsInterface
{
    const ON_CS_CLIENT_TYPE_ID = 4;

    public function __construct(
        private AppFormatter             $appFormatter,
        private SessionsRepository       $repository,
        private ValidatorInterface       $validator,
        private AppDateHelper            $appDateHelper,
        private QuartersRepository       $quartersRepository,
        private ClientsRepository        $clientsRepository,
        private ClientSessionsRepository $clientSessionsRepository,
    ){}

    public function create(SessionsModel $sessionData): array
    {
        try {
            $errors = $this->validator->validate($sessionData);

            if (count($errors) > 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::VALIDATING_FAILED, null, $this->appFormatter->formatErrors($errors));
            }

            $id = $this->repository->create($sessionData);

            if ($id == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['app' => 'Session already exist.']);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_SUCCESS, ['id' => $id]);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['orm' => $exception->getMessage()]);
        } catch (Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['app' => $e->getMessage()]);
        } catch (\Psr\Cache\InvalidArgumentException $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['cache' => $e->getMessage()]);
        }
    }

    public function createWithClientsAndFacilitators(SessionsModel $sessionData): array
    {
        try {
            $errors = $this->validator->validate($sessionData);

            if (count($errors) > 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::VALIDATING_FAILED, null, $this->appFormatter->formatErrors($errors));
            }

            $id = $this->repository->createWithClientsAndFacilitators($sessionData);

            if ($id == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['app' => 'Session already exist.']);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_SUCCESS, ['id' => $id]);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['orm' => $exception->getMessage()]);
        } catch (Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['app' => $e->getMessage()]);
        } catch (\Psr\Cache\InvalidArgumentException $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['cache' => $e->getMessage()]);
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
        } catch (CacheException| \Psr\Cache\InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function getAllWithClientsAndFacilitators(): array
    {
        try {
            $sessions = $this->repository->listWithClientsAndFacilitators();

            if ($sessions == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $sessions);
        } catch (CacheException| \Psr\Cache\InvalidArgumentException $exception) {
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
        } catch (\Psr\Cache\InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (Exception $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['orm' => $exception->getMessage()]);
        }
    }

    public function updateById(int $id, SessionsModel $sessionData):array
    {
        try {
            $isUpdated = $this->repository->update($id, $sessionData);

            if ($isUpdated !== ResponseEnum::OK) {
                return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['app' => $isUpdated]);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_SUCCESS, null);
        } catch (InvalidArgumentException | Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['app' => $e->getMessage()]);
        } catch (\Psr\Cache\InvalidArgumentException $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['cache' => $e->getMessage()]);
        }
    }

    public function updateByIdWithClientAndFacilitators(int $id, SessionsModel $sessionData):array
    {
        try {
            $isUpdated = $this->repository->updateWithClientAndFacilitators($id, $sessionData);

            if ($isUpdated !== ResponseEnum::OK) {
                return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['app' => $isUpdated]);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_SUCCESS, null);
        } catch (\Doctrine\DBAL\Driver\Exception $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['orm' => $exception->getMessage()]);
        } catch (InvalidArgumentException | Exception $e) {
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
        } catch (\Psr\Cache\InvalidArgumentException | CacheException $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['cache' => $e->getMessage()]);
        }
    }

    public function getPaginated(int $page, int $pageSize): array
    {
        try {
            $sessions = $this->repository->paginated($page, $pageSize);

            if ($sessions == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $sessions);
        } catch (CacheException| \Psr\Cache\InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
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
            $sessions = $this->repository->fetchTCIA2($quarterId, $fieldOfficeId, $role);

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

                if (! isset($rows[$rowIdentifier])) {
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
        } catch (\Doctrine\DBAL\Exception | \Doctrine\DBAL\Driver\Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['orm' => $e->getMessage()]);
        }
    }

    public function getTC7(int $quarterId, int $fieldOfficeId):array
    {
        try {
            $currentQuarter = $this->quartersRepository->find($quarterId);
            if ($currentQuarter == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            $previousQuarter = $this->quartersRepository->fetchPreviousQuarterByNameAndYear($currentQuarter->getName(), $currentQuarter->getYear());

            $activeSupervisions = $this->getActiveSupervision($previousQuarter, $fieldOfficeId);
            $activeCourtesySupervision = $this->getActiveSupervision($previousQuarter, $fieldOfficeId, self::ON_CS_CLIENT_TYPE_ID);
            $superVisionReferrals = $this->getSupervisionReferrals($currentQuarter, $fieldOfficeId);
            $courtesySupervisionReferrals = $this->getSupervisionReferrals($currentQuarter, $fieldOfficeId, self::ON_CS_CLIENT_TYPE_ID);
            $supervisionCasesDropped = $this->getSupervisionCasesDropped($currentQuarter, $fieldOfficeId);
            $totalSupervisionCasesHandled = $this
                ->getTotalSupervisionCaseHandled(
                    $activeSupervisions,
                    $activeCourtesySupervision,
                    $superVisionReferrals,
                    $courtesySupervisionReferrals,
                    $supervisionCasesDropped
                );
            $less = $this->getLess($currentQuarter, $fieldOfficeId);
            $totalLess = $this->getTotalLess($less);
            $totalAdjustedSupervisionCaseLoad = $this->getTotalAdjustedSupervisionCaseLoad($totalSupervisionCasesHandled, $totalLess);
            $clientsAttendingTC = $this->getClientsAttendingTC($currentQuarter, $fieldOfficeId);
            $percentageOfClientsAttendingTC = $this
                ->getPercentageOfClientsAttendingTC($totalAdjustedSupervisionCaseLoad, $clientsAttendingTC);

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, [
                'activeSupervisions' => $activeSupervisions,
                'activeCourtesySupervision' => $activeCourtesySupervision,
                'superVisionReferrals' => $superVisionReferrals,
                'courtesySupervisionReferrals' => $courtesySupervisionReferrals,
                'supervisionCasesDropped' => $supervisionCasesDropped,
                'totalSupervisionCasesHandled' => $totalSupervisionCasesHandled,
                'less' => $less,
                'totalLess' => $totalLess,
                'totalAdjustedSupervisionCaseLoad' => $totalAdjustedSupervisionCaseLoad,
                'clientsAttendingTC' => $clientsAttendingTC,
                'percentageOfClientsAttendingTC' => $percentageOfClientsAttendingTC
            ]);
        } catch (Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, [
                'app' => $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace(),]);
        } catch (\Doctrine\DBAL\Driver\Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['orm' => $e->getMessage()]);
        }
    }

    /**
     * @param \App\Entity\Quarters|null $quarter
     * @param int $fieldOfficeId
     * @param int|null $clientRemarksId
     * @return int[]
     */
    private function getActiveSupervision(?\App\Entity\Quarters $quarter, int $fieldOfficeId, ?int $clientRemarksId = null): array
    {
        /**
         * CRITERIA:
         * Clients supervision end is within the previous quarter
         * Same field office and client remarks = null | 4 (on CS)
         *
         * RETURNS:  [client_type_id:score]
         */
        if ($quarter == null) {
           return [];
        }

        $result = [];
        $previousQuarterDates = $this->quartersRepository->getQuarterMinMaxDate($quarter);
        $clients = $this->clientsRepository->findBySupervisionPeriodDateRange($previousQuarterDates, 'END', $fieldOfficeId, $clientRemarksId);

        foreach ($clients as $client) {
            if (! isset($result[$client->getClientTypeId()])) {
                $result[$client->getClientTypeId()] = 0;
            }

            $result[$client->getClientTypeId()]++;
        }

        return $result;
    }

    /**
     * @param \App\Entity\Quarters $quarter
     * @param int $fieldOfficeId
     * @param int|null $clientRemarksId
     * @return int[][]
     */
    private function getSupervisionReferrals(\App\Entity\Quarters $quarter, int $fieldOfficeId, ?int $clientRemarksId = null): array
    {
        /**
         * CRITERIA:
         * Clients supervision start is within the selected quarter
         * Same field office and client remarks = null | 4 (on CS)
         *
         * RETURNS [month:[client_type_id:score]]
         */

        $result = [];
        $quarterMinMaxDate = $this->quartersRepository->getQuarterMinMaxDate($quarter);
        $clients = $this->clientsRepository->findBySupervisionPeriodDateRange($quarterMinMaxDate, 'START', $fieldOfficeId, $clientRemarksId);

        foreach ($clients as $client) {
            $supervisionMonth = intval($client->getSupervisionStart()->format('m'));

            if (! isset($result[$supervisionMonth][$client->getClientTypeId()])) {
                $result[$supervisionMonth][$client->getClientTypeId()] = 0;
            }

            $result[$supervisionMonth][$client->getClientTypeId()]++;
        }

        return $result;
    }

    /**
     * @param \App\Entity\Quarters $quarter
     * @param int $fieldOfficeId
     * @return int[][]
     */
    private function getSupervisionCasesDropped(\App\Entity\Quarters $quarter, int $fieldOfficeId): array
    {
        /**
         * CRITERIA:
         * Clients supervision end is within the selected quarter
         * Same field office and client remarks = null | 4 (on CS)
         *
         * RETURNS [month:[client_type_id:score]]
         */

        $result = [];
        $quarterMinMaxDate = $this->quartersRepository->getQuarterMinMaxDate($quarter);
        $clients = $this->clientsRepository
            ->findSupervisionCasesDropBySupervisionPeriodEndDateRange($quarterMinMaxDate,  $fieldOfficeId);

        foreach ($clients as $client) {
            $supervisionMonth = intval($client->getSupervisionStart()->format('m'));

            if (! isset($result[$supervisionMonth][$client->getClientTypeId()])) {
                $result[$supervisionMonth][$client->getClientTypeId()] = 0;
            }

            $result[$supervisionMonth][$client->getClientTypeId()]++;
        }

        return $result;
    }

    /**
     * @param int[] $activeSupervisions
     * @param int[] $activeCourtesySupervisions
     * @param int[][] $superVisionReferrals
     * @param int[][] $courtesySupervisionReferrals
     * @param int[][] $supervisionCasesDropped
     * @return int[]
     */
    private function getTotalSupervisionCaseHandled(
        array $activeSupervisions,
        array $activeCourtesySupervisions,
        array $superVisionReferrals,
        array $courtesySupervisionReferrals,
        array $supervisionCasesDropped
    ): array
    {
        $result = [];

        foreach ($activeSupervisions as $clientTypeId=>$score) {
            if (! isset($result[$clientTypeId])) {
                $result[$clientTypeId] = 0;
            }
            $result[$clientTypeId] += $score;
        }

        foreach ($activeCourtesySupervisions as $clientTypeId=>$score) {
            if (! isset($result[$clientTypeId])) {
                $result[$clientTypeId] = 0;
            }
            $result[$clientTypeId] += $score;
        }

        foreach ($superVisionReferrals as $clientTypeId=>$superVisionReferral) {
            foreach ($superVisionReferral as $supervisionMonth=>$score) {
                if (! isset($result[$clientTypeId])) {
                    $result[$clientTypeId] = 0;
                }
                $result[$clientTypeId] += $score;
            }
        }

        foreach ($courtesySupervisionReferrals as $clientTypeId=>$superVisionReferral) {
            foreach ($superVisionReferral as $supervisionMonth=>$score) {
                if (! isset($result[$clientTypeId])) {
                    $result[$clientTypeId] = 0;
                }
                $result[$clientTypeId] += $score;
            }
        }

        foreach ($supervisionCasesDropped as $clientTypeId=>$superVisionReferral) {
            foreach ($superVisionReferral as $supervisionMonth=>$score) {
                if (! isset($result[$clientTypeId])) {
                    $result[$clientTypeId] = 0;
                }
                $result[$clientTypeId] += $score;
            }
        }

        return $result;
    }

    /**
     * @param \App\Entity\Quarters $quarter
     * @param int $fieldOfficeId
     * @return array<int, int[]>
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function getLess(\App\Entity\Quarters $quarter, int $fieldOfficeId):array
    {
        /**
         * CRITERIA:
         *  Clients that has no session in the current quarter, same field office as selected
         *  and remarks are:
         *      a.   On CS to other FOs
                b.   Died with no report submitted to Court/ BPP
                c.   Absconded with no report submitted to Court/ BPP
                d.   In jail with no report submitted to court/ BPP
                e.   With serious ailment
                f.   On travel abroad ( with permit)
                h.   Cases Pending in Court/ BPP
                i.   Others (specify):  No initial report
         * RETURNS [client_remarks_id:[client_type_id:score]]
         */

        $result = [];
        $quarterMinMaxDate = $this->quartersRepository->getQuarterMinMaxDate($quarter);
        /** @var \App\Entity\Clients[] $clients */
        $clients = $this->clientsRepository
            ->findSupervisionCasesDropBySupervisionPeriodEndDateRangeLess($quarterMinMaxDate,  $fieldOfficeId);

        $sessionIds = $this->repository->findSessionsIdsByQuarter($quarter);
        $sessionIds = array_map(fn($sessionId) => $sessionId['session_id'], $sessionIds);

        $sessionClients = $this->clientSessionsRepository->findClientsBySessionIds($sessionIds);
        $sessionClientIds = array_map(fn($client) => $client['client_id'], $sessionClients);

        foreach ($clients as $client) {
            if (in_array($client->getClientId(), $sessionClientIds)) {
                continue;
            }

            if (! isset( $result[$client->getClientRemarksId()][$client->getClientTypeId()] )) {
                $result[$client->getClientRemarksId()][$client->getClientTypeId()] = 0;
            }

            $result[$client->getClientRemarksId()][$client->getClientTypeId()]++;
        }

        return $result;
    }

    /**
     * @param int[][] $less
     * @return int[]
     */
    private function getTotalLess(array $less): array
    {
        $result = [];

        foreach ($less as $clientRemarksId=>$les) {
            foreach ($les as $clientTypeId=>$score) {
                if (! isset($result[$clientTypeId])) {
                    $result[$clientTypeId] = 0;
                }
                $result[$clientTypeId] += $score;
            }
        }

        return $result;
    }

    /**
     * @param int[] $totalSupervisionCasesHandled
     * @param int[] $totalLess
     * @return int[]
     */
    private function getTotalAdjustedSupervisionCaseLoad(array $totalSupervisionCasesHandled, array $totalLess): array
    {
        $result = [];

        if (count($totalSupervisionCasesHandled) > count($totalLess)) {
            foreach ($totalSupervisionCasesHandled as $clientTypeId=>$score) {
                $less = !isset($totalLess[$clientTypeId]) ? 0 : $totalLess[$clientTypeId];
                $result[$clientTypeId] = $score - $less;
            }
        } else {
            foreach ($totalLess as $clientTypeId=>$score) {
                $totalSupervision = !isset($totalSupervisionCasesHandled[$clientTypeId]) ? 0 : $totalSupervisionCasesHandled[$clientTypeId];
                $result[$clientTypeId] = $totalSupervision - $score;
            }
        }

        return $result;
    }

    /**
     * @param \App\Entity\Quarters $quarter
     * @param int $fieldOfficeId
     * @return int[]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function getClientsAttendingTC(\App\Entity\Quarters $quarter, int $fieldOfficeId):array
    {
        /**
         * CRITERIA:
         * All clients that attend session in current quarter and field office
         *
         * RETURNS [client_type_id:score]
         */
        $result = [];
        $sessionIds = $this->repository->findSessionsIdsByQuarter($quarter);
        $sessionIds = array_map(fn($sessionId) => $sessionId['session_id'], $sessionIds);
        $sessionClients = $this->clientSessionsRepository->findClientsBySessionIdsAndFieldOfficeId($sessionIds, $fieldOfficeId);

        foreach ($sessionClients as $sessionClient) {
            if (! isset($result[$sessionClient['client_type_id']])) {
                $result[$sessionClient['client_type_id']] = 0;
            }
            $result[$sessionClient['client_type_id']]++;
        }

        return $result;
    }

    /**
     * @param int[] $totalAdjustedSupervisionCaseLoad
     * @param int[] $clientsAttendingTC
     * @return int[]
     */
    private function getPercentageOfClientsAttendingTC(
        array $totalAdjustedSupervisionCaseLoad,
        array $clientsAttendingTC
    ):array {
        $result = [];

        foreach ($totalAdjustedSupervisionCaseLoad as $clientTypeId=>$score) {
            if (! isset($clientsAttendingTC[$clientTypeId])) {
                continue;
            }

            $result[$clientTypeId] = $score > 0 ? ($clientsAttendingTC[$clientTypeId] / $score) * 100 : 0;
        }

        return $result;
    }
}