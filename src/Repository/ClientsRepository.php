<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\Clients;
use App\Enum\Response as ResponseEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use App\Model\Clients as ClientModel;
use App\Service\Cmis\CmisSource;
use Exception;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method Clients|null find($id, $lockMode = null, $lockVersion = null)
 * @method Clients|null findOneBy(array $criteria, array $orderBy = null)
 * @method Clients[]    findAll()
 * @method Clients[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientsRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "clients";

    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private AppDateHelper $appDateHelper,
        private Helper $helper,
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
        if ($this->isExisting($clientData)) {
            return null;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);
        $newClient = new Clients();
        $newClient->setCmisId($clientData->getCmisId());
        $newClient->setClientTypeId($clientData->getClientTypeId());
        $newClient->setFirstName($clientData->getFirstName());
        $newClient->setMiddleName($clientData->getMiddleName());
        $newClient->setLastName($clientData->getLastName());
        $newClient->setFullName($clientData->getFullName());
        $newClient->setSuffix($clientData->getSuffix());
        $newClient->setAlias($clientData->getAlias());
        $newClient->setGender($clientData->getGender());
        $newClient->setDateOfBirth($clientData->getDateOfBirth());
        $newClient->setOffenseCategory($clientData->getOffenseCategory());
        $newClient->setFieldOfficeId($clientData->getFieldOfficeId());
        $newClient->setIsSeniorCitizen($clientData->isSeniorCitizen());
        $newClient->setIsPwd($clientData->isPwd());
        $newClient->setSupervisionStart($this->appDateHelper->convertStringToImmutableDate($clientData->getSupervisionStart()));
        $newClient->setSupervisionEnd($this->appDateHelper->convertStringToImmutableDate($clientData->getSupervisionEnd()));
        $newClient->setClientRemarksId($clientData->getClientRemarksId());
        $newClient->setCmisDocketNo($clientData->getCmisDocketNo());
        $newClient->setCmisCaseClassification($clientData->getCmisCaseClassification());
        $newClient->setCmisYM($clientData->getCmisYM());
        $newClient->setCmisSource($clientData->getCmisSource());
        $newClient->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());
        $newClient->setUpdatedAt($this->appDateHelper->getCurrentImmutableDate());
        $this->getEntityManager()->persist($newClient);
        $this->getEntityManager()->flush();

        return $newClient->getClientId();
    }

    /**
     * @return array<string, mixed>|null
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function list(): ?array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllClientsKey(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponseCustomQuery($params, function() {
            $conn = $this->getEntityManager()->getConnection();

            $sql = "SELECT c.*, ct.code as client_type_code, ct.description as client_type_description, fo.name as field_office_name,
                 rg.region_id, rg.name as region_name FROM clients as c " .
                "LEFT JOIN client_types as ct ON c.client_type_id = ct.client_type_id " .
                "LEFT JOIN field_offices as fo ON c.field_office_id = fo.field_office_id " .
                "LEFT JOIN regions as rg ON fo.region_id = rg.region_id " .
                "WHERE c.deleted_at IS NULL ORDER BY c.client_id DESC";
            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();

            return $query->fetchAllAssociative();
        });
    }

    /**
     * @return array<string, mixed>|null
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function listByFieldOffice(int $fieldOfficeId, int $clientTypeId): ?array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllClientsByFieldOfficeKeyAndClientTypeId($fieldOfficeId, $clientTypeId),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponseCustomQuery($params, function() use($fieldOfficeId, $clientTypeId) {
            $conn = $this->getEntityManager()->getConnection();

            $sql = "SELECT c.*, ct.code as client_type_code, ct.description as client_type_description, fo.name as field_office_name,
                 rg.region_id, rg.name as region_name FROM clients as c
                 LEFT JOIN client_types as ct ON c.client_type_id = ct.client_type_id
                 LEFT JOIN field_offices as fo ON c.field_office_id = fo.field_office_id
                 LEFT JOIN regions as rg ON fo.region_id = rg.region_id
                 WHERE c.field_office_id = $fieldOfficeId
                   AND c.client_type_id = $clientTypeId
                   AND c.deleted_at IS NULL ORDER BY c.client_id DESC";
            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();

            return $query->fetchAllAssociative();
        });
    }

    /**
     * @throws InvalidArgumentException
     * @throws ORMException
     */
    public function delete(int $id): bool
    {
        $client = $this->isExistingById($id);

        if (! $client) {
            return false;
        }

        // TODO: Check if there is an existing id in client sessions and rj conducted process

        $this->cache->invalidateTags([self::CACHE_TAG]);

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

        $this->cache->invalidateTags([self::CACHE_TAG]);

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
            return ResponseEnum::NO_RECORD;
        }

        if ($this->isConflicted($client, $clientData)) {
            return ResponseEnum::CONFLICTED_INPUT;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $client->setClientTypeId($clientData->getClientTypeId());
        $client->setCmisId($clientData->getCmisId());
        $client->setFirstName($clientData->getFirstName());
        $client->setMiddleName($clientData->getMiddleName());
        $client->setLastName($clientData->getLastName());
        $client->setFullName($clientData->getFullName());
        $client->setSuffix($clientData->getSuffix());
        $client->setAlias($clientData->getAlias());
        $client->setGender($clientData->getGender());
        $client->setDateOfBirth($clientData->getDateOfBirth());
        $client->setOffenseCategory($clientData->getOffenseCategory());
        $client->setFieldOfficeId($clientData->getFieldOfficeId());
        $client->setIsSeniorCitizen($clientData->isSeniorCitizen());
        $client->setIsPwd($clientData->isPwd());
        $client->setSupervisionStart($this->appDateHelper->convertStringToImmutableDate($clientData->getSupervisionStart()));
        $client->setSupervisionEnd($this->appDateHelper->convertStringToImmutableDate($clientData->getSupervisionEnd()));
        $client->setClientRemarksId($clientData->getClientRemarksId());
        $client->setCmisDocketNo($clientData->getCmisDocketNo());
        $client->setCmisCaseClassification($clientData->getCmisCaseClassification());
        $client->setCmisYM($clientData->getCmisYM());
        $client->setCmisSource($clientData->getCmisSource());
        $client->setUpdatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->flush();

        return ResponseEnum::OK;
    }

    /**
     * @throws Exception
     */
    public function createFromCmis(ClientModel $clientData): int | null
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);
        $now = $this->appDateHelper->getCurrentImmutableDate();

        $newClient = new Clients();
        $newClient->setCmisId($clientData->getCmisId());
        $newClient->setClientTypeId($clientData->getClientTypeId());
        $newClient->setFirstName($clientData->getFirstName());
        $newClient->setMiddleName($clientData->getMiddleName());
        $newClient->setLastName($clientData->getLastName());
        $newClient->setFullName($clientData->getFullName());
        $newClient->setSuffix($clientData->getSuffix());
        $newClient->setAlias($clientData->getAlias());
        $newClient->setGender($clientData->getGender());
        $newClient->setDateOfBirth($clientData->getDateOfBirth());
        $newClient->setOffenseCategory($clientData->getOffenseCategory());
        $newClient->setFieldOfficeId($clientData->getFieldOfficeId());
        $newClient->setIsSeniorCitizen($clientData->isSeniorCitizen());
        $newClient->setIsPwd($clientData->isPwd());
        $newClient->setSupervisionStart($this->appDateHelper->convertStringToImmutableDate($clientData->getSupervisionStart()));
        $newClient->setSupervisionEnd($this->appDateHelper->convertStringToImmutableDate($clientData->getSupervisionEnd()));
        $newClient->setClientRemarksId($clientData->getClientRemarksId());
        $newClient->setCmisDocketNo($clientData->getCmisDocketNo());
        $newClient->setCmisCaseClassification($clientData->getCmisCaseClassification());
        $newClient->setCmisYM($clientData->getCmisYM());
        $newClient->setCmisSource($clientData->getCmisSource());
        $newClient->setCmisSyncedAt($now);
        $newClient->setCmisLastSeenAt($now);
        $newClient->setCreatedAt($now);
        $newClient->setUpdatedAt($now);

        $this->getEntityManager()->persist($newClient);
        $this->getEntityManager()->flush();

        return $newClient->getClientId();
    }

    /**
     * @throws Exception
     */
    public function updateFromCmis(Clients $client, ClientModel $clientData): void
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);
        $now = $this->appDateHelper->getCurrentImmutableDate();

        $client->setCmisId($clientData->getCmisId());
        $client->setClientTypeId($clientData->getClientTypeId());
        $client->setFirstName($clientData->getFirstName());
        $client->setMiddleName($clientData->getMiddleName());
        $client->setLastName($clientData->getLastName());
        $client->setFullName($clientData->getFullName());
        $client->setSuffix($clientData->getSuffix());
        $client->setAlias($clientData->getAlias());
        $client->setFieldOfficeId($clientData->getFieldOfficeId());
        $client->setSupervisionStart($this->appDateHelper->convertStringToImmutableDate($clientData->getSupervisionStart()));
        $client->setSupervisionEnd($this->appDateHelper->convertStringToImmutableDate($clientData->getSupervisionEnd()));
        $client->setCmisDocketNo($clientData->getCmisDocketNo());
        $client->setCmisCaseClassification($clientData->getCmisCaseClassification());
        $client->setCmisYM($clientData->getCmisYM());
        $client->setCmisSource($clientData->getCmisSource());
        $client->setCmisSyncedAt($now);
        $client->setCmisLastSeenAt($now);
        $client->setUpdatedAt($now);

        $this->getEntityManager()->flush();
    }

    /**
     * @param int $id
     * @return bool|array<string, mixed>
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getById(int $id): bool|array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT c.*, ct.code as client_type_code, ct.description as client_type_description, fo.name as field_office_name,
                 rg.region_id, rg.name as region_name FROM clients as c " .
            "LEFT JOIN client_types as ct ON c.client_type_id = ct.client_type_id " .
            "LEFT JOIN field_offices as fo ON c.field_office_id = fo.field_office_id " .
            "LEFT JOIN regions as rg ON fo.region_id = rg.region_id " .
            "WHERE c.client_id = $id AND c.deleted_at IS NULL ORDER BY c.client_id DESC";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();

        return $query->fetchAssociative();
    }

    public function isExistingById(int $id): bool | Clients
    {
        $client = $this->findOneBy([
            'clientId' => $id,
            'deletedAt' => null
        ]);

        return ($client == null) ? false : $client;
    }

    public function findOneActiveByCmisId(int $cmisId): ?Clients
    {
        return $this->findOneActiveByCmisSourceAndId(CmisSource::F5T7, $cmisId);
    }

    public function findOneActiveByCmisSourceAndId(string $cmisSource, int $cmisId): ?Clients
    {
        return $this->findOneBy([
            'cmisSource' => $cmisSource,
            'cmisId' => $cmisId,
            'deletedAt' => null,
        ]);
    }

    public function findOneActiveByCmisDocketNo(string $docketNo): ?Clients
    {
        return $this->findOneBy([
            'cmisDocketNo' => $docketNo,
            'deletedAt' => null,
        ]);
    }

    /**
     * @return Clients[]
     * @throws \Doctrine\DBAL\Exception
     */
    public function findActiveDuplicateCandidates(ClientModel $clientData): array
    {
        if (!$this->isValidSqlDate($clientData->getSupervisionStart())
            || !$this->isValidSqlDate($clientData->getSupervisionEnd())) {
            return [];
        }

        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT * FROM clients
                WHERE deleted_at IS NULL
                  AND cmis_id IS NULL
                  AND LOWER(TRIM(first_name)) = LOWER(TRIM(:first_name))
                  AND LOWER(TRIM(last_name)) = LOWER(TRIM(:last_name))
                  AND field_office_id = :field_office_id
                  AND supervision_start = :supervision_start
                  AND supervision_end = :supervision_end";

        return $conn->executeQuery($sql, [
            'first_name' => $clientData->getFirstName(),
            'last_name' => $clientData->getLastName(),
            'field_office_id' => $clientData->getFieldOfficeId(),
            'supervision_start' => $clientData->getSupervisionStart(),
            'supervision_end' => $clientData->getSupervisionEnd(),
        ])->fetchAllAssociative();
    }

    private function isValidSqlDate(string $value): bool
    {
        if ($value === '' || str_starts_with($value, '0000-00-00')) {
            return false;
        }

        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $value);

        return $date !== false
            && $date->format('Y-m-d') === $value
            && (int) $date->format('Y') >= 1900;
    }

    /**
     * @param int $page
     * @param int $pageSize
     * @param int $fieldOfficeId
     * @return array<string, mixed>|null
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function paginated(int $page = 1, int $pageSize = 10, int $filedOfficeId): ?array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getClientsPaginatedKey($page, $pageSize),
            'cacheTag' => self::CACHE_TAG,
            'pageSize' => $pageSize,
            'page' => $page
        ];

        return $this->helper->createPaginatedResponseCustomQuery($params, function() use ($pageSize, $page, $filedOfficeId) {
            $conn = $this->getEntityManager()->getConnection();
            $startOffset = $pageSize * ($page-1);
            $result = [];

            $sql = "SELECT c.*, ct.code as client_type_code, ct.description as client_type_description, fo.name as field_office_name,
                        rg.region_id, rg.name as region_name FROM clients as c
                    LEFT JOIN client_types as ct ON c.client_type_id = ct.client_type_id
                    LEFT JOIN field_offices as fo ON c.field_office_id = fo.field_office_id
                    LEFT JOIN regions as rg ON fo.region_id = rg.region_id
                    WHERE c.deleted_at IS NULL ";

            if ($filedOfficeId > 0) {
                $sql .= "AND c.field_office_id = $filedOfficeId ";
            }

            $result['totalItems'] = $this->helper->getCustomQueryPaginatedTotalItems($conn, $sql);

            $sql .= "ORDER BY c.client_id DESC LIMIT $pageSize OFFSET $startOffset";

            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();
            $result['data'] = $query->fetchAllAssociative();

            return $result;
        });
    }

    /**
     * @param array $ids
     * @return Clients[]
     */
    public function findByIds(array $ids): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.clientId IN (:ids)')
            ->where('c.deletedAt IS NULL')
            ->setParameter('ids', $ids, Connection::PARAM_INT_ARRAY)
            ->getQuery()
            ->getResult();

    }

    /**
     * @param int $id
     * @return Clients[]
     */
    public function findByClientTypeId(int $id): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.clientTypeId = :id')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }

    public function findSupervisionCasesDropBySupervisionPeriodEndDateRangeLess(array $minMaxDate, int $fieldOfficeId): array
    {
        $predicate = 's.date BETWEEN CAST("'.$minMaxDate['min'].'" AS DATE) AND CAST("'.$minMaxDate['max'].'" AS DATE)';
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT
                    c.client_id,
                    c.client_type_id,
                    cs.client_remarks_id,
                    c.supervision_start,
                    c.supervision_end
                FROM client_sessions as cs
                LEFT JOIN clients c on cs.client_id = c.client_id
                LEFT JOIN sessions s on cs.session_id = s.session_id
                WHERE (c.field_office_id = $fieldOfficeId AND $predicate AND cs.client_remarks_id = 13 AND c.deleted_at IS NULL)
                   OR (c.field_office_id = $fieldOfficeId AND $predicate AND cs.client_remarks_id = 7 AND c.deleted_at IS NULL)
                   OR (c.field_office_id = $fieldOfficeId AND $predicate AND cs.client_remarks_id = 6 AND c.deleted_at IS NULL)
                   OR (c.field_office_id = $fieldOfficeId AND $predicate AND cs.client_remarks_id = 8 AND c.deleted_at IS NULL)
                   OR (c.field_office_id = $fieldOfficeId AND $predicate AND cs.client_remarks_id = 9 AND c.deleted_at IS NULL)
                   OR (c.field_office_id = $fieldOfficeId AND $predicate AND cs.client_remarks_id = 10 AND c.deleted_at IS NULL)
                   OR (c.field_office_id = $fieldOfficeId AND $predicate AND cs.client_remarks_id = 11 AND c.deleted_at IS NULL)
                   OR (c.field_office_id = $fieldOfficeId AND $predicate AND cs.client_remarks_id = 12 AND c.deleted_at IS NULL)
                ORDER BY s.date DESC
                ";

        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();

        return $query->fetchAllAssociative();
    }

    /**
     * @param int[] $ids
     * @return void
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function batchDateUpdate(array $ids): void
    {
        $clientIds = implode(',', $ids);
        $currentDate = $this->appDateHelper->getCurrentImmutableDate()->format('Y-m-d H:m:s');
        $conn = $this->getEntityManager()->getConnection();
        $sql = "UPDATE clients SET clients.updated_at = '$currentDate' WHERE clients.client_id IN ($clientIds)";
        $stmt = $conn->prepare($sql);

        $stmt->executeQuery();
    }

    /**
     * @param string[] $minMaxDate
     * @param int $fieldOfficeId
     * @return int[]
     * @throws \Doctrine\DBAL\Exception
     */
    public function findClientsIdUnderSupervisionPeriod(array $minMaxDate, int $fieldOfficeId): array
    {
        $predicate = 'c.supervision_end BETWEEN CAST("'.$minMaxDate['min'].'" AS DATE) AND CAST("'.$minMaxDate['max'].'" AS DATE)';

        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT c.client_id FROM clients c
                WHERE $predicate AND c.field_office_id = $fieldOfficeId
                AND c.deleted_at IS NULL";

        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();
        $data = [];

        foreach ($query->fetchAllAssociative() as $client) {
            $data[] = intval($client['client_id']);
        }

        return $data;
    }

    private function isExisting(ClientModel $clientData): bool
    {
        $client = $this->findOneBy([
            'cmisId' => $clientData->getCmisId(),
            'firstName' => $clientData->getFirstName(),
            'lastName' => $clientData->getLastName(),
            'deletedAt' => null
        ]);

        return $client != null;
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

        // There is an existing record in the database.
        // It is trying to update another record that existing in the database.
        if ($this->isExisting($clientData)) {
            return true;
        }

        return false;
    }
}
