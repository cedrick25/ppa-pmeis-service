<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\TreatmentCategories;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @method TreatmentCategories|null find($id, $lockMode = null, $lockVersion = null)
 * @method TreatmentCategories|null findOneBy(array $criteria, array $orderBy = null)
 * @method TreatmentCategories[]    findAll()
 * @method TreatmentCategories[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TreatmentCategoriesRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private CacheInterface $cache,
        private CacheHelper $cacheHelper,
        private AppDateHelper $appDateHelper,
    ){
        parent::__construct($registry, TreatmentCategories::class);
    }

    /**
     * @throws InvalidArgumentException
     * @return TreatmentCategories[]
     */
    public function list(): array
    {
        $cacheKey = $this->cacheHelper->getAllTreatmentCategoriesKey();
        $expiration = $this->cacheHelper->getExpirationDateTime(24);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($cacheKey, $expiration) {
            $item->expiresAt($expiration);

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

        $this->cache->delete($this->cacheHelper->getAllTreatmentCategoriesKey());

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

        $this->cache->delete($this->cacheHelper->getAllTreatmentCategoriesKey());

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

        $this->cache->delete($this->cacheHelper->getAllTreatmentCategoriesKey());

        $treatmentCategory->setDeletedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->flush();

        return true;
    }

    public function isExistingById(int $id): bool | TreatmentCategories
    {
        $treatmentCategory = $this->findOneBy([
            'treatmentCategoryId' => $id,
            'deletedAt' => null
        ]);

        return ($treatmentCategory == null) ? false : $treatmentCategory;
    }

    private function isExisting(string $name): bool
    {
        $client = $this->findOneBy([
            'name' => $name,
            'deletedAt' => null
        ]);

        return $client != null;
    }
}
