<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\AppFormatter;
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
use Symfony\Contracts\Cache\ItemInterface;
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
}
