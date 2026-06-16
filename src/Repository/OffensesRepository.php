<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\Offenses;
use App\Enum\Response as ResponseEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method Offenses|null find($id, $lockMode = null, $lockVersion = null)
 * @method Offenses|null findOneBy(array $criteria, array $orderBy = null)
 * @method Offenses[]    findAll()
 * @method Offenses[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OffensesRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "offenses";

    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private Helper $helper,
        private AppDateHelper $appDateHelper,
    ){
        parent::__construct($registry, Offenses::class);
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     * @return Offenses[]
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllOffensesKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function() {
            return $this->createQueryBuilder('o')
                ->where('o.deletedAt IS NULL')
                ->orderBy('o.offensesId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @throws InvalidArgumentException
     * @throws ORMException|\Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function create(string $name, string $type): int | null
    {
        if ($this->isExisting($name, $type)) {
            return null;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $newOffense = new Offenses();
        $newOffense->setName($name);
        $newOffense->setType($type);
        $newOffense->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($newOffense);
        $this->getEntityManager()->flush();

        return $newOffense->getOffensesId();
    }

    /**
     * @throws OptimisticLockException
     * @throws InvalidArgumentException
     * @throws ORMException
     */
    public function softDelete(int $id): bool
    {
        $offense = $this->isExistingById($id);

        if (! $offense) {
            return false;
        }

        // TODO: Check if there is an existing id in sessions

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $offense->setDeletedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws InvalidArgumentException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function update(int $id, string $name, $type): string
    {
        $offense = $this->isExistingById($id);

        if (! $offense) {
            return ResponseEnum::NO_RECORD;
        }

        if ($this->isConflicted($offense, $name, $type)) {
            return ResponseEnum::CONFLICTED_INPUT;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $offense->setName($name);
        $offense->setType($type);
        $offense->setUpdatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->flush();

        return ResponseEnum::OK;
    }

    public function isExistingById(int $id): bool | Offenses
    {
        $offense = $this->findOneBy([
            'offensesId' => $id,
            'deletedAt' => null
        ]);

        return ($offense == null) ? false : $offense;
    }

    private function isExisting(string $name, string $type): bool
    {
        $offense = $this->findOneBy([
            'name' => $name,
            'type' => $type,
            'deletedAt' => null
        ]);

        return $offense != null;
    }

    private function isConflicted(Offenses $offenses, string $name, string $type): bool
    {
        if ($offenses->getName() === $name && $offenses->getType() === $type)
        {
            return false;
        }

        if ($this->isExisting($name, $type)) {
            return true;
        }

        return false;
    }
}
