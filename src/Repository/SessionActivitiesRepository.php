<?php

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\Phases;
use App\Entity\SessionActivities;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
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
     */
    public function getById(int $id): ?SessionActivities
    {
        return $this->createQueryBuilder('sa')
            ->andWhere('sa.sessionActivityId = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @throws NonUniqueResultException
     * @throws InvalidArgumentException
     * @throws ORMException
     */
    public function delete(int $id): bool
    {
        $sessionActivityById = $this->getById($id);

        if ($sessionActivityById == null) {
            return false;
        }

        $this->cache->delete($this->cacheHelper->getAllSessionActivitiesKey());

        $this->getEntityManager()->remove($sessionActivityById);
        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @throws NonUniqueResultException
     */
    private function isExistByName(string $name): bool
    {
        $sessionActivity = $this->createQueryBuilder('sa')
            ->andWhere('sa.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();

        return $sessionActivity != null;
    }
}
