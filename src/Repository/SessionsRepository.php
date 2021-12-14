<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\Sessions;
use App\Enum\Response as ResponseEnum;
use App\Model\Sessions as SessionsModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
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
        ManagerRegistry $registry,
        private AppDateHelper $appDateHelper,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private Helper $helper,
        private ClientSessionsRepository $clientSessionsRepository,
        private ResourceFacilitatorSessionRepository $resourceFacilitatorSessionRepository,
    ){
        parent::__construct($registry, Sessions::class);
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function create(SessionsModel $sessionData): int | null
    {
        $isExist = $this->isExisting($sessionData);

        if ($isExist) {
            return null;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $session = new Sessions();
        $session->setQuarterId($sessionData->getQuarterId());
        $session->setFieldOfficeId($sessionData->getFieldOfficeId());
        $session->setPhaseId($sessionData->getPhaseId());
        $session->setBatch($sessionData->getBatch());
        $session->setSessionActivityId($sessionData->getSessionActivityId());
        $session->setTreatmentCategoryId($sessionData->getTreatmentCategoryId());
        $session->setDate($this->appDateHelper->convertStringToImmutableDate($sessionData->getDate()));
        $session->setVenueId($sessionData->getVenueId());
        $session->setPeriod($sessionData->getPeriod());
        $session->setRemarks($sessionData->getRemarks());
        $session->setFsg($sessionData->getFsg());
        $session->setLiLo($sessionData->getLiLo());
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
    public function createWithClientsAndFacilitators(SessionsModel $sessionData): int | null
    {
        if ($this->isExisting($sessionData)) {
            return null;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $session = new Sessions();
        $session->setQuarterId($sessionData->getQuarterId());
        $session->setFieldOfficeId($sessionData->getFieldOfficeId());
        $session->setPhaseId($sessionData->getPhaseId());
        $session->setBatch($sessionData->getBatch());
        $session->setSessionActivityId($sessionData->getSessionActivityId());
        $session->setTreatmentCategoryId($sessionData->getTreatmentCategoryId());
        $session->setDate($this->appDateHelper->convertStringToImmutableDate($sessionData->getDate()));
        $session->setVenueId($sessionData->getVenueId());
        $session->setPeriod($sessionData->getPeriod());
        $session->setRemarks($sessionData->getRemarks());
        $session->setFsg($sessionData->getFsg());
        $session->setLiLo($sessionData->getLiLo());
        $session->setCreatedBy($sessionData->getCreatedBy());
        $session->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($session);
        $this->getEntityManager()->flush();

        $this->clientSessionsRepository->batchCreate($session->getSessionId(), $sessionData->getClientSession());
        $this->resourceFacilitatorSessionRepository->batchCreate($session->getSessionId(), $sessionData->getResourceFacilitator());

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

        return $this->helper->createCachedResponseCustomQuery($params, function() {
            $conn = $this->getEntityManager()->getConnection();
            $sql = "SELECT se.*, q.name as quarter_name, q.year as quarter_year, fe.name as field_office_name,
                    p.name as phase_name, sa.name as session_activity_name, tc.name as treatment_category_name, v.name as venue_name
                 FROM sessions as se " .
                "LEFT JOIN quarters as q ON se.quarter_id = q.quarter_id " .
                "LEFT JOIN field_offices as fe ON se.field_office_id = fe.field_office_id " .
                "LEFT JOIN phases as p ON se.field_office_id = p.phase_id " .
                "LEFT JOIN session_activities as sa ON se.session_activity_id = sa.session_activity_id " .
                "LEFT JOIN treatment_categories as tc ON se.treatment_category_id = tc.treatment_category_id " .
                "LEFT JOIN venues as v ON se.venue_id = v.venue_id " .
                "WHERE se.deleted_at IS NULL ORDER BY se.session_id DESC";
            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();

            return $query->fetchAllAssociative();
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

        return $this->helper->createCachedResponse($params, function() {
            $conn = $this->getEntityManager()->getConnection();
            $data = [];

            $sql = "SELECT se.*, q.name as quarter_name, q.year as quarter_year, fo.name as field_office_name,
                    p.name as phase_name, sa.name as session_activity_name, tc.name as treatment_category_name, v.name as venue_name
                 FROM sessions as se " .
                "LEFT JOIN quarters as q ON se.quarter_id = q.quarter_id " .
                "LEFT JOIN field_offices as fo ON se.field_office_id = fo.field_office_id " .
                "LEFT JOIN phases as p ON se.field_office_id = p.phase_id " .
                "LEFT JOIN session_activities as sa ON se.session_activity_id = sa.session_activity_id " .
                "LEFT JOIN treatment_categories as tc ON se.treatment_category_id = tc.treatment_category_id " .
                "LEFT JOIN venues as v ON se.venue_id = v.venue_id " .
                "WHERE se.deleted_at IS NULL ORDER BY se.session_id DESC";
            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();

            $sessions = $query->fetchAllAssociative();

            foreach ($sessions as $session) {
                $session['client_session'] = $this->clientSessionsRepository->listBySessionId((int)$session['session_id']);
                $session['resource_facilitator'] = $this->resourceFacilitatorSessionRepository->listBySessionId((int)$session['session_id']);

                $data[] = $session;
            }

            return $data;
        });
    }

    /**
     * @throws OptimisticLockException
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws ORMException
     * @throws NonUniqueResultException
     */
    public function delete(int $id): bool
    {
        $session = $this->isExistingById($id);

        if (! $session) {
            return false;
        }

        // TODO: check if existing in client sessions, resource facilitator sessions

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->getEntityManager()->remove($session);
        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function softDelete(int $id): bool
    {
        $session = $this->isExistingById($id);

        if (! $session) {
            return false;
        }

        // TODO: check if existing in client sessions, resource facilitator sessions

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $session->setDeletedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @throws NonUniqueResultException
     * @throws InvalidArgumentException
     * @throws Exception
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function update(int $id, SessionsModel $sessionData): string
    {
        $session = $this->isExistingById($id);

        if (! $session) {
            return ResponseEnum::NO_RECORD;
        }

        if ($this->isConflicted($session, $sessionData)) {
            return ResponseEnum::CONFLICTED_INPUT;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $session->setQuarterId($sessionData->getQuarterId());
        $session->setPhaseId($sessionData->getPhaseId());
        $session->setBatch($sessionData->getBatch());
        $session->setSessionActivityId($sessionData->getSessionActivityId());
        $session->setFieldOfficeId($sessionData->getFieldOfficeId());
        $session->setTreatmentCategoryId($sessionData->getTreatmentCategoryId());
        $session->setDate($this->appDateHelper->convertStringToImmutableDate($sessionData->getDate()));
        $session->setVenueId($sessionData->getVenueId());
        $session->setPeriod($sessionData->getPeriod());
        $session->setRemarks($sessionData->getRemarks());
        $session->setFsg($sessionData->getFsg());
        $session->setLiLo($sessionData->getLiLo());
        $session->setCreatedBy($sessionData->getCreatedBy());
        $session->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

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

        if (! $session) {
            return ResponseEnum::NO_RECORD;
        }

        if ($this->isConflicted($session, $sessionData)) {
            return ResponseEnum::CONFLICTED_INPUT;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $session->setQuarterId($sessionData->getQuarterId());
        $session->setPhaseId($sessionData->getPhaseId());
        $session->setBatch($sessionData->getBatch());
        $session->setSessionActivityId($sessionData->getSessionActivityId());
        $session->setFieldOfficeId($sessionData->getFieldOfficeId());
        $session->setTreatmentCategoryId($sessionData->getTreatmentCategoryId());
        $session->setDate($this->appDateHelper->convertStringToImmutableDate($sessionData->getDate()));
        $session->setVenueId($sessionData->getVenueId());
        $session->setPeriod($sessionData->getPeriod());
        $session->setRemarks($sessionData->getRemarks());
        $session->setFsg($sessionData->getFsg());
        $session->setLiLo($sessionData->getLiLo());
        $session->setCreatedBy($sessionData->getCreatedBy());
        $session->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->flush();

        $this->clientSessionsRepository->deleteBySessionId($session->getSessionId());
        $this->clientSessionsRepository->batchCreate($session->getSessionId(), $sessionData->getClientSession());

        $this->resourceFacilitatorSessionRepository->deleteBySessionId($session->getSessionId());
        $this->resourceFacilitatorSessionRepository->batchCreate($session->getSessionId(), $sessionData->getResourceFacilitator());

        return ResponseEnum::OK;
    }

    public function isExistingById(int $id): bool | Sessions
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

        return $this->helper->createPaginatedResponseCustomQuery($params, function() use ($pageSize, $page) {
            $startOffset = $pageSize * ($page-1);
            $result = [];

            $conn = $this->getEntityManager()->getConnection();
            $sql = "SELECT se.*, q.name as quarter_name, q.year as quarter_year, fe.name as field_office_name,
                    p.name as phase_name, sa.name as session_activity_name, tc.name as treatment_category_name, v.name as venue_name
                 FROM sessions as se " .
                "LEFT JOIN quarters as q ON se.quarter_id = q.quarter_id " .
                "LEFT JOIN field_offices as fe ON se.field_office_id = fe.field_office_id " .
                "LEFT JOIN phases as p ON se.field_office_id = p.phase_id " .
                "LEFT JOIN session_activities as sa ON se.session_activity_id = sa.session_activity_id " .
                "LEFT JOIN treatment_categories as tc ON se.treatment_category_id = tc.treatment_category_id " .
                "LEFT JOIN venues as v ON se.venue_id = v.venue_id " .
                "WHERE se.deleted_at IS NULL " .
                "LIMIT $pageSize OFFSET $startOffset";
            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();
            $result['data'] = $query->fetchAllAssociative();
            $result['totalItems'] = count($this->findBy([
                'deletedAt' => null
            ]));

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
        $params = [
            'cacheKey' => $this->cacheHelper->getSessionsById($id),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponseCustomQuery($params, function() use ($id) {
            $conn = $this->getEntityManager()->getConnection();

            $sql = "SELECT se.*, q.name as quarter_name, q.year as quarter_year, fe.name as field_office_name,
                    p.name as phase_name, sa.name as session_activity_name, tc.name as treatment_category_name, v.name as venue_name,
                    r.name
                 FROM sessions as se " .
                "LEFT JOIN quarters as q ON se.quarter_id = q.quarter_id " .
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

            $session['client_session'] = $this->clientSessionsRepository->listBySessionId($id);
            $session['resource_facilitator'] = $this->resourceFacilitatorSessionRepository->listBySessionId($id);

            return $session;
        });
    }

    private function isExisting(SessionsModel $sessionData): bool
    {
        $session = $this->findOneBy([
            'quarterId' => $sessionData->getQuarterId(),
            'phaseId' => $sessionData->getPhaseId(),
            'sessionActivityId' => $sessionData->getSessionActivityId(),
            'deletedAt' => null
        ]);

        return $session != null;
    }

    private function isConflicted(Sessions $fetchedSession, SessionsModel $sessionData): bool
    {
        // Fetched and input client is the same.
        // It is trying to update itself.
        if (
            $fetchedSession->getQuarterId() === $sessionData->getQuarterId() &&
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
}
