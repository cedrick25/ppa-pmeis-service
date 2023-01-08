<?php

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\IdSupport;
use App\Enum\Response as ResponseEnum;
use App\Model\IdSupport as IdSupportModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
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
        private VolunteerRepository $volunteerRepository,
        private UserDetailsRepository $userDetailsRepository,
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
        $newIdSupport->setActivity($data->getActivity());
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

    /**
     * @param string[] $minMaxDate
     * @param int $fieldOfficeId
     * @return array<int, array<string, mixed>>
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function findByDateRange(array $minMaxDate, int $fieldOfficeId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $min = $minMaxDate['min'];
        $max = $minMaxDate['max'];

        $sql = "SELECT ids.*, fo.name as field_office FROM id_support as ids
                LEFT JOIN field_offices as fo ON ids.field_office_id = fo.field_office_id
                WHERE ids.field_office_id = $fieldOfficeId AND ids.date BETWEEN CAST('$min' AS DATE) AND CAST('$max' AS DATE)";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();
        $rows = $query->fetchAllAssociative();
        $result = [];

        foreach ($rows as $row) {
            if ($row['type'] === 'VPA') {
                $vpa = $this->volunteerRepository->find(intval($row['vpa_personnel_id']));
                $name = $vpa->getFirstName() . ' ' . $vpa->getMiddleName() . ' ' . $vpa->getLastName();
            } else {
                $userDetail = $this->userDetailsRepository->findOneBy(['userAccountId' => $row['vpa_personnel_id']]);
                $name = $userDetail->getFirstName() . ' ' . $userDetail->getMiddleName() . ' ' . $userDetail->getLastName();
            }
            $row['name'] = $name;
            $result[] = $row;
        }

        return $result;
    }

    public function getVolunteerIdsByDateRange(array $minMaxDate, int $fieldOfficeId): ?array
    {
        $conn = $this->getEntityManager()->getConnection();
        $min = $minMaxDate['min'];
        $max = $minMaxDate['max'];

        $sql = "SELECT DISTINCT is2.vpa_personnel_id FROM id_support is2
                WHERE is2.field_office_id = $fieldOfficeId
                AND is2.type = 'VPA'
                AND is2.date BETWEEN CAST('$min' AS DATE) AND CAST('$max' AS DATE)
                AND is2.deleted_at IS NULL
                ";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();

        return $query->fetchAllAssociative();
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function update(int $id, IdSupportModel $data): string
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $entity = $this->isExistingById($id);

        if (! $entity) {
            return ResponseEnum::NO_RECORD;
        }

        $entity->setType($data->getType());
        $entity->setVpaPersonnelId($data->getVpaPersonnelId());
        $entity->setProgram($data->getProgram());
        $entity->setFieldOfficeId($data->getFieldOfficeId());
        $entity->setActivity($data->getActivity());
        $entity->setDate($this->appDateHelper->convertStringToImmutableDate($data->getDate()));
        $entity->setVenue($data->getVenue());
        $entity->setAssistanceRendered($data->getAssistanceRendered());
        $entity->setUpdatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        return ResponseEnum::OK;
    }

    public function getById(int $id): array | bool
    {
        return $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT ids.*, fo.name as field_office, r.region_id, r.name region FROM id_support as ids
                LEFT JOIN field_offices as fo ON ids.field_office_id = fo.field_office_id
                LEFT JOIN regions r on fo.region_id = r.region_id
                WHERE ids.id = :id",
                ['id' => $id]
            )->fetchAssociative();
    }

    public function getServicesByDateRangeAndVolunteerIds(array $minMaxDate, array $volunteerIds): ?array
    {
        return $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT DISTINCT is2.vpa_personnel_id FROM id_support is2
                    WHERE is2.vpa_personnel_id IN (:volunteerIds)
                    AND is2.type = :type
                    AND is2.date BETWEEN CAST(:min AS DATE) AND CAST(:max AS DATE)
                    AND is2.deleted_at IS NULL",
                [
                    'min' => $minMaxDate['min'],
                    'max' => $minMaxDate['max'],
                    'type' => 'VPA',
                    'volunteerIds' => $volunteerIds
                ],
                ['volunteerIds' => Connection::PARAM_INT_ARRAY],
            )->fetchAllAssociative();
    }
}
