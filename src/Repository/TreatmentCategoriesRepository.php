<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\TreatmentCategories;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

            return $this->findAll();
        });
    }
    /**
     * @throws InvalidArgumentException
     * @throws ORMException
     */
    public function create(string $name): int | null
    {
        $treatmentCategory = $this->findOneBy(['name' => $name]);

        if ($treatmentCategory != null) {
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
}
