<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\Phases;
use App\Entity\SessionActivities;
use App\Enum\Response as ResponseEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method SessionActivities|null find($id, $lockMode = null, $lockVersion = null)
 * @method SessionActivities|null findOneBy(array $criteria, array $orderBy = null)
 * @method SessionActivities[]    findAll()
 * @method SessionActivities[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionActivitiesRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "session_activities";

    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private AppDateHelper $appDateHelper,
        private Helper $helper,
    ){
        parent::__construct($registry, SessionActivities::class);
    }

    /**
     * @return Phases[]
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllSessionActivitiesKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function() {
            return $this->createQueryBuilder('sa')
                ->andWhere('sa.deletedAt IS NULL')
                ->orderBy('sa.sessionActivityId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @throws InvalidArgumentException
     */
    public function create(
        string $name,
        ?int $phaseId,
        int $treatmentCategoryId,
    ): int|null {
        // $isExist = $this->isExistByName($name);

        // if ($isExist) {
        //     return null;
        // }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $sessionActivity = new SessionActivities();
        $sessionActivity->setName($name);
        $sessionActivity->setPhaseId($phaseId);
        $sessionActivity->setTreatmentCategoryId($treatmentCategoryId);
        $sessionActivity->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($sessionActivity);
        $this->getEntityManager()->flush();

        return $sessionActivity->getSessionActivityId();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function delete(int $id): bool
    {
        $sessionActivity = $this->getById($id);

        if ($sessionActivity == null) {
            return false;
        }

        // TODO: Check if there is an existing session activity to sessions

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->getEntityManager()->remove($sessionActivity);
        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function softDelete(int $id): bool
    {
        $sessionActivity = $this->getById($id);

        if ($sessionActivity == null) {
            return false;
        }

        // TODO: Check if there is an existing session activity to sessions

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $sessionActivity->setDeletedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function update(
        int $id,
        string $name,
        ?int $phaseId,
        int $treatmentCategoryId,
    ): string {
        $sessionActivity = $this->getById($id);

        if ($sessionActivity == null) {
            return ResponseEnum::NO_RECORD;
        }

        // if ($this->isExistByName($name) && $sessionActivity->getName() !== $name) {
        //     return ResponseEnum::CONFLICTED_INPUT;
        // }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $sessionActivity->setName($name);
        $sessionActivity->setPhaseId($phaseId);
        $sessionActivity->setTreatmentCategoryId($treatmentCategoryId);
        $sessionActivity->setUpdatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->flush();

        return ResponseEnum::OK;
    }

    public function getById(int $id): ?SessionActivities
    {
        return $this->findOneBy([
            'sessionActivityId' => $id,
            'deletedAt' => null
        ]);
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
            'cacheKey' => $this->cacheHelper->getSessionActivitiesPaginatedKey($page, $pageSize),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG,
            'pageSize' => $pageSize,
            'page' => $page
        ];

        return $this->helper->createPaginatedResponseCustomQuery($params, function() use ($pageSize, $page) {
            $conn = $this->getEntityManager()->getConnection();
            $startOffset = $pageSize * ($page-1);
            $result = [];

            $sql = "SELECT sa.*, tc.name as treatment_category_name, 'test' as testColumn FROM session_activities as sa " .
                    "LEFT JOIN treatment_categories as tc ON sa.treatment_category_id = tc.treatment_category_id " .
                    "WHERE sa.deleted_at IS NULL ORDER BY sa.session_activity_id DESC ";

            $result['totalItems'] = $this->helper->getCustomQueryPaginatedTotalItems($conn, $sql);

            $sql .= "LIMIT $pageSize OFFSET $startOffset";

            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();
            $result['data'] = $query->fetchAllAssociative();

            return $result;
        });
    }

    private function isExistByName(string $name): bool
    {
        $sessionActivity = $this->findOneBy([
            'name' => $name,
            'deletedAt' => null
        ]);

        return $sessionActivity != null;
    }
}
