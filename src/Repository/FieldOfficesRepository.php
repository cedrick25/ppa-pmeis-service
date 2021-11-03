<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\CacheHelper;
use App\Entity\FieldOffices;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @method FieldOffices|null find($id, $lockMode = null, $lockVersion = null)
 * @method FieldOffices|null findOneBy(array $criteria, array $orderBy = null)
 * @method FieldOffices[]    findAll()
 * @method FieldOffices[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FieldOfficesRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "field_offices";

    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private Helper $helper,
    ){
        parent::__construct($registry, FieldOffices::class);
    }

    /**
     * @return FieldOffices[]
     * @throws InvalidArgumentException
     */
    public function list(): array
    {
        $cacheKey = $this->cacheHelper->getAllFieldOfficesKey();
        $expiration = $this->cacheHelper->getExpirationDateTime();

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($cacheKey, $expiration) {
            $item->expiresAt($expiration);
            $item->tag(self::CACHE_TAG);

            return $this->createQueryBuilder('fo')
                ->orderBy('fo.fieldOfficeId', 'DESC')
                ->getQuery()
                ->getResult();
        });
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
            'cacheKey' => $this->cacheHelper->getFieldOfficePaginatedKey($page, $pageSize),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG,
            'pageSize' => $pageSize,
            'page' => $page
        ];

        return $this->helper->createPaginatedResponse($params, function() {
            return $this->createQueryBuilder('fo')->orderBy('fo.fieldOfficeId');
        });
    }

    /**
     * @throws InvalidArgumentException
     * @return FieldOffices[]
     */
    public function getByRegion(int $regionId): array
    {
        $cacheKey = $this->cacheHelper->getFieldOfficeByRegionKey($regionId);
        $expiration = $this->cacheHelper->getExpirationDateTime();

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($cacheKey, $expiration, $regionId) {
            $item->expiresAt($expiration);
            $item->tag(self::CACHE_TAG);

            return $this->findBy([
                    'regionId' => $regionId,
                    'deletedAt' => null
                ]);
        });
    }
}
