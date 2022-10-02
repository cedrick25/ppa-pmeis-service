<?php

declare(strict_types=1);

namespace App\Service\TherapeuticCommunity;

use App\Common\AppDateHelper;
use App\Common\AppFormatter;
use App\Enum\AuditTrailActions;
use App\Enum\Response as ResponseEnum;
use App\Model\ClientSessions as ClientSessionModel;
use App\Model\Sessions as SessionsModel;
use App\Repository\ClientSessionsRepository;
use App\Repository\QuartersRepository;
use App\Repository\ResourceFacilitatorSessionRepository;
use App\Repository\SessionsRepository;
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
        private ResourceFacilitatorSessionRepository $resourceFacilitatorSessionRepository,
        private AuditTrail                           $auditTrail,
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
                return $this->appFormatter->formatResponse(ResponseEnum::VALIDATING_FAILED, null, $this->appFormatter->formatErrors($errors));
            }

            $id = $this->repository->create($sessionData);

            if ($id == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::CREATING_FAILED, null, ['app' => 'Session already exist.']);
            }

            $this->auditTrail->log(AuditTrailActions::CREATE, $sessionData->jsonSerialize(), $this->shortName, $id);

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

            $this->auditTrail->log(AuditTrailActions::CREATE, $sessionData->jsonSerialize(), $this->shortName, $id);

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
        } catch (CacheException|\Psr\Cache\InvalidArgumentException $exception) {
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
        } catch (CacheException|\Psr\Cache\InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function deleteById(int $id): array
    {
        try {
            $isDeleted = $this->repository->delete($id);

            if (!$isDeleted) {
                return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['app' => ResponseEnum::NO_DATA]);
            }

            $this->auditTrail->log(AuditTrailActions::DELETE, [], $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_SUCCESS, null);
        } catch (\Psr\Cache\InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (\Doctrine\DBAL\Driver\Exception|Exception $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['orm' => $exception->getMessage()]);
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
        } catch (InvalidArgumentException|Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['app' => $e->getMessage()]);
        } catch (\Psr\Cache\InvalidArgumentException $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['cache' => $e->getMessage()]);
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

    public function getPaginated(int $page, int $pageSize): array
    {
        try {
            $sessions = $this->repository->paginated($page, $pageSize);

            if ($sessions == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

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
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $e->getMessage()]);
        }
    }

    public function getTCA1Part2(\App\Entity\Quarters $quarterData, int $fieldOfficeId): array
    {
        try {

            $quarterData = $this->repository->fetchTCA1Part2($fieldOfficeId, $quarterData);

            return array_values($quarterData ?? []);
        } catch (CacheException $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $e->getMessage()]);
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
        } catch (\Doctrine\DBAL\Exception|\Doctrine\DBAL\Driver\Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['orm' => $e->getMessage()]);
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
            $less = $this->getLess($currentQuarter, $fieldOfficeId);
            $quarterInitialValues = $this->getCurrentQuarterInitialValues($currentQuarter);

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
                    'clientsAttendingTC' => $this->getClientsAttendingTC($currentQuarter, $fieldOfficeId),
                    'percentageOfClientsAttendingTC' => $initialValues
                ]
            );
        } catch (Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, [
                'app' => $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace()]);

        } catch (\Doctrine\DBAL\Driver\Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['orm' => $e->getMessage()]);
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
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['cache' => $e->getMessage()]);
        }
    }

    /**
     * @param \App\Entity\Quarters $quarter
     * @param int $fieldOfficeId
     * @return array<int, int[]>
     * @throws \Doctrine\DBAL\Driver\Exception
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
            foreach ($forms as $formName=>$value) {
                $total[$formName] += $value;
            }
        }

        return $total;
    }

    /**
     * @param \App\Entity\Quarters $quarter
     * @param int $fieldOfficeId
     * @return int[]
     * @throws \Doctrine\DBAL\Driver\Exception
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

    private function getInitialValues(): array
    {
        return ['form5' => 0, 'form21' => 0, 'form44' => 0, 'form45' => 0];
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
}