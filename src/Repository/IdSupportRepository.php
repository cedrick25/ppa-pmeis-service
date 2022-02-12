<?php

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\IdSupport;
use App\Model\IdSupport as IdSupportModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method IdSupport|null find($id, $lockMode = null, $lockVersion = null)
 * @method IdSupport|null findOneBy(array $criteria, array $orderBy = null)
 * @method IdSupport[]    findAll()
 * @method IdSupport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IdSupportRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "id_supports";

    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private Helper $helper,
        private AppDateHelper $appDateHelper,
    ) {
        parent::__construct($registry, IdSupport::class);
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllIdSupportsKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function() {
            return $this->createQueryBuilder('ids')
                ->where('ids.deletedAt IS NULL')
                ->orderBy('ids.id', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function create(IdSupportModel $data): int | null
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $newIdSupport = new IdSupport();
        $newIdSupport->setType($data->getType());
        $newIdSupport->setVpaPersonnelId($data->getVpaPersonnelId());
        $newIdSupport->setProgram($data->getProgram());
        $newIdSupport->setFieldOfficeId($data->getFieldOfficeId());
        $newIdSupport->setDate($this->appDateHelper->convertStringToImmutableDate($data->getDate()));
        $newIdSupport->setVenue($data->getVenue());
        $newIdSupport->setAssistanceRendered($data->getAssistanceRendered());
        $newIdSupport->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($newIdSupport);
        $this->getEntityManager()->flush();

        return $newIdSupport->getId();
    }

    /**
     * @throws InvalidArgumentException
     * @throws ORMException
     */
    public function delete(int $id): bool
    {
        $idSupport = $this->isExistingById($id);
        if (! $idSupport) {
            return false;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->getEntityManager()->remove($idSupport);
        $this->getEntityManager()->flush();

        return true;
    }

    public function isExistingById(int $id): bool | IdSupport
    {
        $idSupport = $this->findOneBy([
            'id' => $id,
            'deletedAt' => null
        ]);

        return ($idSupport == null) ? false : $idSupport;
    }
}
