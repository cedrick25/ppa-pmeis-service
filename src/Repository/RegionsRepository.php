<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\CacheHelper;
use App\Entity\Regions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method Regions|null find($id, $lockMode = null, $lockVersion = null)
 * @method Regions|null findOneBy(array $criteria, array $orderBy = null)
 * @method Regions[]    findAll()
 * @method Regions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RegionsRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "regions";

    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private Helper $helper,
    ){
        parent::__construct($registry, Regions::class);
    }


    /**
     * @return Regions[]
     * @throws InvalidArgumentException
     */
    public function list(): array
    {
        $cacheKey = $this->cacheHelper->getAllRegionsKey();
        $expiration = $this->cacheHelper->getExpirationDateTime();

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($cacheKey, $expiration) {
            $item->expiresAt($expiration);
            $item->tag(self::CACHE_TAG);

            return $this->createQueryBuilder('r')
                ->orderBy('r.regionId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }
}
