<?php

declare(strict_types=1);

namespace App\Service\TherapeuticCommunity;

use App\Common\AppDateHelper;
use App\Common\AppFormatter;
use App\Enum\Response as ResponseEnum;
use App\Model\Sessions as SessionsModel;
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
        private AppFormatter       $appFormatter,
        private SessionsRepository $repository,
        private ValidatorInterface $validator,
        private AppDateHelper      $appDateHelper,
        private QuartersRepository $quartersRepository,
        private ClientsRepository  $clientsRepository,
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
        } catch (ORMException $exception) {
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
        } catch (ORMException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['orm' => $exception->getMessage()]);
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
        } catch (\Doctrine\DBAL\Driver\Exception | ORMException $exception) {
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
                    $session["month_quarter"] = [$monthIdentifier];
                    $rows[$rowIdentifier] = $session;
                } else {
                    $rows[$rowIdentifier]["month_quarter"][] = $monthIdentifier;
                }
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
//            $a = 0;
            // LESS: Clients under the following circumstances

            // Total Adjusted Supervision Caseload This Quarter
            // Total Number of Clients Attending TC
            // Percentage of Clients Attending TC

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, []);
        } catch (\Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['app' => $e->getMessage()]);
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

    public function getSupervisionReferrals(?\App\Entity\Quarters $quarter, int $fieldOfficeId, ?int $clientRemarksId = null): array
    {
        /**
         * CRITERIA:
         * Clients supervision start is within the selected quarter
         * Same field office and client remarks = null | 4 (on CS)
         *
         * RETURNS [month:[client_type_id:score]]
         */
        if ($quarter == null) {
            return [];
        }

        $result = [];
        $quarterMinMaxDate = $this->quartersRepository->getQuarterMinMaxDate($quarter);
        $clients = $this->clientsRepository->findBySupervisionPeriodDateRange($quarterMinMaxDate, 'START', $fieldOfficeId, $clientRemarksId);

        foreach ($clients as $client) {
            $supervisionMonth = intval($client->getSupervisionStart()->format('m'));

            if (! isset($result[$client->getClientTypeId()][$supervisionMonth])) {
                $result[$client->getClientTypeId()][$supervisionMonth] = 0;
            }

            $result[$client->getClientTypeId()][$supervisionMonth]++;
        }

        return $result;
    }

    public function getSupervisionCasesDropped(?\App\Entity\Quarters $quarter, int $fieldOfficeId): array
    {
        /**
         * CRITERIA:
         * Clients supervision end is within the selected quarter
         * Same field office and client remarks = null | 4 (on CS)
         *
         * RETURNS [month:[client_type_id:score]]
         */
        if ($quarter == null) {
            return [];
        }

        $result = [];
        $quarterMinMaxDate = $this->quartersRepository->getQuarterMinMaxDate($quarter);
        $clients = $this->clientsRepository
            ->findSupervisionCasesDropBySupervisionPeriodEndDateRange($quarterMinMaxDate,  $fieldOfficeId);

        foreach ($clients as $client) {
            $supervisionMonth = intval($client->getSupervisionStart()->format('m'));

            if (! isset($result[$client->getClientTypeId()][$supervisionMonth])) {
                $result[$client->getClientTypeId()][$supervisionMonth] = 0;
            }

            $result[$client->getClientTypeId()][$supervisionMonth]++;
        }

        return $result;
    }
}