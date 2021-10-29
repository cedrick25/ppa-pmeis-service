<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\Phases;
use App\Entity\SessionActivities;
use App\Enum\Response as ResponseEnum;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @method SessionActivities|null find($id, $lockMode = null, $lockVersion = null)
 * @method SessionActivities|null findOneBy(array $criteria, array $orderBy = null)
 * @method SessionActivities[]    findAll()
 * @method SessionActivities[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionActivitiesRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private CacheInterface $cache,
        private CacheHelper $cacheHelper,
        private AppDateHelper $appDateHelper,
    ){
        parent::__construct($registry, SessionActivities::class);
    }

    /**
     * @return Phases[]
     * @throws InvalidArgumentException
     */
    public function list(): array
    {
        $cacheKey = $this->cacheHelper->getAllSessionActivitiesKey();
        $expiration = $this->cacheHelper->getExpirationDateTime(24);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($cacheKey, $expiration) {
            $item->expiresAt($expiration);

            return $this->createQueryBuilder('sa')
                ->andWhere('sa.deletedAt IS NULL')
                ->orderBy('sa.sessionActivityId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @throws NonUniqueResultException|InvalidArgumentException
     * @throws ORMException
     */
    public function create(string $name): int|null
    {
        $isExist = $this->isExistByName($name);

        if ($isExist) {
            return null;
        }

        $this->cache->delete($this->cacheHelper->getAllSessionActivitiesKey());

        $sessionActivity = new SessionActivities();
        $sessionActivity->setName($name);
        $sessionActivity->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($sessionActivity);
        $this->getEntityManager()->flush();

        return $sessionActivity->getSessionActivityId();
    }

    /**
     * @throws NonUniqueResultException
     * @throws InvalidArgumentException
     * @throws ORMException
     */
    public function delete(int $id): bool
    {
        $sessionActivity = $this->getById($id);

        if ($sessionActivity == null) {
            return false;
        }

        // TODO: Check if there is an existing session activity to sessions

        $this->cache->delete($this->cacheHelper->getAllSessionActivitiesKey());

        $this->getEntityManager()->remove($sessionActivity);
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
        $sessionActivity = $this->getById($id);

        if ($sessionActivity == null) {
            return false;
        }

        // TODO: Check if there is an existing session activity to sessions

        $this->cache->delete($this->cacheHelper->getAllSessionActivitiesKey());

        $sessionActivity->setDeletedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function update(int $id, string $name): string
    {
        $sessionActivity = $this->getById($id);

        if ($sessionActivity == null) {
            return ResponseEnum::NO_RECORD;
        }

        if ($this->isExistByName($name) && $sessionActivity->getName() !== $name) {
            return ResponseEnum::CONFLICTED_INPUT;
        }

        $this->cache->delete($this->cacheHelper->getAllSessionActivitiesKey());

        $sessionActivity->setName($name);
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

    private function isExistByName(string $name): bool
    {
        $sessionActivity = $this->findOneBy([
            'name' => $name,
            'deletedAt' => null
        ]);

        return $sessionActivity != null;
    }
}
