<?php

namespace App\Repository;

use App\Common\AppFormatter;
use App\Common\CacheHelper;
use App\Entity\ClientSessions;
use App\Enum\ClientSessionRole;
use App\Enum\Response as ResponseEnum;
use App\Model\ClientSessions as ClientSessionModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @method ClientSessions|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClientSessions|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClientSessions[]    findAll()
 * @method ClientSessions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientSessionsRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "client_sessions";

    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private AppFormatter $appFormatter,
        private Helper $helper
    ){
        parent::__construct($registry, ClientSessions::class);
    }

    /**
     * @throws NonUniqueResultException
     * @throws ORMException
     * @throws InvalidArgumentException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function create(ClientSessionModel $clientSessions): int|null
    {
        if ($this->isExisting($clientSessions)) {
            return null;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $newClientSession = new ClientSessions();
        $newClientSession->setClientId($clientSessions->getClientId());
        $newClientSession->setSessionId($clientSessions->getSessionId());
        $newClientSession->setRole(ClientSessionRole::from($clientSessions->getRole()));

        $this->getEntityManager()->persist($newClientSession);
        $this->getEntityManager()->flush();

        return $newClientSession->getClientSessionId();
    }

    /**
     * @throws InvalidArgumentException
     * @return ClientSessions[]
     */
    public function list(): array
    {
        $cacheKey = $this->cacheHelper->getAllClientSessionsKey();
        $expiration = $this->cacheHelper->getExpirationDateTime(24);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($cacheKey, $expiration) {
            $item->expiresAt($expiration);

            return $this->createQueryBuilder('cs')
                ->orderBy('cs.clientSessionId', 'DESC')
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
        $clientSession = $this->isExistingById($id);

        if (! $clientSession) {
            return false;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->getEntityManager()->remove($clientSession);
        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function update(int $id, ClientSessionModel $clientSessionData): string
    {
        $clientSession = $this->isExistingById($id);

        if (! $clientSession) {
            return ResponseEnum::NO_RECORD;
        }

        if ($this->isConflicted($clientSession, $clientSessionData)) {
            return ResponseEnum::CONFLICTED_INPUT;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $clientSession->setClientId($clientSessionData->getClientId());
        $clientSession->setSessionId($clientSessionData->getSessionId());
        $clientSession->setRole(ClientSessionRole::from($clientSessionData->getRole()));

        $this->getEntityManager()->persist($clientSession);
        $this->getEntityManager()->flush();

        return ResponseEnum::OK;
    }

    public function isExistingById(int $id): bool | ClientSessions
    {
        $client = $this->findOneBy([
            'clientSessionId' => $id
        ]);

        return ($client == null) ? false : $client;
    }

    /**
     * @param int $page
     * @param int $pageSize
     * @return array<string, mixed
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function paginated(int $page = 1, int $pageSize = 10): array
    {
        $cacheKey = $this->cacheHelper->getClientSessionsPaginatedKey($page, $pageSize);
        $expiration = $this->cacheHelper->getExpirationDateTime(24);
        $params = [
            'cacheKey' => $cacheKey,
            'expiration' => $expiration,
            'cacheTag' => self::CACHE_TAG,
            'pageSize' => $pageSize,
            'page' => $page
        ];

        $query = $this->createQueryBuilder('cs')->orderBy('cs.clientSessionId');

        return $this->helper->setCachedPaginatedResponse($query, $params);
    }

    private function isExisting(ClientSessionModel $clientSessionData): bool
    {
        $clientSession = $this->findOneBy([
            'clientId' => $clientSessionData->getClientId(),
            'sessionId' => $clientSessionData->getSessionId()
        ]);

        return $clientSession != null;
    }

    private function isConflicted(ClientSessions $fetchedClientSession, ClientSessionModel $clientSessionData): bool
    {
        // Fetched and input client is the same.
        // It is trying to update itself.
        if (
            $fetchedClientSession->getClientId() === $clientSessionData->getClientId() &&
            $fetchedClientSession->getSessionId() === $clientSessionData->getSessionId()
        ) {
            return false;
        }

        // There is an existing record in the database.
        // It is trying to update another record that existing in the database.
        if ($this->isExisting($clientSessionData)) {
            return true;
        }

        return false;
    }
}
