<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\CacheHelper;
use App\Entity\Regions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheException;
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
        private CacheHelper $cacheHelper,
        private Helper $helper,
    ){
        parent::__construct($registry, Regions::class);
    }


    /**
     * @return Regions[]
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllRegionsKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function() {
            return $this->createQueryBuilder('r')
                ->where('r.deletedAt IS NULL')
                ->orderBy('r.regionId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    public function isExistingById(int $id): bool | Regions
    {
        $region = $this->findOneBy([
            'regionId' => $id,
            'deletedAt' => null
        ]);

        return ($region == null) ? false : $region;
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
            'cacheKey' => $this->cacheHelper->getRegionsPaginatedKey($page, $pageSize),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG,
            'pageSize' => $pageSize,
            'page' => $page
        ];

        return $this->helper->createPaginatedResponse($params, function () {
            return $this->createQueryBuilder('r')
                ->where('r.deletedAt IS NULL')
                ->orderBy('r.regionId');
        });
    }

    /**
     * @param int[] $ids
     * @return array<string, string>
     */
    public function getRegionNamesByIds(array $ids): array
    {
        $return = [];
        $query = $this->_em->getConnection()->executeQuery(
          "SELECT region_id, name FROM regions WHERE region_id IN (:ids)",
              ['ids' => $ids],
              ['ids' => Connection::PARAM_INT_ARRAY]
        );
        $results = $query->fetchAllAssociative();

        foreach ($results as $result) {
            $return[$result['region_id']] = $result['name'];
        }

        return $return;
    }
}
