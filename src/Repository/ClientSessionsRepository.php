<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\ClientSessions;
use App\Enum\Response as ResponseEnum;
use App\Model\ClientSessions as ClientSessionModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

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
        private Helper $helper,
        private AppDateHelper $appDateHelper,
        private ClientsRepository $clientsRepository,
    ) {
        parent::__construct($registry, ClientSessions::class);
    }

    /**
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
        $newClientSession->setRole($clientSessions->getRole());

        $this->getEntityManager()->persist($newClientSession);
        $this->getEntityManager()->flush();

        return $newClientSession->getClientSessionId();
    }

    /**
     * @param int $sessionId
     * @param array<string, mixed> $attendees
     * @throws InvalidArgumentException
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function batchCreate(int $sessionId, array $attendees): void
    {
        $clientIds = [];
        foreach ($attendees as $attendee) {
            $clientSession = new ClientSessions();
            $clientSession->setSessionId($sessionId);
            $clientSession->setClientId($attendee['id']['value']);
            $clientSession->setRole($this->convertClientRole($attendee['type']['label']));
            $clientSession->setFsi($attendee['fsi']['value']);
            $clientIds[] = intval($attendee['id']['value']);

            $this->getEntityManager()->persist($clientSession);
        }

        $this->clientsRepository->batchDateUpdate($clientIds);

        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear(ClientSessions::class);

        $this->cache->invalidateTags([self::CACHE_TAG]);
    }

    /**
     * @param int $sessionId
     * @param array<string, mixed> $absentees
     * @throws InvalidArgumentException
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws Exception
     */
    public function batchCreateAbsentees(int $sessionId, array $absentees): void
    {
        $clientIds = [];
        foreach ($absentees as $absentee) {
            $clientSession = new ClientSessions();
            $clientSession->setSessionId($sessionId);
            $clientSession->setClientId($absentee['id']['value']);
            $clientSession->setRole($this->convertClientRole($absentee['type']['label']));
            $clientSession->setClientRemarksId($absentee['remarks']['value']);
            $clientSession->setRemarksDate(
                $this->appDateHelper->convertStringToImmutableDate($absentee['remarksDate'])
            );
            $clientSession->setOtherRemarks($absentee['otherRemarks']);
            $clientIds[] = intval($absentee['id']['value']);

            $this->getEntityManager()->persist($clientSession);
        }

        $this->clientsRepository->batchDateUpdate($clientIds);

        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear(ClientSessions::class);

        $this->cache->invalidateTags([self::CACHE_TAG]);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function deleteBySessionId(int $sessionId): void
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "DELETE FROM client_sessions WHERE session_id = $sessionId";
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();
    }

    /**
     * @return ClientSessions[]
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllClientSessionsKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function () {
            return $this->createQueryBuilder('cs')
                ->orderBy('cs.clientSessionId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @return ClientSessions[]
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function listBySessionId(int $id): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllClientSessionsBySessionIdKey($id),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function () use ($id) {
            return $this->createQueryBuilder('cs')
                ->where('cs.sessionId = :id')
                ->setParameter('id', $id)
                ->orderBy('cs.clientSessionId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @throws InvalidArgumentException
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
        $clientSession->setRole($clientSessionData->getRole());

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
     * @return array<string, mixed>
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function paginated(int $page = 1, int $pageSize = 10): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getClientsPaginatedKey($page, $pageSize),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG,
            'pageSize' => $pageSize,
            'page' => $page
        ];

        return $this->helper->createPaginatedResponse($params, function () {
            return $this->createQueryBuilder('cs')->orderBy('cs.clientSessionId');
        });
    }

    /**
     * @return array<string, mixed>
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function paginatedSearch(string $query, string $order = 'ASC', int $page = 1, int $pageSize = 10): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getClientsPaginatedKey($page, $pageSize),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG,
            'pageSize' => $pageSize,
            'page' => $page
        ];

        return $this->helper->createPaginatedResponse($params, function () use ($query) {
            return $this->createQueryBuilder('cs')
                ->where(" LIKE %:query%")
                ->setParameter('query', $query)
                ->orderBy('cs.clientSessionId');
        });
    }

    /**
     * @param int[] $sessionIds
     * @return array<int, array<string, mixed>>
     * @throws \Doctrine\DBAL\Exception
     */
    public function findBySessionIds(array $sessionIds): array
    {
        return $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT cs.*, cr.name as client_remarks, s.trees_planted, c.offense_category, c.gender, c.is_pwd,
                        c.is_senior_citizen, p.name as phase, ct.description as client_type, s.field_office_id
                    FROM client_sessions cs
                    LEFT JOIN clients c on cs.client_id = c.client_id
                    LEFT JOIN client_remarks cr on c.client_remarks_id = cr.client_remarks_id
                    LEFT JOIN sessions s on cs.session_id = s.session_id
                    LEFT JOIN phases p on s.phase_id = p.phase_id
                    LEFT JOIN client_types ct on c.client_type_id = ct.client_type_id
                    WHERE cs.session_id IN (:sessionIds)",
                ['sessionIds' => $sessionIds],
                ['sessionIds' => Connection::PARAM_INT_ARRAY],
            )->fetchAllAssociative();
    }

    /**
     * @param int[] $sessionIds
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAbsenteesBySessionIds(array $sessionIds): array
    {
        $query = $this->getEntityManager()->getConnection()->executeQuery(
            "SELECT * FROM client_sessions cs WHERE cs.session_id IN (:sessionIds)
                    AND cs.client_remarks_id IS NOT NULL",
            ['sessionIds' => $sessionIds],
            ['sessionIds' => Connection::PARAM_INT_ARRAY],
        );
        $result = [];

        foreach ($query->fetchAllAssociative() as $row) {
            $result[] = new ClientSessionModel(
                (int) $row['client_id'],
                (int) $row['session_id'],
                $row['role'],
                (int) $row['client_remarks_id'],
                $row['other_remarks'],
                filter_var($row['fsi'], FILTER_VALIDATE_BOOLEAN)
            );
        }

        return $result;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAbsenteesRemarksIdAndFieldOfficeIdBySessionId(array $sessionIds): array
    {
        return $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT cs.client_remarks_id, s.field_office_id FROM client_sessions cs
                LEFT JOIN sessions s on cs.session_id = s.session_id
                WHERE cs.session_id IN (:sessionIds) AND cs.client_remarks_id IS NOT NULL",
                ['sessionIds' => $sessionIds],
                ['sessionIds' => Connection::PARAM_INT_ARRAY],
            )->fetchAllAssociative();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getVPA3Report(int $volunteerId): array
    {
        return $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT c.first_name, c.middle_name, c.last_name, c.gender, sr.name as service_rendered,
                        vs.community_resources_tapped, vs.assistance_received, vs.remarks
                    FROM volunteer_supervisions vs
                    LEFT JOIN volunteer_supervision_clients vsc
                        ON vs.volunteer_supervisions_id = vsc.volunteer_supervision_id
                    LEFT JOIN clients c ON vsc.client_id = c.client_id
                    LEFT JOIN services_rendered sr ON vs.services_rendered_id = sr.services_rendered_id
                    WHERE vs.volunteer_id = :volunteerId",
                ['volunteerId' => $volunteerId]
            )->fetchAllAssociative();
    }

    /**
     * @param int[] $ids
     * @return array<int, array<string, mixed>>
     * @throws \Doctrine\DBAL\Exception
     */
    public function findClientsBySessionIds(array $ids): array
    {
        return $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT c.*, ct.code client_type_code, cs.session_id FROM client_sessions cs
                    LEFT JOIN clients c on cs.client_id = c.client_id
                    LEFT JOIN client_types ct on c.client_type_id = ct.client_type_id
                    WHERE cs.session_id IN (:ids)",
                ['ids' => $ids],
                ['ids' => Connection::PARAM_INT_ARRAY],
            )->fetchAllAssociative();
    }

    /**
     * @param int[] $ids
     * @return array<int, array<string, mixed>>
     * @throws \Doctrine\DBAL\Exception
     */
    public function findClientAttendeesBySessionIds(array $ids): array
    {
        $query = $this->getEntityManager()->getConnection()->executeQuery(
            "SELECT * FROM client_sessions WHERE client_sessions.session_id IN (:ids)
                    AND client_sessions.client_remarks_id IS NULL",
            ['ids' => $ids],
            ['ids' => Connection::PARAM_INT_ARRAY],
        );
        $result = [];

        foreach ($query->fetchAllAssociative() as $row) {
            $result[] = new ClientSessionModel(
                (int) $row['client_id'],
                (int) $row['session_id'],
                $row['role'],
                (int) $row['client_remarks_id'],
                $row['other_remarks'],
                filter_var($row['fsi'], FILTER_VALIDATE_BOOLEAN)
            );
        }

        return $result;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function duplicate(int $sessionId, int $newSessionId): void
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "INSERT INTO client_sessions
                    (client_id, session_id, role, client_remarks_id, other_remarks, fsi, remarks_date)
                SELECT  client_id, $newSessionId, role, client_remarks_id, other_remarks, fsi, remarks_date
                FROM client_sessions WHERE session_id = $sessionId";
        $stmt = $conn->prepare($sql);

        $stmt->executeQuery();
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

    private function convertClientRole(string $fullRole): string
    {
        $conversions = [
            'Probationers' => 'PS',
            'Parolees' => 'PR',
            'Pardonees' => 'PD',
            'FTMDOs' => 'FTMDO',
            'Petitioners' => 'PET',
            'Terminated' => 'TERM',
            'JICLs' => 'JICL'
        ];

        return $conversions[$fullRole];
    }
}
