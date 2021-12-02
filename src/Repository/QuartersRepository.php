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
    public function fetchTCA1Part1(int $id): ?array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getQuartersTCA1Part1Key($id),
            'cacheTag' => self::SESSION_CACHE_TAG
        ];

        return $this->helper->createCachedResponseCustomQuery($params, function() use ($id) {
            $conn = $this->getEntityManager()->getConnection();

            $sql = "SELECT q.*, s.session_id, sa.name as session_activity_title, s.treatment_category_id,
                      p.name as phase_name, s.batch ,v.name as venue, s.date, s.period FROM quarters as q " .
                "LEFT JOIN sessions as s ON q.quarter_id = s.quarter_id " .
                "LEFT JOIN session_activities as sa ON s.session_activity_id = sa.session_activity_id " .
                "LEFT JOIN phases as p ON s.phase_id = p.phase_id " .
                "LEFT JOIN venues as v ON s.venue_id = v.venue_id " .
                "WHERE q.quarter_id = $id ORDER BY p.phase_id";
            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();

            return $query->fetchAllAssociative();
        });
    }
}
