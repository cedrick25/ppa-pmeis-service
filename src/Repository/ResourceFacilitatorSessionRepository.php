<?php

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\ResourceFacilitatorSession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use App\Model\ResourceFacilitatorSession as ResourceFacilitatorSessionModel;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use App\Enum\ResourceFacilitatorType;

/**
 * @method ResourceFacilitatorSession|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResourceFacilitatorSession|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResourceFacilitatorSession[]    findAll()
 * @method ResourceFacilitatorSession[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResourceFacilitatorSessionRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private CacheInterface $cache,
        private CacheHelper $cacheHelper
    ){
        parent::__construct($registry, ResourceFacilitatorSession::class);
    }

    /**
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws ORMException
     */
    public function create(ResourceFacilitatorSessionModel $resourceFacilitatorSessionData): int | null
    {
        if ($this->isExisting($resourceFacilitatorSessionData)) {
            return null;
        }

        $this->cache->delete($this->cacheHelper->getAllResourceFacilitatorSessionsKey());

        $newResourceFacilitatorSession = new ResourceFacilitatorSession();
        $newResourceFacilitatorSession->setSessionId($resourceFacilitatorSessionData->getSessionId());
        $newResourceFacilitatorSession->setResourceFacilitatorId($resourceFacilitatorSessionData->getResourceFacilitatorId());
        $newResourceFacilitatorSession->setResourceFacilitatorType(ResourceFacilitatorType::from($resourceFacilitatorSessionData->getResourceFacilitatorType()));

        $this->getEntityManager()->persist($newResourceFacilitatorSession);
        $this->getEntityManager()->flush();

        return $newResourceFacilitatorSession->getResourceFacilitatorSessionId();
    }

    private function isExisting(ResourceFacilitatorSessionModel $resourceFacilitatorSessionData): bool
    {
        $client = $this->findOneBy([
            'sessionId' => $resourceFacilitatorSessionData->getSessionId(),
            'resourceFacilitatorId' => $resourceFacilitatorSessionData->getResourceFacilitatorId(),
            'resourceFacilitatorType' => $resourceFacilitatorSessionData->getResourceFacilitatorType()
        ]);

        return $client != null;
    }
}
