<?php

namespace App\Repository;

use App\Common\CacheHelper;
use App\Entity\ResourceFacilitatorSession;
use App\Enum\Response as ResponseEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use App\Model\ResourceFacilitatorSession as ResourceFacilitatorSessionModel;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use App\Enum\ResourceFacilitatorType;
use Symfony\Contracts\Cache\ItemInterface;

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

    /**
     * @throws InvalidArgumentException
     * @return ResourceFacilitatorSession[]
     */
    public function list(): array
    {
        $cacheKey = $this->cacheHelper->getAllResourceFacilitatorSessionsKey();
        $expiration = $this->cacheHelper->getExpirationDateTime(24);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($cacheKey, $expiration) {
            $item->expiresAt($expiration);

            return $this->createQueryBuilder('rfs')
                ->orderBy('rfs.resourceFacilitatorSessionId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @throws InvalidArgumentException
     * @throws ORMException
     */
    public function delete(int $id): bool
    {
        $resourceFacilitatorSession = $this->isExistingById($id);

        if (! $resourceFacilitatorSession) {
            return false;
        }

        $this->cache->delete($this->cacheHelper->getAllResourceFacilitatorSessionsKey());

        $this->getEntityManager()->remove($resourceFacilitatorSession);
        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function update(int $id, ResourceFacilitatorSessionModel $resourceFacilitatorSessionData): string
    {
        $resourceFacilitatorSession = $this->isExistingById($id);

        if (! $resourceFacilitatorSession) {
            return ResponseEnum::NO_RECORD;
        }

        if ($this->isConflicted($resourceFacilitatorSession, $resourceFacilitatorSessionData)) {
            return ResponseEnum::CONFLICTED_INPUT;
        }

        $this->cache->delete($this->cacheHelper->getAllResourceFacilitatorSessionsKey());

        $resourceFacilitatorSession->setSessionId($resourceFacilitatorSessionData->getSessionId());
        $resourceFacilitatorSession->setResourceFacilitatorId($resourceFacilitatorSessionData->getResourceFacilitatorId());
        $resourceFacilitatorSession->setResourceFacilitatorType(ResourceFacilitatorType::from($resourceFacilitatorSessionData->getResourceFacilitatorType()));

        $this->getEntityManager()->flush();

        return ResponseEnum::OK;

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

    public function isExistingById(int $id): bool | ResourceFacilitatorSession
    {
        $client = $this->findOneBy([
            'resourceFacilitatorSessionId' => $id
        ]);

        return ($client == null) ? false : $client;
    }

    private function isConflicted(
        ResourceFacilitatorSession $fetchedResourceFacilitatorSession,
        ResourceFacilitatorSessionModel $resourceFacilitatorSessionData): bool
    {
        // Fetched and input client is the same.
        // It is trying to update itself.
        if (
            $fetchedResourceFacilitatorSession->getSessionId() === $resourceFacilitatorSessionData->getSessionId() &&
            $fetchedResourceFacilitatorSession->getResourceFacilitatorId() === $resourceFacilitatorSessionData->getResourceFacilitatorId() &&
            $fetchedResourceFacilitatorSession->getResourceFacilitatorType() === $resourceFacilitatorSessionData->getResourceFacilitatorType()
        ) {
            return false;
        }

        // There is an existing record in the database.
        // It is trying to update another record that existing in the database.
        if ($this->isExisting($resourceFacilitatorSessionData)) {
            return true;
        }

        return false;
    }
}
