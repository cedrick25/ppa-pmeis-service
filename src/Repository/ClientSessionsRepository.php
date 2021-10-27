<?php

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\ClientSessions;
use App\Enum\ClientSessionRole;
use App\Model\ClientSessions as ClientSessionModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @method ClientSessions|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClientSessions|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClientSessions[]    findAll()
 * @method ClientSessions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientSessionsRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private CacheInterface $cache,
        private CacheHelper $cacheHelper,
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
        $clientSession = $this->findOneBy([
            'clientId' => $clientSessions->getClientId(),
            'sessionId' => $clientSessions->getSessionId()
        ]);

        if ($clientSession != null) {
            return null;
        }

        $this->cache->delete($this->cacheHelper->getAllClientSessionsKey());

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

            return $this->findAll();
        });
    }

    /**
     * @throws InvalidArgumentException
     * @throws ORMException
     */
    public function delete(int $id): bool
    {
        $clientSession = $this->find($id);

        if ($clientSession == null) {
            return false;
        }

        $this->cache->delete($this->cacheHelper->getAllClientSessionsKey());

        $this->getEntityManager()->remove($clientSession);
        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function update(int $id, ClientSessionModel $clientSessions): string
    {
        $clientSession = $this->find($id);

        if ($clientSession == null) {
            return "No data found.";
        }

        $checkClientSession = $this->findOneBy([
            'clientId' => $clientSessions->getClientId(),
            'sessionId' => $clientSessions->getSessionId()
        ]);

        if ($checkClientSession != null) {
            return "Selected data conflicted with current .";
        }

        $this->cache->delete($this->cacheHelper->getAllClientSessionsKey());

        $clientSession->setClientId($clientSessions->getClientId());
        $clientSession->setSessionId($clientSessions->getSessionId());
        $clientSession->setRole(ClientSessionRole::from($clientSessions->getRole()));

        $this->getEntityManager()->persist($clientSession);
        $this->getEntityManager()->flush();

        return "OK";
    }
}
