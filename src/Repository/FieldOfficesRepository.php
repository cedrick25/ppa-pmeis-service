<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppFormatter;
use App\Common\CacheHelper;
use App\Entity\FieldOffices;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
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
        private AppFormatter $appFormatter
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
     * @return array<string, mixed>
     * @throws InvalidArgumentException
     */
    public function paginated(int $page = 1, int $pageSize = 10): array
    {
        $cacheKey = $this->cacheHelper->getFieldOfficePaginatedKey($page, $pageSize);
        $expiration = $this->cacheHelper->getExpirationDateTime();

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($cacheKey, $expiration, $page, $pageSize) {
            $item->expiresAt($expiration);
            $item->tag(self::CACHE_TAG);

            $query = $this->createQueryBuilder('fo')->orderBy('fo.fieldOfficeId');

            $pageItems = array();
            $paginator = new Paginator($query);
            $totalItems = $paginator->count();
            $pageCount = ceil($totalItems / $pageSize);

            $paginator
                ->getQuery()
                ->setFirstResult($pageSize * ($page-1))
                ->setMaxResults($pageSize);

            foreach ($paginator as $pageItem) {
                $pageItems[] = $pageItem;
            }

            return $this->appFormatter->formatPagination($totalItems, $pageCount, $pageItems);
        });
    }
}
