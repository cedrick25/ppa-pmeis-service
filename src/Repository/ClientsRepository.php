<?php

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\Clients;
use App\Entity\ClientTypes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

use App\Model\Clients as ClientModel;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @method Clients|null find($id, $lockMode = null, $lockVersion = null)
 * @method Clients|null findOneBy(array $criteria, array $orderBy = null)
 * @method Clients[]    findAll()
 * @method Clients[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientsRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private CacheInterface $cache,
        private CacheHelper $cacheHelper,
        private AppDateHelper $appDateHelper,
    ){
        parent::__construct($registry, Clients::class);
    }

    /**
     * @throws InvalidArgumentException
     * @throws ORMException
     * @throws Exception
     */
    public function create(ClientModel $clientData): int | null
    {
        $client = $this->findOneBy([
            'cmisId' => $clientData->getCmisId(),
            'firstName' => $clientData->getFirstName(),
            'lastName' => $clientData->getLastName(),
            'deletedAt' => null
        ]);

        if ($client != null) {
            return null;
        }

        $this->cache->delete($this->cacheHelper->getAllClientsKey());

        $newClient = new Clients();
        $newClient->setCmisId($clientData->getCmisId());
        $newClient->setClientTypeId($clientData->getClientTypeId());
        $newClient->setFirstName($clientData->getFirstName());
        $newClient->setMiddleName($clientData->getMiddleName());
        $newClient->setLastName($clientData->getLastName());
        $newClient->setSuffix($clientData->getSuffix());
        $newClient->setGender($clientData->getGender());
        $newClient->setDateOfBirth($this->appDateHelper->convertStringToImmutableDate($clientData->getDateOfBirth()));
        $newClient->setOffenseCategory($clientData->getOffenseCategory());
        $newClient->setFieldOfficeId($clientData->getFieldOfficeId());
        $newClient->setRegionId($clientData->getRegionId());
        $newClient->setIsSeniorCitizen($clientData->isSeniorCitizen());
        $newClient->setIsPwd($clientData->isPwd());
        $newClient->setSupervisionStart($this->appDateHelper->convertStringToImmutableDate($clientData->getSupervisionStart()));
        $newClient->setSupervisionEnd($this->appDateHelper->convertStringToImmutableDate($clientData->getSupervisionEnd()));
        $newClient->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($newClient);
        $this->getEntityManager()->flush();

        return $newClient->getClientId();
    }

    /**
     * @throws InvalidArgumentException
     * @return Clients[]
     */
    public function list(): array
    {
        $cacheKey = $this->cacheHelper->getAllClientsKey();
        $expiration = $this->cacheHelper->getExpirationDateTime(24);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($cacheKey, $expiration) {
            $item->expiresAt($expiration);

            return $this->createQueryBuilder('cl')
                ->andWhere('cl.deletedAt IS NULL')
                ->orderBy('cl.clientId', 'DESC')
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
        // TODO: Check if there is an existing id in client sessions and rj conducted process
        $client = $this->isExistingById($id);

        if ($client == null) {
            return false;
        }

        $this->cache->delete($this->cacheHelper->getAllClientsKey());

        $this->getEntityManager()->remove($client);
        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws InvalidArgumentException
     */
    public function softDelete(int $id): bool
    {
        // TODO: Check if there is an existing id in client sessions and rj conducted process
        $client =$this->isExistingById($id);

        if ($client == null) {
            return false;
        }

        $this->cache->delete($this->cacheHelper->getAllClientsKey());

        $client->setDeletedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->flush();

        return true;
    }


    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function update(int $id, ClientModel $clientData): string
    {
        $client = $this->isExistingById($id);

        if (! $client) {
            return "No record found.";
        }

        if ($this->isConflicted($client, $clientData)) {
            return "Input data conflicted with current record.";
        }

        $this->cache->delete($this->cacheHelper->getAllClientsKey());

        $client->setClientTypeId($clientData->getClientTypeId());
        $client->setCmisId($clientData->getCmisId());
        $client->setFirstName($clientData->getFirstName());
        $client->setMiddleName($clientData->getMiddleName());
        $client->setLastName($clientData->getLastName());
        $client->setSuffix($clientData->getSuffix());
        $client->setGender($clientData->getGender());
        $client->setDateOfBirth($this->appDateHelper->convertStringToImmutableDate($clientData->getDateOfBirth()));
        $client->setOffenseCategory($clientData->getOffenseCategory());
        $client->setFieldOfficeId($clientData->getFieldOfficeId());
        $client->setRegionId($clientData->getRegionId());
        $client->setIsSeniorCitizen($clientData->isSeniorCitizen());
        $client->setIsPwd($clientData->isPwd());
        $client->setSupervisionStart($this->appDateHelper->convertStringToImmutableDate($clientData->getSupervisionStart()));
        $client->setSupervisionEnd($this->appDateHelper->convertStringToImmutableDate($clientData->getSupervisionEnd()));
        $client->setUpdatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->flush();

        return "OK";
    }

    public function isExistingById(int $id): bool | Clients
    {
        $client = $this->findOneBy([
            'clientId' => $id,
            'deletedAt' => null
        ]);

        if ($client == null) {
            return false;
        }

        return $client;
    }

    private function isConflicted(Clients $fetchedClient, ClientModel $clientData): bool
    {
        // Fetched and input client is the same.
        // It is trying to update itself.
        if (
            $fetchedClient->getCmisId() === $clientData->getCmisId() &&
            $fetchedClient->getFirstName() === $clientData->getFirstName() &&
            $fetchedClient->getLastName() === $clientData->getLastName()
        ) {
            return false;
        }

        $client = $this->findOneBy([
            'cmisId' => $clientData->getCmisId(),
            'firstName' => $clientData->getFirstName(),
            'lastName' => $clientData->getLastName(),
            'deletedAt' => null
        ]);

        // There is an existing record in the database.
        // It is trying to update another record that existing in the database.
        if ($client != null) {
            return true;
        }

        return false;
    }
}
