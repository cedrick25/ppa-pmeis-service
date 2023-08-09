<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\Quarters;
use App\Entity\Sessions;
use App\Enum\Response as ResponseEnum;
use App\Model\Sessions as SessionsModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Psr\Cache\CacheException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method Sessions|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sessions|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sessions[]    findAll()
 * @method Sessions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionsRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "sessions";

    public function __construct(
        ManagerRegistry                              $registry,
        private AppDateHelper                        $appDateHelper,
        private TagAwareCacheInterface               $cache,
        private CacheHelper                          $cacheHelper,
        private Helper                               $helper,
        private ClientSessionsRepository             $clientSessionsRepository,
        private ResourceFacilitatorSessionRepository $resourceFacilitatorSessionRepository,
        private QuartersRepository                   $quartersRepository,
        private ClientTypesRepository                $clientTypesRepository,
        private ClientsRepository                    $clientsRepository,
        private ClientRemarksRepository              $clientRemarksRepository,
    )
    {
        parent::__construct($registry, Sessions::class);
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function create(SessionsModel $sessionData): int|null
    {
        $isExist = $this->isExisting($sessionData);

        if ($isExist) {
            return null;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $session = new Sessions();
        $session->setFieldOfficeId($sessionData->getFieldOfficeId());
        $session->setPhaseId($sessionData->getPhaseId());
        $session->setBatch($sessionData->getBatch());
        $session->setSessionActivityId($sessionData->getSessionActivityId());
        $session->setTreatmentCategoryId($sessionData->getTreatmentCategoryId());
        $session->setDate($this->appDateHelper->convertStringToImmutableDate($sessionData->getDate()));
        $session->setVenueId($sessionData->getVenueId());
        $session->setPeriod($sessionData->getPeriod());
        $session->setLiLo($sessionData->getLiLo());
        $session->setTreesPlanted($sessionData->getTreesPlanted());
        $session->setCreatedBy($sessionData->getCreatedBy());
        $session->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($session);
        $this->getEntityManager()->flush();

        return $session->getSessionId();
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function createWithClientsAndFacilitators(SessionsModel $sessionData): int|null
    {
        // if ($this->isExisting($sessionData)) {
        //     return null;
        // }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $session = new Sessions();
        $session->setTreesPlanted($sessionData->getTreesPlanted());
        $session->setFieldOfficeId($sessionData->getFieldOfficeId());
        $session->setPhaseId($sessionData->getPhaseId());
        $session->setBatch($sessionData->getBatch());
        $session->setSessionActivityId($sessionData->getSessionActivityId());
        $session->setTreatmentCategoryId($sessionData->getTreatmentCategoryId());
        $session->setDate($this->appDateHelper->convertStringToImmutableDate($sessionData->getDate()));
        $session->setVenueId($sessionData->getVenueId());
        $session->setPeriod($sessionData->getPeriod());
        $session->setLiLo($sessionData->getLiLo());
        $session->setFsgNumber($sessionData->getFsgNumber());
        $session->setIsCommunityService($sessionData->isCommunityService());
        $session->setActivityDetail($sessionData->getActivityDetail());
        $session->setIsTreePlanting($sessionData->isTreePlanting());
        $session->setIsCooperativeSelfHelp($sessionData->isCooperativeSelfHelp());
        $session->setIsCooperativeSelfHelpActivities($sessionData->isCooperativeSelfHelpActivities());
        $session->setCreatedBy($sessionData->getCreatedBy());
        $session->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($session);
        $this->getEntityManager()->flush();

        $this->clientSessionsRepository->batchCreate($session->getSessionId(), $sessionData->getAttendees());
        if ($sessionData->getAbsentees() !== null && count($sessionData->getAbsentees()) > 0) {
            $this->clientSessionsRepository->batchCreateAbsentees($session->getSessionId(), $sessionData->getAbsentees());
        }
        $this->resourceFacilitatorSessionRepository
            ->batchCreate($session->getSessionId(), $sessionData->getFacilitators());

        return $session->getSessionId();
    }

    /**
     * @return array<string, mixed>|null
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws CacheException
     */
    public function list(): ?array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllSessionsKey(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponseCustomQuery($params, function () {
            $conn = $this->getEntityManager()->getConnection();
            $sql = "SELECT se.*, MONTH(se.date) as quarter_month, YEAR(se.date) as quarter_year, fe.name as field_office_name,
                    p.name as phase_name, sa.name as session_activity_name, tc.name as treatment_category_name, v.name as venue_name
                 FROM sessions as se " .
                "LEFT JOIN field_offices as fe ON se.field_office_id = fe.field_office_id " .
                "LEFT JOIN phases as p ON se.field_office_id = p.phase_id " .
                "LEFT JOIN session_activities as sa ON se.session_activity_id = sa.session_activity_id " .
                "LEFT JOIN treatment_categories as tc ON se.treatment_category_id = tc.treatment_category_id " .
                "LEFT JOIN venues as v ON se.venue_id = v.venue_id " .
                "WHERE se.deleted_at IS NULL ORDER BY se.session_id DESC";
            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();
            $data = $query->fetchAllAssociative();
            $sessions = [];

            foreach ($data as $row) {
                $row['quarter_name'] = $this->appDateHelper->getQuarterByMonth(intval($row['quarter_month']));
                $sessions[] = $row;
            }

            return $sessions;
        });
    }

    /**
     * @return array<string, mixed> | null
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws CacheException
     */
    public function listWithClientsAndFacilitators(): ?array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllSessionsWithClientsAndFacilitatorsKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function () {
            $conn = $this->getEntityManager()->getConnection();
            $data = [];

            $sql = "SELECT se.*, MONTH(se.date) as quarter_month, YEAR(se.date) as quarter_year, fo.name as field_office_name,
                    p.name as phase_name, sa.name as session_activity_name, tc.name as treatment_category_name, v.name as venue_name
                 FROM sessions as se
                LEFT JOIN field_offices as fo ON se.field_office_id = fo.field_office_id
                LEFT JOIN phases as p ON se.field_office_id = p.phase_id
                LEFT JOIN session_activities as sa ON se.session_activity_id = sa.session_activity_id
                LEFT JOIN treatment_categories as tc ON se.treatment_category_id = tc.treatment_category_id
                LEFT JOIN venues as v ON se.venue_id = v.venue_id
                WHERE se.deleted_at IS NULL ORDER BY se.session_id DESC";
            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();

            $sessions = $query->fetchAllAssociative();

            foreach ($sessions as $session) {
                $session['client_session'] = $this->clientSessionsRepository->listBySessionId((int)$session['session_id']);
                $session['resource_facilitator'] = $this->resourceFacilitatorSessionRepository->listBySessionId((int)$session['session_id']);
                $session['quarter_name'] = $this->appDateHelper->getQuarterByMonth(intval($session['quarter_month']));

                $data[] = $session;
            }

            return $data;
        });
    }

    /**
     * @param int $id
     * @return bool
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function delete(int $id): bool
    {
        $session = $this->isExistingById($id);

        if (!$session) {
            return false;
        }
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->resourceFacilitatorSessionRepository->deleteBySessionId($id);
        $this->clientSessionsRepository->deleteBySessionId($id);

        $this->getEntityManager()->remove($session);
        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function softDelete(int $id): bool
    {
        $session = $this->isExistingById($id);

        if (!$session) {
            return false;
        }

        // TODO: check if existing in client sessions, resource facilitator sessions

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $session->setDeletedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function update(int $id, SessionsModel $sessionData): string
    {
        $session = $this->isExistingById($id);

        if (!$session) {
            return ResponseEnum::NO_RECORD;
        }

        if ($this->isConflicted($session, $sessionData)) {
            return ResponseEnum::CONFLICTED_INPUT;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $session->setTreesPlanted($sessionData->getTreesPlanted());
        $session->setPhaseId($sessionData->getPhaseId());
        $session->setBatch($sessionData->getBatch());
        $session->setSessionActivityId($sessionData->getSessionActivityId());
        $session->setFieldOfficeId($sessionData->getFieldOfficeId());
        $session->setTreatmentCategoryId($sessionData->getTreatmentCategoryId());
        $session->setDate($this->appDateHelper->convertStringToImmutableDate($sessionData->getDate()));
        $session->setVenueId($sessionData->getVenueId());
        $session->setPeriod($sessionData->getPeriod());
        $session->setLiLo($sessionData->getLiLo());
        $session->setCreatedBy($sessionData->getCreatedBy());
        $session->setUpdatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->flush();

        return ResponseEnum::OK;
    }

    /**
     * @throws NonUniqueResultException
     * @throws InvalidArgumentException
     * @throws Exception
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function updateWithClientAndFacilitators(int $id, SessionsModel $sessionData): string
    {
        $session = $this->isExistingById($id);

        if (!$session) {
            return ResponseEnum::NO_RECORD;
        }

        if ($this->isConflicted($session, $sessionData)) {
            return ResponseEnum::CONFLICTED_INPUT;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $session->setTreesPlanted($sessionData->getTreesPlanted());
        $session->setPhaseId($sessionData->getPhaseId());
        $session->setBatch($sessionData->getBatch());
        $session->setSessionActivityId($sessionData->getSessionActivityId());
        $session->setFieldOfficeId($sessionData->getFieldOfficeId());
        $session->setTreatmentCategoryId($sessionData->getTreatmentCategoryId());
        $session->setDate($this->appDateHelper->convertStringToImmutableDate($sessionData->getDate()));
        $session->setVenueId($sessionData->getVenueId());
        $session->setPeriod($sessionData->getPeriod());
        $session->setLiLo($sessionData->getLiLo());
        $session->setFsgNumber($sessionData->getFsgNumber());
        $session->setIsCommunityService($sessionData->isCommunityService());
        $session->setActivityDetail($sessionData->getActivityDetail());
        $session->setIsTreePlanting($sessionData->isTreePlanting());
        $session->setIsCooperativeSelfHelp($sessionData->isCooperativeSelfHelp());
        $session->setIsCooperativeSelfHelpActivities($sessionData->isCooperativeSelfHelpActivities());
        $session->setCreatedBy($sessionData->getCreatedBy());
        $session->setUpdatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->flush();

        $this->clientSessionsRepository->deleteBySessionId($session->getSessionId());
        $this->clientSessionsRepository->batchCreate($session->getSessionId(), $sessionData->getAttendees());
        $this->clientSessionsRepository->batchCreateAbsentees($session->getSessionId(), $sessionData->getAbsentees());

        $this->resourceFacilitatorSessionRepository->deleteBySessionId($session->getSessionId());
        $this->resourceFacilitatorSessionRepository
            ->batchCreate($session->getSessionId(), $sessionData->getFacilitators());

        return ResponseEnum::OK;
    }

    public function isExistingById(int $id): bool|Sessions
    {
        $session = $this->findOneBy([
            'sessionId' => $id,
            'deletedAt' => null
        ]);

        return ($session == null) ? false : $session;
    }

    /**
     * @param int $page
     * @param int $pageSize
     * @return array<string, mixed>|null
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws CacheException
     */
    public function paginated(int $page = 1, int $pageSize = 10): ?array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getSessionsPaginatedKey($page, $pageSize),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG,
            'pageSize' => $pageSize,
            'page' => $page
        ];

        return $this->helper->createPaginatedResponseCustomQuery($params, function () use ($pageSize, $page) {
            $startOffset = $pageSize * ($page - 1);
            $result = [];

            $conn = $this->getEntityManager()->getConnection();
            $sql = "SELECT se.*, MONTH(se.date) as quarter_month, YEAR(se.date) as quarter_year, fe.name as field_office_name,
                         p.name as phase_name, sa.name as session_activity_name, tc.name as treatment_category_name, v.name as venue_name
                    FROM sessions as se
                    LEFT JOIN field_offices as fe ON se.field_office_id = fe.field_office_id
                    LEFT JOIN phases as p ON se.field_office_id = p.phase_id
                    LEFT JOIN session_activities as sa ON se.session_activity_id = sa.session_activity_id
                    LEFT JOIN treatment_categories as tc ON se.treatment_category_id = tc.treatment_category_id
                    LEFT JOIN venues as v ON se.venue_id = v.venue_id
                    WHERE se.deleted_at IS NULL ";

            $result['totalItems'] = $this->helper->getCustomQueryPaginatedTotalItems($conn, $sql);

            $sql .= "LIMIT $pageSize OFFSET $startOffset";

            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();
            $sessions = $query->fetchAllAssociative();
            $result['data'] = [];

            foreach ($sessions as $session) {
                $session['quarter_name'] = $this->appDateHelper->getQuarterByMonth(intval($session['quarter_month']));
                $result['data'][] = $session;
            }

            return $result;
        });
    }

    /**
     * @return array<string, mixed>|null
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws CacheException
     */
    public function fetchById(int $id): ?array
    {
        $clientTypes = $this->getAllClientTypes();
        $clientNames = $this->geClientNames();
        $clientRemarks = $this->geClientRemarks();

        $params = [
            'cacheKey' => $this->cacheHelper->getSessionsById($id),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponseCustomQuery($params, function () use ($clientTypes, $clientNames, $clientRemarks, $id) {
            $conn = $this->getEntityManager()->getConnection();

            $sql = "SELECT se.*, MONTH(se.date) as quarter_month, YEAR(se.date) as quarter_year, fe.name as field_office_name,
                    p.name as phase_name, sa.name as session_activity_name, tc.name as treatment_category_name, v.name as venue_name,
                    r.name, r.region_id
                 FROM sessions as se " .
                "LEFT JOIN field_offices as fe ON se.field_office_id = fe.field_office_id " .
                "LEFT JOIN phases as p ON se.field_office_id = p.phase_id " .
                "LEFT JOIN regions as r ON fe.region_id = r.region_id " .
                "LEFT JOIN session_activities as sa ON se.session_activity_id = sa.session_activity_id " .
                "LEFT JOIN treatment_categories as tc ON se.treatment_category_id = tc.treatment_category_id " .
                "LEFT JOIN venues as v ON se.venue_id = v.venue_id " .
                "WHERE se.session_id = $id AND se.deleted_at IS NULL ORDER BY se.session_id DESC";
            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();
            $session = $query->fetchAssociative();

            $session['quarter_name'] = $this->appDateHelper->getQuarterByMonth(intval($session['quarter_month']));
            $clients = $this->clientSessionsRepository->listBySessionId($id);

            foreach ($clients as $client) {
                if (null === $client->getClientRemarksId()) {
                    $session['attendees'][] = [
                        'type' => [
                            'label' => $clientTypes[strtoupper($client->getRole())][1],
                            'value' => $clientTypes[strtoupper($client->getRole())][0]
                        ],
                        'id' => [
                            'label' => $clientNames[$client->getClientId()],
                            'value' => $client->getClientId()
                        ],
                        'fsi' => [
                            'label' => $client->getFsi() ? 'Yes' : 'No',
                            'value' => $client->getFsi()
                        ],
                    ];

                    continue;
                }

                $session['absentees'][] = [
                    'type' => [
                        'label' => $clientTypes[strtoupper($client->getRole())][1],
                        'value' => $clientTypes[strtoupper($client->getRole())][0]
                    ],
                    'id' => [
                        'label' => $clientNames[$client->getClientId()],
                        'value' => $client->getClientId()
                    ],
                    'fsi' => [
                        'label' => $client->getFsi() ? 'Yes' : 'No',
                        'value' => $client->getFsi()
                    ],
                    'remarks' => [
                        'label' => $clientRemarks[$client->getClientRemarksId()],
                        'value' => $client->getClientRemarksId()
                    ],
                    'otherRemarks' => $client->getOtherRemarks(),
                    'remarksDate' => $client->getRemarksDate(),
                ];
            }
            $session['facilitators'] = $this->resourceFacilitatorSessionRepository->listBySessionId($id);

            return $session;
        });
    }

    /**
     * @throws CacheException
     */
    public function fetchTCA1Part1(int $fieldOfficeId, Quarters $quarterData): ?array
    {
        $quarterId = $quarterData->getQuarterId();
        $params = [
            'cacheKey' => $this->cacheHelper->getSessionTCA1Part1Key($quarterId, $fieldOfficeId),
            'cacheTag' => self::CACHE_TAG

        ];

        $minMaxDate = $this->getQuarterMinMaxDate($quarterData);
        $minDate = $minMaxDate['min'];
        $maxDate = $minMaxDate['max'];

        return $this->helper->createCachedResponseCustomQuery($params, function() use ($quarterId, $fieldOfficeId, $minDate, $maxDate) {
            $conn = $this->getEntityManager()->getConnection();

            $sql = "SELECT q.*, s.session_id, sa.name as session_activity_title, s.treatment_category_id,
                       s.trees_planted, s.field_office_id,s.fsg_number , p.name as phase_name, s.batch ,v.name as venue, s.date, s.period,
                       s.is_community_service, s.is_cooperative_self_help, s.is_cooperative_self_help_activities,
                       s.is_tree_planting FROM quarters as q 
                    LEFT JOIN sessions as s ON s.date BETWEEN CAST('$minDate' AS DATE) AND CAST('$maxDate' AS DATE) 
                    LEFT JOIN session_activities as sa ON s.session_activity_id = sa.session_activity_id 
                    LEFT JOIN phases as p ON s.phase_id = p.phase_id
                    LEFT JOIN venues as v ON s.venue_id = v.venue_id
                    WHERE q.quarter_id = $quarterId
                    AND s.field_office_id = $fieldOfficeId
                    AND s.deleted_at IS NULL
                    ORDER BY p.phase_id
                ";
            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();

            return $query->fetchAllAssociative();
        });
    }

    /**
     * @throws CacheException
     */
    public function fetchTCA1Part2(int $fieldOfficeId, Quarters $quarterData): ?array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->geSessionTCA1Part2Key($quarterData->getQuarterId(), $fieldOfficeId),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponseCustomQuery($params, function () use ($quarterData, $fieldOfficeId) {
            $data = [];
            $erpFacilitators = [];
            $resourcePeopleId = [];
            $sessions = $this->getSessionDataByQuarterAndFieldOfficeId($fieldOfficeId, $quarterData);

            if (empty($sessions)) {
                return [];
            }

            $sessionsIds = array_map(fn(array $session) => intval($session['session_id']), $sessions);
            $resourceFacilitators = $this->getResourceFacilitatorIds($sessionsIds);

            foreach ($resourceFacilitators as $resourceFacilitator) {
                $type = $resourceFacilitator['resource_facilitator_type'];
                $sessionId = $resourceFacilitator['session_id'];

                if ('ERP' === $type) {
                    $erpFacilitators[$sessionId][] = [
                        'name' => $resourceFacilitator['erp_name'],
                        'role' => $resourceFacilitator['role']
                    ];
                    continue;
                }

                $resourcePeopleId[$type][$sessionId][] = [
                    'id' => (int) $resourceFacilitator['resource_facilitator_id'],
                    'role' => $resourceFacilitator['role']
                ];
            }

            foreach ($sessions as $session) {
                if (
                    isset($resourcePeopleId['VPA']) &&
                    isset($resourcePeopleId['VPA'][$session['session_id']])
                ) {
                    $session['vpa_resource_person'] = $this
                        ->getVpaResourcePeople($resourcePeopleId['VPA'][$session['session_id']]);
                }

                if (
                    isset($resourcePeopleId['PPO']) &&
                    isset($resourcePeopleId['PPO'][$session['session_id']])
                ) {
                    $session['ppo_resource_person'] = $this
                        ->getPpoResourcePeople($resourcePeopleId['PPO'][$session['session_id']]);
                }

                if (! empty($erpFacilitators) && isset($erpFacilitators[$session['session_id']])) {
                    $session['erp_resource_person'] = $erpFacilitators[$session['session_id']];
                }
                $session['count'] = $this->getClientSessionCount(
                    $quarterData->getQuarterId(),
                    intval($session['session_id'])
                );
                $session['attendees_count'] = $this->getAttendeesClientSessionCount(
                    $quarterData->getQuarterId(),
                    intval($session['session_id'])
                );
                $data[$session['session_id']] = $session;
            }

            return $data;
        });
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function fetchTCIA2(int $quarterId, int $fieldOfficeId, string $role): ?array
    {
        $conn = $this->getEntityManager()->getConnection();

        $quarterData = $this->quartersRepository->find($quarterId);

        if ($quarterData == null) {
            return null;
        }

        $quarterMonthsList = [...$this->appDateHelper->getMonthsByQuarterString($quarterData->getName())];
        $quarterYearList = [intval($quarterData->getYear())];

        $previousQuarters = $this->quartersRepository
            ->fetchPreviousQuartersByNameAndYear($quarterData->getName(), $quarterData->getYear());

        foreach ($previousQuarters as $quarter) {
            $quarterMonthsList = array_merge_recursive($quarterMonthsList, $this->appDateHelper->getMonthsByQuarterString($quarter->getName()));
            $quarterYearList[] = $quarter->getYear();
        }

        $minMaxDate = $this->appDateHelper->getMinMaxDateByYearsAndMonths($quarterYearList, $quarterMonthsList);
        $minDate = $minMaxDate['min'];
        $maxDate = $minMaxDate['max'];

        $sql = "SELECT c.last_name, c.first_name, c.middle_name, c.suffix, c.gender, c.is_pwd, c.is_senior_citizen, c.date_of_birth, cr.name as remarks,
                c.offense_category, ct.code as client_type ,c.supervision_start, c.supervision_end, p.name as phase, se.date, YEAR(se.date) as quarter_year,
                cs.other_remarks, cs.fsi
                FROM sessions as se 
                LEFT JOIN client_sessions as cs ON se.session_id = cs.session_id 
                LEFT JOIN clients as c ON cs.client_id = c.client_id 
                LEFT JOIN client_types as ct ON c.client_type_id = ct.client_type_id 
                LEFT JOIN phases as p ON se.phase_id = p.phase_id 
                LEFT JOIN client_remarks as cr ON cs.client_remarks_id = cr.client_remarks_id 
                WHERE cs.role = '$role' AND c.client_id IS NOT NULL
                  AND se.field_office_id = $fieldOfficeId
                  AND se.date BETWEEN CAST('$minDate' AS DATE) AND CAST('$maxDate' AS DATE) 
                  ORDER BY ct.code, se.session_id";

        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();
        $rows = $query->fetchAllAssociative();

        $sessions = [];
        foreach ($rows as $row) {
            $date = explode('-', $row['date']);
            $row['quarter'] = $this->appDateHelper->getQuarterByMonth(intval($date[1]));

            $sessions[] = $row;
        }

        return $sessions;
    }

    /**
     * @param Quarters $quarterData
     * @return array<int, array<string, mixed>>
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function findByQuarterData(Quarters $quarterData): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $minMaxDate = $this->quartersRepository->getQuarterMinMaxDate($quarterData);
        $minDate = $minMaxDate['min'];
        $maxDate = $minMaxDate['max'];

        $sql = "SELECT s.* FROM sessions as s WHERE s.date BETWEEN CAST('$minDate' AS DATE) AND CAST('$maxDate' AS DATE)";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();
        return $query->fetchAllAssociative();
    }

    /**
     * @param Quarters $quarterData
     * @param int|null $fieldOfficeId
     * @return int[]
     * @throws \Doctrine\DBAL\Exception
     */
    public function findSessionsIdsByQuarter(Quarters $quarterData, ?int $fieldOfficeId = null): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $minMaxDate = $this->quartersRepository->getQuarterMinMaxDate($quarterData);
        $minDate = $minMaxDate['min'];
        $maxDate = $minMaxDate['max'];

        $sql = "SELECT s.session_id FROM sessions as s WHERE s.date BETWEEN CAST('$minDate' AS DATE) AND CAST('$maxDate' AS DATE) ";

        if (null != $fieldOfficeId) {
            $sql .= "AND field_office_id = $fieldOfficeId";
        }

        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();
        $results = $query->fetchAllAssociative();

        return array_map(fn($sessionId) => $sessionId['session_id'], $results);
    }

    /**
     * @param Quarters $quarterData
     * @return int[]
     * @throws \Doctrine\DBAL\Exception
     */
    public function findFieldOfficeIdsInSessionByQuarter(Quarters $quarterData): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $minMaxDate = $this->quartersRepository->getQuarterMinMaxDate($quarterData);
        $minDate = $minMaxDate['min'];
        $maxDate = $minMaxDate['max'];

        $sql = "SELECT s.field_office_id FROM sessions as s WHERE s.date BETWEEN CAST('$minDate' AS DATE) AND CAST('$maxDate' AS DATE) ";

        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();
        $results = $query->fetchAllAssociative();

        return array_map(fn($session) => $session['field_office_id'], $results);
    }

    /**
     * @param Quarters $quarterData
     * @return int[]
     * @throws \Doctrine\DBAL\Exception
     */
    public function findFieldOfficeIdsInSessionByQuarterAndRegionId(Quarters $quarterData, int $regionId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $minMaxDate = $this->quartersRepository->getQuarterMinMaxDate($quarterData);
        $minDate = $minMaxDate['min'];
        $maxDate = $minMaxDate['max'];

        $sql = "SELECT s.field_office_id FROM sessions as s
                LEFT JOIN field_offices fo on s.field_office_id = fo.field_office_id
                WHERE fo.region_id =  $regionId
                AND s.date BETWEEN CAST('$minDate' AS DATE) AND CAST('$maxDate' AS DATE) ";

        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();
        $results = $query->fetchAllAssociative();

        return array_map(fn($session) => $session['field_office_id'], $results);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function duplicate(int $id): string
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "INSERT INTO sessions 
                    (trees_planted, field_office_id, phase_id, batch, session_activity_id, treatment_category_id, date, 
                     venue_id, period, li_lo, created_by, created_at, updated_at, deleted_at)
                SELECT trees_planted, field_office_id, phase_id, batch, session_activity_id, treatment_category_id, date, 
                       venue_id, period, li_lo, created_by, created_at, updated_at, deleted_at 
                FROM sessions WHERE session_id = $id";
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();

        return $conn->lastInsertId();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function fetchTC1FieldOfficeSummary(string $minDate, string $maxDate, int $fieldOfficeId): ?array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT tc.name as treatment_category FROM sessions as s
                    LEFT JOIN treatment_categories as tc ON s.treatment_category_id = tc.treatment_category_id
                    WHERE s.date BETWEEN CAST('$minDate' AS DATE) AND CAST('$maxDate' AS DATE)
                    AND s.field_office_id = $fieldOfficeId";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();

        return $query->fetchAllAssociative();
    }

    public function getTableIA1SummaryFormTreatmentCategoryData(
        string $minDate,
        string $maxDate,
        int $fieldOfficeId
    ): array {
        return $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT s.session_id, tc.name as treatment_category, p.name as phase FROM sessions as s
                    LEFT JOIN treatment_categories as tc ON s.treatment_category_id = tc.treatment_category_id
                    LEFT JOIN phases p on s.phase_id = p.phase_id
                    WHERE s.date BETWEEN CAST(:minDate AS DATE) AND CAST(:maxDate AS DATE)
                    AND s.field_office_id = :fieldOfficeId",
                [
                    'minDate' => $minDate,
                    'maxDate' => $maxDate,
                    'fieldOfficeId' => $fieldOfficeId,
                ]
            )->fetchAllAssociative();
    }

    public function getTableIA1SummaryFormTreatmentCategoriesData(
        string $minDate,
        string $maxDate,
        array $fieldOfficesId
    ): array {
        return $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT s.session_id, tc.name as treatment_category, p.name as phase, s.field_office_id
                        FROM sessions as s
                    LEFT JOIN treatment_categories as tc ON s.treatment_category_id = tc.treatment_category_id
                    LEFT JOIN phases p on s.phase_id = p.phase_id
                    WHERE s.date BETWEEN CAST(:minDate AS DATE) AND CAST(:maxDate AS DATE)
                    AND s.field_office_id IN (:fieldOfficesId)",
                [
                    'minDate' => $minDate,
                    'maxDate' => $maxDate,
                    'fieldOfficesId' => $fieldOfficesId,
                ],
                ['fieldOfficesId' => Connection::PARAM_INT_ARRAY]
            )->fetchAllAssociative();
    }

    public function getTableIA1SummaryFormTreatmentCategoryDataRegional(string $minDate, string $maxDate, string $fieldOfficeIds): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT s.session_id, tc.name as treatment_category FROM sessions as s 
                    LEFT JOIN treatment_categories as tc ON s.treatment_category_id = tc.treatment_category_id
                    WHERE s.date BETWEEN CAST('$minDate' AS DATE) AND CAST('$maxDate' AS DATE)
                    AND s.field_office_id IN ($fieldOfficeIds)";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();

        return $query->fetchAllAssociative();
    }

    public function findWithActivitiesByIds(array $ids): array
    {
        return $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT s.*, sa.name, sa.phase_id, s.is_tree_planting, s.is_cooperative_self_help_activities,
                        s.is_cooperative_self_help, s.is_community_service FROM sessions s
                    LEFT JOIN session_activities sa on s.session_activity_id = sa.session_activity_id
                    WHERE s.session_id IN (:sessionIds)",
                ['sessionIds' => $ids],
                ['sessionIds' => Connection::PARAM_INT_ARRAY]
            )->fetchAllAssociative();
    }

    public function getVpaFacilitatorsByIds(array $ids)
    {
        return $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT s.field_office_id, rfs.resource_facilitator_id FROM sessions s
                    LEFT JOIN resource_facilitator_session rfs on s.session_id = rfs.session_id
                    WHERE s.session_id IN (:sessionIds) AND rfs.resource_facilitator_type = :type",
                [
                    'sessionIds' => $ids,
                    'type' => 'VPA'
                ],
                ['sessionIds' => Connection::PARAM_INT_ARRAY]
            )->fetchAllAssociative();
    }

    /**
     * @throws NonUniqueResultException
     */
    private function isExisting(SessionsModel $sessionData): bool
    {
        $date = explode('-', $sessionData->getDate());
        $quarterName = $this->appDateHelper->getQuarterByMonth(intval($date[1]));
        $months = $this->appDateHelper->getMonthsByQuarterString($quarterName);
        $minMaxDate = $this->appDateHelper->getMinMaxDateByYearsAndMonths([$date[0]], $months);

        $session = $this->createQueryBuilder('se')
            ->select('se.sessionId')
            ->where("se.date BETWEEN CAST(:minDate AS DATE) AND CAST(:maxDate AS DATE)")
            ->andWhere('se.phaseId = :phaseId')
            ->andWhere('se.sessionActivityId = :sessionActivityId')
            ->andWhere('se.treatmentCategoryId = :treatmentCategoryId')
            ->andWhere('se.deletedAt IS NULL')
            ->setMaxResults(1)
            ->setParameter('minDate', $minMaxDate['min'], 'string')
            ->setParameter('maxDate', $minMaxDate['max'], 'string')
            ->setParameter('phaseId', $sessionData->getPhaseId())
            ->setParameter('treatmentCategoryId', $sessionData->getTreatmentCategoryId())
            ->setParameter('sessionActivityId', $sessionData->getSessionActivityId())
            ->getQuery()
            ->getOneOrNullResult();

        return $session != null;
    }

    private function isConflicted(Sessions $fetchedSession, SessionsModel $sessionData): bool
    {
        // Fetched and input client is the same.
        // It is trying to update itself.
        if (
            $fetchedSession->getPhaseId() === $sessionData->getPhaseId() &&
            $fetchedSession->getSessionActivityId() === $sessionData->getSessionActivityId()
        ) {
            return false;
        }

        // There is an existing record in the database.
        // It is trying to update another record that existing in the database.
        if ($this->isExisting($sessionData)) {
            return true;
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    private function getAllClientTypes(): array
    {
        $data = [];
        $clientTypes = $this->clientTypesRepository->findAll();

        foreach ($clientTypes as $clientType) {
            $data[strtoupper($clientType->getCode())] = [
                $clientType->getClientTypeId(),
                $clientType->getDescription()
            ];
        }

        return $data;
    }

    /**
     * @return array<int, string>
     */
    private function geClientNames(): array
    {
        $data = [];

        $clients = $this->clientsRepository->findAll();
        foreach ($clients as $client) {
            $data[$client->getClientId()] = $client->getLastName() . ', ' . $client->getFirstName() . ' ' . $client->getMiddleName();
        }

        return $data;
    }

    /**
     * @return array<int, string>
     */
    private function geClientRemarks(): array
    {
        $data = [];

        $clientRemarks = $this->clientRemarksRepository->findAll();
        foreach ($clientRemarks as $clientRemark) {
            $data[$clientRemark->getClientRemarksId()] = $clientRemark->getName();
        }

        return $data;
    }

    public function getQuarterMinMaxDate(Quarters $quarterData): array
    {
        $quarterMonthsList = [...$this->appDateHelper->getMonthsByQuarterString($quarterData->getName())];
        $quarterYearList = [intval($quarterData->getYear())];

        return $this->appDateHelper->getMinMaxDateByYearsAndMonths($quarterYearList, $quarterMonthsList);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getSessionDataByQuarterAndFieldOfficeId(int $fieldOfficeId, Quarters $quarterData): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $minMaxDate = $this->getQuarterMinMaxDate($quarterData);
        $minDate = $minMaxDate['min'];
        $maxDate = $minMaxDate['max'];

        $sql = "SELECT s.session_id, s.field_office_id, s.li_lo FROM sessions as s
                WHERE s.date BETWEEN CAST('$minDate' AS DATE)
                AND CAST('$maxDate' AS DATE)
                AND s.field_office_id = $fieldOfficeId
                AND s.deleted_at IS NULL";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();
        return $query->fetchAllAssociative();
    }

    /**
     * @param int[] $sessionIds
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    private function getResourceFacilitatorIds(array $sessionIds): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sessionIds = implode(',', $sessionIds);
        $sql = "SELECT * FROM resource_facilitator_session WHERE session_id IN ($sessionIds)";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();

        return $query->fetchAllAssociative();
    }

    /**
     * @param array<string, mixed> $volunteerData
     * @return array
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function getVpaResourcePeople(array $volunteerData): array
    {
        $roles = [];
        $volunteerIds = [];
        foreach ($volunteerData as $volunteerDatum) {
            $roles[$volunteerDatum['id']] = $volunteerDatum['role'];

            if (! in_array($volunteerDatum['id'], $volunteerIds)) {
                $volunteerIds[] = $volunteerDatum['id'];
            }
        }

        $conn = $this->getEntityManager()->getConnection();
        $volunteerIds = implode(',', $volunteerIds);
        $sql = "SELECT DISTINCT v.first_name, v.middle_name, v.last_name, v.suffix, v.volunteer_id
                FROM volunteer as v WHERE v.volunteer_id IN ($volunteerIds) ORDER BY v.first_name";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();
        $data = $query->fetchAllAssociative();

        foreach ($data as $index=>$row) {
            $data[$index]['role'] = $roles[$row['volunteer_id']];
            $data[$index]['full_name'] = $row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name'] . ' ' . $row['suffix'];
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $userData
     * @return array
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function getPpoResourcePeople(array $userData): array
    {
        $roles = [];
        $userIds = [];
        foreach ($userData as $userDatum) {
            $roles[$userDatum['id']] = $userDatum['role'];

            if (! in_array($userDatum['id'], $userIds)) {
                $userIds[] = $userDatum['id'];
            }
        }
        $conn = $this->getEntityManager()->getConnection();
        $userIds = implode(',', $userIds);
        $sql = "SELECT DISTINCT ud.first_name, ud.middle_name, ud.last_name, ud.suffix, ud.user_account_id
                FROM pmeis.user_details as ud WHERE ud.user_account_id IN ($userIds) ORDER BY ud.first_name";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();
        $data = $query->fetchAllAssociative();

        foreach ($data as $index=>$row) {
            $data[$index]['role'] = $roles[$row['user_account_id']];
            $data[$index]['full_name'] = $row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name'] . ' ' . $row['suffix'];
        }

        return $data;
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function getClientSessionCount(int $id, int $sessionId): array|bool
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT s.session_id, s.field_office_id, s.li_lo,
                        (SELECT COUNT(client_session_id) FROM client_sessions WHERE role = 'PS' AND client_sessions.session_id = s.session_id) as parolees,
                        (SELECT COUNT(client_session_id) FROM client_sessions WHERE role = 'PR' AND client_sessions.session_id = s.session_id) as probationers,
                        (SELECT COUNT(client_session_id) FROM client_sessions WHERE role = 'PD' AND client_sessions.session_id = s.session_id) as pardonees,
                        (SELECT COUNT(client_session_id) FROM client_sessions WHERE role = 'JICL' AND client_sessions.session_id = s.session_id) as jicl,
                        (SELECT COUNT(client_session_id) FROM client_sessions WHERE role = 'FTMDO' AND client_sessions.session_id = s.session_id) as ftmdo,
                        (SELECT COUNT(client_session_id) FROM client_sessions WHERE role = 'PET' AND client_sessions.session_id = s.session_id) as petitioners,
                        (SELECT COUNT(client_session_id) FROM client_sessions WHERE role = 'TERM' AND client_sessions.session_id = s.session_id) as `terminated`
                        FROM sessions as s
                LEFT JOIN quarters as q ON q.quarter_id = $id
                WHERE s.session_id = $sessionId ORDER BY s.session_id";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();
        return $query->fetchAssociative();
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function getAttendeesClientSessionCount(int $id, int $sessionId): array|bool
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT s.session_id, s.field_office_id, s.li_lo,
                        (SELECT COUNT(client_session_id) FROM client_sessions WHERE role = 'PS' AND client_sessions.session_id = s.session_id AND client_sessions.client_remarks_id IS NULL) as parolees,
                        (SELECT COUNT(client_session_id) FROM client_sessions WHERE role = 'PR' AND client_sessions.session_id = s.session_id AND client_sessions.client_remarks_id IS NULL) as probationers,
                        (SELECT COUNT(client_session_id) FROM client_sessions WHERE role = 'PD' AND client_sessions.session_id = s.session_id AND client_sessions.client_remarks_id IS NULL) as pardonees,
                        (SELECT COUNT(client_session_id) FROM client_sessions WHERE role = 'JICL' AND client_sessions.session_id = s.session_id AND client_sessions.client_remarks_id IS NULL) as jicl,
                        (SELECT COUNT(client_session_id) FROM client_sessions WHERE role = 'FTMDO' AND client_sessions.session_id = s.session_id AND client_sessions.client_remarks_id IS NULL) as ftmdo,
                        (SELECT COUNT(client_session_id) FROM client_sessions WHERE role = 'PET' AND client_sessions.session_id = s.session_id AND client_sessions.client_remarks_id IS NULL) as petitioners,
                        (SELECT COUNT(client_session_id) FROM client_sessions WHERE role = 'TERM' AND client_sessions.session_id = s.session_id AND client_sessions.client_remarks_id IS NULL) as `terminated`
                        FROM sessions as s
                LEFT JOIN quarters as q ON q.quarter_id = $id
                WHERE s.session_id = $sessionId ORDER BY s.session_id";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();
        return $query->fetchAssociative();
    }
}
