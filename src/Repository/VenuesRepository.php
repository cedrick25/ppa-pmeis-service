<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\Venues;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @method Venues|null find($id, $lockMode = null, $lockVersion = null)
 * @method Venues|null findOneBy(array $criteria, array $orderBy = null)
 * @method Venues[]    findAll()
 * @method Venues[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VenuesRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private CacheInterface $cache,
        private CacheHelper $cacheHelper,
        private AppDateHelper $appDateHelper,
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

        $this->cache->delete($this->cacheHelper->getAllVenuesKey());

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
     */
    public function list(): array
    {
        $cacheKey = $this->cacheHelper->getAllVenuesKey();
        $expiration = $this->cacheHelper->getExpirationDateTime(24);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($cacheKey, $expiration) {
            $item->expiresAt($expiration);

            return $this->createQueryBuilder('vn')
                ->orderBy('vn.venueId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getById(int $id): ?Venues
    {
        return $this->createQueryBuilder('vn')
            ->andWhere('vn.venueId = :id')
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
        $venue = $this->getById($id);

        if ($venue == null) {
            return false;
        }

        $this->cache->delete($this->cacheHelper->getAllVenuesKey());

        $this->getEntityManager()->remove($venue);
        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @throws NonUniqueResultException
     */
    private function isExistByName(string $name): bool
    {
        $venue = $this->createQueryBuilder('vn')
            ->andWhere('vn.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();

        return $venue != null;
    }
}
