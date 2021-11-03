<?php

namespace App\Repository;

use App\Common\AppFormatter;
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
}