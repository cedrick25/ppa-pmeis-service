<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\ResourceFacilitatorSession;
use App\Enum\Response as ResponseEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use App\Model\ResourceFacilitatorSession as ResourceFacilitatorSessionModel;
use Doctrine\Persistence\Mapping\MappingException;
use Exception;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use App\Enum\ResourceFacilitatorType;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method ResourceFacilitatorSession|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResourceFacilitatorSession|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResourceFacilitatorSession[]    findAll()
 * @method ResourceFacilitatorSession[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResourceFacilitatorSessionRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "resource_facilitator_sessions";

    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper            $cacheHelper,
        private Helper                 $helper,
        private QuartersRepository     $quartersRepository,
        private AppDateHelper          $appDateHelper,
    ){
        parent::__construct($registry, ResourceFacilitatorSession::class);
    }

    /**
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws ORMException
     */
    public function create(ResourceFacilitatorSessionModel $resourceFacilitatorSessionData): int | null
    {
        if ($this->isExisting($resourceFacilitatorSessionData)) {
            return null;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $newResourceFacilitatorSession = new ResourceFacilitatorSession();
        $newResourceFacilitatorSession->setSessionId($resourceFacilitatorSessionData->getSessionId());
        $newResourceFacilitatorSession->setResourceFacilitatorId($resourceFacilitatorSessionData->getResourceFacilitatorId());
        $newResourceFacilitatorSession->setResourceFacilitatorType($resourceFacilitatorSessionData->getResourceFacilitatorType());

        $this->getEntityManager()->persist($newResourceFacilitatorSession);
        $this->getEntityManager()->flush();

        return $newResourceFacilitatorSession->getResourceFacilitatorSessionId();
    }

    /**
     * @param int $sessionId
     * @param array<string, int[]> $resourceFacilitatorSessionIds
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws ORMException
     * @throws MappingException
     * @throws InvalidArgumentException
     */
    public function batchCreate(int $sessionId, array $resourceFacilitatorSessionIds): void
    {
        foreach ($resourceFacilitatorSessionIds as $type => $resourceFacilitatorIds) {
            foreach ($resourceFacilitatorIds as $resourceFacilitatorId) {
                $clientSession = new ResourceFacilitatorSession();
                $clientSession->setSessionId($sessionId);
                $clientSession->setResourceFacilitatorId($resourceFacilitatorId);
                $clientSession->setResourceFacilitatorType(strtoupper($type));

                $this->getEntityManager()->persist($clientSession);
            }
        }

        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear(ResourceFacilitatorSession::class);

        $this->cache->invalidateTags([self::CACHE_TAG]);
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function deleteBySessionId(int $sessionId): void
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "DELETE FROM resource_facilitator_session WHERE session_id = $sessionId";
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();
    }

    /**
     * @return ResourceFacilitatorSession[]
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllResourceFacilitatorSessionsKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function() {
            return $this->createQueryBuilder('rfs')
                ->orderBy('rfs.resourceFacilitatorSessionId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @return array<string, mixed>
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function listBySessionId(int $id): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllResourceFacilitatorSessionsBySessionIdKey($id),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function() use ($id) {
            $data = [];

            $facilitators = $this->createQueryBuilder('rfs')
                ->where('rfs.sessionId = :id')
                ->setParameter('id', $id)
                ->orderBy('rfs.resourceFacilitatorSessionId', 'DESC')
                ->getQuery()
                ->getArrayResult();

            foreach ($facilitators as $facilitator) {
                if (!isset($data[$facilitator['resourceFacilitatorType']])) {
                    $data[$facilitator['resourceFacilitatorType']] = [$facilitator['resourceFacilitatorId']];
                    continue;
                }

                array_push($data[$facilitator['resourceFacilitatorType']], $facilitator['resourceFacilitatorId']);
            }

            return $data;
        });
    }

    /**
     * @throws InvalidArgumentException
     * @throws ORMException
     */
    public function delete(int $id): bool
    {
        $resourceFacilitatorSession = $this->isExistingById($id);

        if (! $resourceFacilitatorSession) {
            return false;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->getEntityManager()->remove($resourceFacilitatorSession);
        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function update(int $id, ResourceFacilitatorSessionModel $resourceFacilitatorSessionData): string
    {
        $resourceFacilitatorSession = $this->isExistingById($id);

        if (! $resourceFacilitatorSession) {
            return ResponseEnum::NO_RECORD;
        }

        if ($this->isConflicted($resourceFacilitatorSession, $resourceFacilitatorSessionData)) {
            return ResponseEnum::CONFLICTED_INPUT;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $resourceFacilitatorSession->setSessionId($resourceFacilitatorSessionData->getSessionId());
        $resourceFacilitatorSession->setResourceFacilitatorId($resourceFacilitatorSessionData->getResourceFacilitatorId());
        $resourceFacilitatorSession->setResourceFacilitatorType($resourceFacilitatorSessionData->getResourceFacilitatorType());

        $this->getEntityManager()->flush();

        return ResponseEnum::OK;

    }

    /**
     * @param int $page
     * @param int $pageSize
     * @return array<string, mixed>
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function paginated(int $page = 1, int $pageSize = 10): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getResourceFacilitatorSessionsPaginatedKey($page, $pageSize),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG,
            'pageSize' => $pageSize,
            'page' => $page
        ];

        return $this->helper->createPaginatedResponse($params, function() {
            return $this->createQueryBuilder('rfs')->orderBy('rfs.resourceFacilitatorSessionId');
        });
    }

    private function isExisting(ResourceFacilitatorSessionModel $resourceFacilitatorSessionData): bool
    {
        $client = $this->findOneBy([
            'sessionId' => $resourceFacilitatorSessionData->getSessionId(),
            'resourceFacilitatorId' => $resourceFacilitatorSessionData->getResourceFacilitatorId(),
            'resourceFacilitatorType' => $resourceFacilitatorSessionData->getResourceFacilitatorType()
        ]);

        return $client != null;
    }

    public function isExistingById(int $id): bool | ResourceFacilitatorSession
    {
        $client = $this->findOneBy([
            'resourceFacilitatorSessionId' => $id
        ]);

        return ($client == null) ? false : $client;
    }

    /**
     * @param string $quarterName
     * @param int $quarterYear
     * @return  array<int, array<string, mixed>>
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getVolunteerIdsByQuarter(string $quarterName, int $quarterYear): array
    {
        $quarterMonthsList = [...$this->appDateHelper->getMonthsByQuarterString($quarterName)];
        $quarterYearList = [$quarterYear];
        $minMaxDate = $this->appDateHelper->getMinMaxDateByYearsAndMonths($quarterYearList, $quarterMonthsList);
        $minDate = $minMaxDate['min'];
        $maxDate = $minMaxDate['max'];

        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT rfs.resource_facilitator_id, rfs.resource_facilitator_type, s.session_id FROM sessions as s
                LEFT JOIN resource_facilitator_session as rfs ON s.session_id = rfs.session_id
                WHERE rfs.resource_facilitator_type = 'VPA' AND s.date BETWEEN CAST('$minDate' AS DATE) AND CAST('$maxDate' AS DATE)";

        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();

        return $query->fetchAllAssociative();
    }

    /**
     * @param int $fieldOfficeId
     * @param string $quarterName
     * @param int $quarterYear
     * @return  array<int, array<string, mixed>>
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getVolunteerIdsByQuarterAndFieldOfficeId(int $fieldOfficeId, string $quarterName, int $quarterYear): array
    {
        $quarterMonthsList = [...$this->appDateHelper->getMonthsByQuarterString($quarterName)];
        $quarterYearList = [$quarterYear];
        $minMaxDate = $this->appDateHelper->getMinMaxDateByYearsAndMonths($quarterYearList, $quarterMonthsList);
        $minDate = $minMaxDate['min'];
        $maxDate = $minMaxDate['max'];

        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT rfs.resource_facilitator_id, rfs.resource_facilitator_type, s.session_id FROM sessions as s
                LEFT JOIN resource_facilitator_session as rfs ON s.session_id = rfs.session_id
                WHERE s.field_office_id = $fieldOfficeId AND rfs.resource_facilitator_type = 'VPA' 
                  AND s.date BETWEEN CAST('$minDate' AS DATE) AND CAST('$maxDate' AS DATE)";

        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();

        return $query->fetchAllAssociative();
    }

    public function getVolunteerIdsBySessionIds(array $sessionIds): array
    {
        return $this->createQueryBuilder('rfr')
            ->select('rfr.resourceFacilitatorId')
            ->where('rfr.sessionId IN (:ids)')
            ->setParameter('ids', $sessionIds, Connection::PARAM_INT_ARRAY)
            ->getQuery()
            ->getResult();
    }

    private function isConflicted(
        ResourceFacilitatorSession $fetchedResourceFacilitatorSession,
        ResourceFacilitatorSessionModel $resourceFacilitatorSessionData): bool
    {
        // Fetched and input client is the same.
        // It is trying to update itself.
        if (
            $fetchedResourceFacilitatorSession->getSessionId() === $resourceFacilitatorSessionData->getSessionId() &&
            $fetchedResourceFacilitatorSession->getResourceFacilitatorId() === $resourceFacilitatorSessionData->getResourceFacilitatorId() &&
            $fetchedResourceFacilitatorSession->getResourceFacilitatorType() === $resourceFacilitatorSessionData->getResourceFacilitatorType()
        ) {
            return false;
        }

        // There is an existing record in the database.
        // It is trying to update another record that existing in the database.
        if ($this->isExisting($resourceFacilitatorSessionData)) {
            return true;
        }

        return false;
    }
}
