<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppFormatter;
use DateInterval;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class Helper
{
    public function __construct(
        private TagAwareCacheInterface $cache,
        private AppFormatter $appFormatter
    ){}

    /**
     * @param array $params
     * @param callable $getData
     * @return array<string, mixed> | null
     * @throws InvalidArgumentException|CacheException
     */
    public function createCachedResponse(array $params, Callable $getData): ?array
    {
        return $this->cache->get($params['cacheKey'], function (ItemInterface $item) use ($params, $getData) {
            $item->expiresAt($params['expiration']);
            $item->tag($params['cacheTag']);

            return $getData();
        });
    }

    /**
     * @param array $params
     * @param callable $getResult
     * @return array|null
     * @throws InvalidArgumentException|CacheException
     */
    public function createCachedResponseCustomQuery(array $params, Callable $getResult): ?array
    {
        return $this->cache->get($params['cacheKey'], function (ItemInterface $item) use ($params, $getResult) {
            $dateTimeExpiration = new \DateTime();

            /** @var array<string, mixed> $result */
            $result = $getResult();

            if (!$result) {
                $dateTimeExpiration->add(new DateInterval("PT1S"));
                $item->expiresAt($dateTimeExpiration);
                $item->tag($params['cacheTag']);
                return null;
            }

            $dateTimeExpiration->add(new DateInterval("PT24H"));
            $item->expiresAt($dateTimeExpiration);
            $item->tag($params['cacheTag']);

            return $result;
        });
    }

    /**
     * @param array $params
     * @param callable $getQuery
     * @return array
     * @throws InvalidArgumentException|CacheException
     */
    public function createPaginatedResponse(array $params, Callable $getQuery): array
    {
        return $this->cache->get($params['cacheKey'], function (ItemInterface $item) use ($params, $getQuery) {
            $item->expiresAt($params['expiration']);
            $item->tag($params['cacheTag']);

            /** @var QueryBuilder $query */
            $query = $getQuery();

            $pageItems = array();
            $paginator = new Paginator($query);
            $totalItems = $paginator->count();
            $pageCount = ceil($totalItems / $params['pageSize']);

            $paginator
                ->getQuery()
                ->setFirstResult($params['pageSize'] * ($params['page']-1))
                ->setMaxResults($params['pageSize']);

            foreach ($paginator as $pageItem) {
                $pageItems[] = $pageItem;
            }

            return $this->appFormatter->formatPagination($totalItems, $pageCount, $pageItems);
        });
    }

    /**
     * @param array $params
     * @param callable $getResult
     * @return array | null
     * @throws InvalidArgumentException|CacheException
     */
    public function createPaginatedResponseCustomQuery(array $params, Callable $getResult): ?array
    {
        return $this->cache->get($params['cacheKey'], function (ItemInterface $item) use ($params, $getResult) {
            $dateTimeExpiration = new \DateTime();

            /** @var array<string, mixed> $result */
            $result = $getResult();

            if ($result['totalItems'] === 0) {
                $dateTimeExpiration->add(new DateInterval("PT1S"));
                $item->expiresAt($dateTimeExpiration);
                $item->tag($params['cacheTag']);

                return $this->appFormatter->formatPagination($result['totalItems'], 0, []);
            }

            $pageCount = ceil($result['totalItems'] / $params['pageSize']);

            $dateTimeExpiration->add(new DateInterval("PT24H"));
            $item->expiresAt($dateTimeExpiration);
            $item->tag($params['cacheTag']);

            return $this->appFormatter->formatPagination($result['totalItems'], $pageCount, $result['data']);
        });
    }

    public function getCustomQueryPaginatedTotalItems(Connection $conn, string $sql, ?array $bindValue = []): int
    {
        $stmt = $conn->prepare($sql);

        foreach ($bindValue as $key=>$value) {
            $stmt->bindValue($key,  $value[0], $value[1]);
        }

        $query = $stmt->executeQuery();

        return $query->rowCount();
    }
}