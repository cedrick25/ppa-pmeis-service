<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\TreatmentCategories;
use App\Enum\Response as ResponseEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method TreatmentCategories|null find($id, $lockMode = null, $lockVersion = null)
 * @method TreatmentCategories|null findOneBy(array $criteria, array $orderBy = null)
 * @method TreatmentCategories[]    findAll()
 * @method TreatmentCategories[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TreatmentCategoriesRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "treatment_categories";

    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private AppDateHelper $appDateHelper,
        private Helper $helper,
    ){
        parent::__construct($registry, TreatmentCategories::class);
    }

    /**
     * @return TreatmentCategories[]
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllTreatmentCategoriesKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function() {
            return $this->createQueryBuilder('tc')
                ->andWhere('tc.deletedAt IS NULL')
                ->orderBy('tc.treatmentCategoryId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @throws InvalidArgumentException
     * @throws ORMException
     */
    public function create(string $name): int | null
    {
        if ($this->isExisting($name)) {
            return null;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $newTreatmentCategory = new TreatmentCategories();
        $newTreatmentCategory->setName($name);
        $newTreatmentCategory->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($newTreatmentCategory);
        $this->getEntityManager()->flush();

        return $newTreatmentCategory->getTreatmentCategoryId();
    }

    /**
     * @throws InvalidArgumentException
     * @throws ORMException
     */
    public function delete(int $id): bool
    {
        $treatmentCategory = $this->isExistingById($id);

        if (! $treatmentCategory) {
            return false;
        }

        // TODO: Check if there is an existing id in sessions

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->getEntityManager()->remove($treatmentCategory);
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
        $treatmentCategory = $this->isExistingById($id);

        if (! $treatmentCategory) {
            return ResponseEnum::NO_RECORD;
        }

        if ($this->isExisting($name) && $treatmentCategory->getName() !== $name) {
            return ResponseEnum::CONFLICTED_INPUT;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $treatmentCategory->setName($name);
        $treatmentCategory->setUpdatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->flush();

        return ResponseEnum::OK;
    }

    public function isExistingById(int $id): bool | TreatmentCategories
    {
        $treatmentCategory = $this->findOneBy([
            'treatmentCategoryId' => $id,
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
            'cacheKey' => $this->cacheHelper->getPositionsPaginatedKey($page, $pageSize),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG,
            'pageSize' => $pageSize,
            'page' => $page
        ];

        return $this->helper->createPaginatedResponse($params, function() {
            return $this->createQueryBuilder('tc')
                ->where('tc.deletedAt IS NULL')
                ->orderBy('tc.treatmentCategoryId');
        });
    }

    private function isExisting(string $name): bool
    {
        $treatmentCategory = $this->findOneBy([
            'name' => $name,
            'deletedAt' => null
        ]);

        return $treatmentCategory != null;
    }
}
