<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\Quarters;
use App\Enum\Response as ResponseEnum;
use App\Model\Quarters as QuartersModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Psr\Cache\CacheException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method Quarters|null find($id, $lockMode = null, $lockVersion = null)
 * @method Quarters|null findOneBy(array $criteria, array $orderBy = null)
 * @method Quarters[]    findAll()
 * @method Quarters[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuartersRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "quarters";
    protected const SESSION_CACHE_TAG = "sessions";

    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private AppDateHelper $appDateHelper,
        private Helper $helper,
    ){
        parent::__construct($registry, Quarters::class);
    }

    /**
     * @throws NonUniqueResultException
     * @throws InvalidArgumentException
     * @throws ORMException|\Psr\Cache\InvalidArgumentException
     */
    public function create(QuartersModel $quarterData): int|null
    {
        if ($this->isExisting($quarterData)) {
            return null;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $quarter = new Quarters();
        $quarter->setName($quarterData->getName());
        $quarter->setYear($quarterData->getYear());
        $quarter->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($quarter);
        $this->getEntityManager()->flush();

        return $quarter->getQuarterId();
    }

    /**
     * @return Quarters[]
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws CacheException
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllQuartersKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function() {
            return $this->createQueryBuilder('qtr')
                ->orderBy('qtr.quarterId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @throws ORMException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function delete(int $id): bool
    {
        $quarter = $this->isExistingById($id);

        if (! $quarter) {
            return false;
        }

        // TODO: Check if there is an existing id in sessions

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->getEntityManager()->remove($quarter);
        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws Exception
     */
    public function update(int $id, QuartersModel $quarterData): string
    {
        $quarter = $this->isExistingById($id);

        if (! $quarter) {
            return ResponseEnum::NO_RECORD;
        }

        if ($this->isConflicted($quarter, $quarterData)) {
            return ResponseEnum::CONFLICTED_INPUT;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $quarter->setName($quarterData->getName());
        $quarter->setYear($quarterData->getYear());

        $this->getEntityManager()->flush();

        return ResponseEnum::OK;
    }

    public function isExistingById(int $id): bool | Quarters
    {
        $quarter = $this->find($id);

        return ($quarter == null) ? false : $quarter;
    }

    /**
     * @param int $page
     * @param int $pageSize
     * @return array<string, mixed>
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws CacheException
     */
    public function paginated(int $page = 1, int $pageSize = 10): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getQuartersPaginatedKey($page, $pageSize),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG,
            'pageSize' => $pageSize,
            'page' => $page
        ];

        return $this->helper->createPaginatedResponse($params, function() {
            return $this->createQueryBuilder('qtr')->orderBy('qtr.quarterId');
        });
    }

    /**
     * @param string $field
     * @param string $query
     * @param int $page
     * @param int $pageSize
     * @return array<string, mixed>
     * @throws CacheException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function paginatedSearch(string $field, string $query, int $page = 1, int $pageSize = 10): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getQuartersPaginatedSearchKey($field, $query, $page, $pageSize),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG,
            'pageSize' => $pageSize,
            'page' => $page
        ];

        return $this->helper->createPaginatedResponse($params, function()  use ($field, $query) {
            return $this->createQueryBuilder('qtr')
                ->where("qtr.$field LIKE :query")
                ->setParameter(':query', '%'. $query . '%')
                ->orderBy('qtr.quarterId');
        });
    }

    public function isExisting(QuartersModel $quarterData): bool
    {
        $quarter = $this->findOneBy([
            'name' => $quarterData->getName(),
            'year' => $quarterData->getYear()
        ]);

        return $quarter != null;
    }

    public function isConflicted(Quarters $fetchedQuarter, QuartersModel $quarterData): bool
    {
        if (
            $fetchedQuarter->getName() === $quarterData->getName() &&
            $fetchedQuarter->getYear() === $quarterData->getYear()
        ) {
            return false;
        }

        if ($this->isExisting($quarterData)) {
            return true;
        }

        return false;
    }

    /**
     * @throws CacheException
     */
    public function fetchTCA1Part1(int $id, int $fieldOfficeId): ?array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getQuartersTCA1Part1Key($id, $fieldOfficeId),
            'cacheTag' => self::SESSION_CACHE_TAG

        ];

        $quarterData = $this->find($id);

        if ($quarterData === null) {
            return null;
        }

        $minMaxDate = $this->getQuarterMinMaxDate($quarterData);
        $minDate = $minMaxDate['min'];
        $maxDate = $minMaxDate['max'];

        return $this->helper->createCachedResponseCustomQuery($params, function() use ($id, $fieldOfficeId, $minDate, $maxDate) {
            $conn = $this->getEntityManager()->getConnection();

            $sql = "SELECT q.*, s.session_id, sa.name as session_activity_title, s.treatment_category_id, sr.name as remarks, s.remarks_id,
                       s.trees_planted, s.field_office_id, p.name as phase_name, s.batch ,v.name as venue, s.date, s.period FROM quarters as q 
                    LEFT JOIN sessions as s ON s.date BETWEEN CAST('$minDate' AS DATE) AND CAST('$maxDate' AS DATE) 
                    LEFT JOIN session_activities as sa ON s.session_activity_id = sa.session_activity_id 
                    LEFT JOIN phases as p ON s.phase_id = p.phase_id
                    LEFT JOIN venues as v ON s.venue_id = v.venue_id
                    LEFT JOIN session_remarks as sr ON s.remarks_id = sr.session_remark_id
                    WHERE q.quarter_id = $id AND s.field_office_id = $fieldOfficeId ORDER BY p.phase_id";
            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();

            return $query->fetchAllAssociative();
        });
    }

    /**
     * @throws CacheException
     */
    public function fetchTCA1Part2(int $quarterId, int $fieldOfficeId): ?array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getQuartersTCA1Part2Key($quarterId, $fieldOfficeId),
            'cacheTag' => self::SESSION_CACHE_TAG
        ];

        return $this->helper->createCachedResponseCustomQuery($params, function() use ($quarterId, $fieldOfficeId) {
            $data = [];
            $erpFacilitators = [];
            $resourcePeopleId = [];
            $sessions = $this->getSessionDataByQuarterAndFieldOfficeId($quarterId, $fieldOfficeId);
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
                $session['vpa_resource_person'] = $this->getVpaResourcePeople($resourcePeopleId['VPA'][$session['session_id']]);
                $session['ppo_resource_person'] = $this->getPpoResourcePeople($resourcePeopleId['PPO'][$session['session_id']]);
                $session['erp_resource_person'] = $erpFacilitators[$session['session_id']];
                $session['count'] = $this->getClientSessionCount($quarterId, intval($session['session_id']));
                $data[$session['session_id']] = $session;
            }

            return $data;
        });
    }

    /**
     * @param string $name
     * @param string $year
     * @return Quarters[]
     */
    public function fetchPreviousQuartersByNameAndYear(string $name, string $year): array
    {
        if ($name === 'FIRST') {
            return [];
        }

        $quarters = [];
        $convertedNumberQuarters = ['FIRST', 'SECOND', 'THIRD', 'FOURTH'];
        $quarterIndex = array_search($name, $convertedNumberQuarters);

        for ($quarterId = $quarterIndex + 1; $quarterId > 0 ; $quarterId--) {
            $quarter = $this->fetchByNameAndYear($convertedNumberQuarters[$quarterId-1], $year);
            if ($quarter != null) {
                $quarters[] = $quarter;
            }
        }

        return $quarters;
    }

    public function fetchPreviousQuarterByNameAndYear(string $name, string $year): ?Quarters
    {
        $namedQuarters = ['FIRST', 'SECOND', 'THIRD', 'FOURTH'];
        $quarterIndex = array_search($name, $namedQuarters);
        $previousQuarterIndex = $quarterIndex > 0 ? $quarterIndex - 1 : 3;
        $year =  $quarterIndex > 0 ? $year : strval(intval($year) - 1);
        $name = $namedQuarters[$previousQuarterIndex];

        return $this->fetchByNameAndYear($name, $year);
    }

    public function fetchByNameAndYear(string $name, string $year): ?Quarters
    {
        return $this->findOneBy([
            'name' => $name,
            'year' => $year
        ]);
    }

    /**
     * @param int[] $sessionIds
     * @return array
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function getResourceFacilitatorIds(array $sessionIds): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sessionIds = implode(',', $sessionIds);
        $sql = "SELECT rfs.*  FROM resource_facilitator_session as rfs WHERE rfs.session_id IN ($sessionIds)";
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
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getSessionDataByQuarterAndFieldOfficeId(int $id, int $fieldOfficeId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $quarterData = $this->find($id);

        if ($quarterData === null) {
            return [];
        }

        $minMaxDate = $this->getQuarterMinMaxDate($quarterData);
        $minDate = $minMaxDate['min'];
        $maxDate = $minMaxDate['max'];

        $sql = "SELECT s.session_id, s.field_office_id, s.li_lo FROM sessions as s 
                    WHERE s.date BETWEEN CAST('$minDate' AS DATE) AND CAST('$maxDate' AS DATE) AND s.field_office_id = $fieldOfficeId";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();
        return $query->fetchAllAssociative();
    }

    public function getQuarterMinMaxDate(Quarters $quarterData): array
    {
        $quarterMonthsList = [...$this->appDateHelper->getMonthsByQuarterString($quarterData->getName())];
        $quarterYearList = [intval($quarterData->getYear())];

        return $this->appDateHelper->getMinMaxDateByYearsAndMonths($quarterYearList, $quarterMonthsList);
    }
}
