<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\Venues;
use App\Enum\Response as ResponseEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method Venues|null find($id, $lockMode = null, $lockVersion = null)
 * @method Venues|null findOneBy(array $criteria, array $orderBy = null)
 * @method Venues[]    findAll()
 * @method Venues[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VenuesRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "venues";

    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private AppDateHelper $appDateHelper,
        private Helper $helper,
    ){
        parent::__construct($registry, Venues::class);
    }

    /**
     * @throws NonUniqueResultException
     * @throws ORMException
     * @throws InvalidArgumentException
     */
    public function create(string $name): int|null
    {
        $isExist = $this->isExistByName($name);

        if ($isExist) {
            return null;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $venue = new Venues();
        $venue->setName($name);
        $venue->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($venue);
        $this->getEntityManager()->flush();

        return $venue->getVenueId();
    }

    /**
     * @return Venues[]
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllVenuesKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function() {
            return $this->createQueryBuilder('vn')
                ->andWhere('vn.deletedAt IS NULL')
                ->orderBy('vn.venueId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @throws NonUniqueResultException
     * @throws InvalidArgumentException
     * @throws ORMException
     */
    public function delete(int $id): bool
    {
        $venue = $this->isExistingById($id);

        if (! $venue) {
            return false;
        }

        // TODO: Check if there is an existing id in sessions

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->getEntityManager()->remove($venue);
        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws InvalidArgumentException
     */
    public function softDelete(int $id): bool
    {
        $treatmentCategory = $this->isExistingById($id);

        if (! $treatmentCategory) {
            return false;
        }

        // TODO: Check if there is an existing id in sessions

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $treatmentCategory->setDeletedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function update(int $id, string $name): string
    {
        $venue = $this->isExistingById($id);

        if (! $venue) {
            return ResponseEnum::NO_RECORD;
        }

        if ($this->isExistByName($name) && $venue->getName() !== $name) {
            return ResponseEnum::CONFLICTED_INPUT;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $venue->setName($name);
        $venue->setUpdatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->flush();

        return ResponseEnum::OK;
    }

    public function isExistingById(int $id): bool | Venues
    {
        $treatmentCategory = $this->findOneBy([
            'venueId' => $id,
            'deletedAt' => null
        ]);

        return ($treatmentCategory == null) ? false : $treatmentCategory;
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
            'cacheKey' => $this->cacheHelper->getVenuesPaginatedKey($page, $pageSize),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG,
            'pageSize' => $pageSize,
            'page' => $page
        ];

        return $this->helper->createPaginatedResponse($params, function() {
            return $this->createQueryBuilder('v')
                ->where('v.deletedAt IS NULL')
                ->orderBy('v.venueId');
        });
    }

    private function isExistByName(string $name): bool
    {
        $venue = $this->findOneBy([
            'name' => $name,
            'deletedAt' => null
        ]);

        return $venue != null;
    }
}
