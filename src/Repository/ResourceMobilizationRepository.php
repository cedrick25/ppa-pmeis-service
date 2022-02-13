<?php

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\ResourceMobilization;
use App\Model\ResourceMobilization as ResourceMobilizationModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method ResourceMobilization|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResourceMobilization|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResourceMobilization[]    findAll()
 * @method ResourceMobilization[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResourceMobilizationRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "resource_mobilizations";

    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private Helper $helper,
        private AppDateHelper $appDateHelper,
    ) {
        parent::__construct($registry, ResourceMobilization::class);
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllResourceMobilizationKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function() {
            return $this->createQueryBuilder('rm')
                ->where('rm.deletedAt IS NULL')
                ->orderBy('rm.resourceMobilizationId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     * @throws \Exception
     */
    public function create(ResourceMobilizationModel $data): int | null
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $newResourceMobilization = new ResourceMobilization();
        $newResourceMobilization->setCategory($data->getCategory());
        $newResourceMobilization->setActivityName($data->getActivityName());
        $newResourceMobilization->setDate($this->appDateHelper->convertStringToImmutableDate($data->getDate()));
        $newResourceMobilization->setVenue($data->getVenue());
        $newResourceMobilization->setAmount($data->getAmount());
        $newResourceMobilization->setCashSourceName($data->getCashSourceName());
        $newResourceMobilization->setCashSourceType($data->getCashSourceType());
        $newResourceMobilization->setMaterialsId($data->getMaterialsId());
        $newResourceMobilization->setMaterialsQty($data->getMaterialsQty());
        $newResourceMobilization->setMaterialSourceName($data->getMaterialSourceName());
        $newResourceMobilization->setMaterialSourceType($data->getMaterialSourceType());
        $newResourceMobilization->setTechnicalAssistanceParticulars($data->getTechnicalAssistanceParticulars());
        $newResourceMobilization->setTechnicalAssistanceAmount($data->getTechnicalAssistanceAmount());
        $newResourceMobilization->setTechnicalAssistanceName($data->getTechnicalAssistanceName());
        $newResourceMobilization->setTechnicalAssistanceType($data->getTechnicalAssistanceType());
        $newResourceMobilization->setResourcesSecuredBy($data->getResourcesSecuredBy());
        $newResourceMobilization->setFieldOfficeId($data->getFieldOfficeId());
        $newResourceMobilization->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($newResourceMobilization);
        $this->getEntityManager()->flush();

        return $newResourceMobilization->getResourceMobilizationId();
    }

    /**
     * @throws InvalidArgumentException
     * @throws ORMException
     */
    public function delete(int $id): bool
    {
        $resourceMobilization = $this->isExistingById($id);
        if (! $resourceMobilization) {
            return false;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->getEntityManager()->remove($resourceMobilization);
        $this->getEntityManager()->flush();

        return true;
    }

    public function isExistingById(int $id): bool | ResourceMobilization
    {
        $resourceMobilization = $this->findOneBy([
            'resourceMobilizationId' => $id,
            'deletedAt' => null
        ]);

        return ($resourceMobilization == null) ? false : $resourceMobilization;
    }
}
