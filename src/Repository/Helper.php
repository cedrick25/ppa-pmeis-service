<?php

namespace App\Repository;

use App\Common\AppFormatter;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\ItemInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class Helper
{
    public function __construct(
        private TagAwareCacheInterface $cache,
        private AppFormatter $appFormatter
    ){}

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function setCachedPaginatedResponse(QueryBuilder $query, array $params): array
    {
        return $this->cache->get($params['cacheKey'], function (ItemInterface $item) use ($query, $params) {
            $item->expiresAt($params['expiration']);
            $item->tag($params['cacheTag']);

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
}