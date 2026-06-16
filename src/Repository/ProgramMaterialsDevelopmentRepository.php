<?php

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\ProgramMaterialsDevelopment;
use App\Enum\Response as ResponseEnum;
use App\Model\ProgramMaterialsDevelopment as ProgramMaterialsDevelopmentModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method ProgramMaterialsDevelopment|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProgramMaterialsDevelopment|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProgramMaterialsDevelopment[]    findAll()
 * @method ProgramMaterialsDevelopment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProgramMaterialsDevelopmentRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "program_materials_developments";

    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private Helper $helper,
        private AppDateHelper $appDateHelper,
    )
    {
        parent::__construct($registry, ProgramMaterialsDevelopment::class);
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllProgramMaterialsDevelopmentKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function () {
            return $this->createQueryBuilder('pmd')
                ->where('pmd.deletedAt IS NULL')
                ->orderBy('pmd.programMaterialsDevelopmentId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function create(ProgramMaterialsDevelopmentModel $data): int | null
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $entity = new ProgramMaterialsDevelopment();
        $entity->setParticulars($data->getParticulars());
        $entity->setDate($this->appDateHelper->convertStringToImmutableDate($data->getDate()));
        $entity->setProgram($data->getProgram());
        $entity->setUtilizedFor($data->getUtilizedFor()['value'] ?? '');
        $entity->setRemarks($data->getRemarks());
        $entity->setFieldOfficeId($data->getFieldOfficeId());
        $entity->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        return $entity->getProgramMaterialsDevelopmentId();
    }

    /**
     * @param int $page
     * @param int $pageSize
     * @param int $fieldOfficeId
     * @return array<string, mixed>
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function paginated(int $page = 1, int $pageSize = 10, int $fieldOfficeId): array
    {
        $params = [
            'cacheKey' => 'pmd_' . $page . '_' . $pageSize,
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG,
            'pageSize' => $pageSize,
            'page' => $page
        ];

        return $this->helper->createPaginatedResponseCustomQuery($params, function () use ($pageSize, $page, $fieldOfficeId) {
            $startOffset = $pageSize * ($page-1);
            $result = [];

            $conn = $this->getEntityManager()->getConnection();
            $sql = "SELECT pmd.*, fo.name field_office, r.region_id, r.name region
                    FROM program_materials_development as pmd
                    LEFT JOIN field_offices fo on pmd.field_office_id = fo.field_office_id
                    LEFT JOIN regions r on fo.region_id = r.region_id
                    WHERE pmd.deleted_at IS NULL ";

            if ($fieldOfficeId > 0) {
                $sql .= "AND pmd.field_office_id = $fieldOfficeId ";
            }

            $result['totalItems'] = $this->helper->getCustomQueryPaginatedTotalItems($conn, $sql);

            $sql .="LIMIT $pageSize OFFSET $startOffset";

            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();
            $result['data'] = $query->fetchAllAssociative();

            return $result;
        });
    }

    /**
     * @throws InvalidArgumentException
     */
    public function delete(int $id): bool
    {
        $programMaterialsDevelopment = $this->isExistingById($id);
        if (! $programMaterialsDevelopment) {
            return false;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->getEntityManager()->remove($programMaterialsDevelopment);
        $this->getEntityManager()->flush();

        return true;
    }

    public function isExistingById(int $id): bool | ProgramMaterialsDevelopment
    {
        $entity = $this->findOneBy([
            'programMaterialsDevelopmentId' => $id,
            'deletedAt' => null
        ]);

        return ($entity == null) ? false : $entity;
    }

    /**
     * @param string[] $minMaxDate
     * @param int $fieldOfficeId
     * @return array<int, array<string, mixed>>
     * @throws \Doctrine\DBAL\Exception
     */
    public function findByDateRange(array $minMaxDate, int $fieldOfficeId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $min = $minMaxDate['min'];
        $max = $minMaxDate['max'];

        $sql = "SELECT pmd.* FROM program_materials_development as pmd
                WHERE pmd.field_office_id = $fieldOfficeId
                AND pmd.date BETWEEN CAST('$min' AS DATE) AND CAST('$max' AS DATE)";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();

        return $query->fetchAllAssociative();
    }

    public function getVolunteerIdsByDateRange(array $minMaxDate, int $fieldOfficeId): ?array
    {
        return $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT DISTINCT ppr.person_responsible_id FROM program_materials_development pmd
                        LEFT JOIN pmd_person_responsible ppr ON pmd.program_materials_development_id = ppr.pmd_id
                        WHERE pmd.field_office_id = :fieldOfficeId
                        AND ppr.type = :type
                        AND pmd.date BETWEEN CAST(:min AS DATE) AND CAST(:max AS DATE)
                        AND pmd.deleted_at IS NULL",
                [
                    'min' => (string) $minMaxDate['min'],
                    'max' => (string) $minMaxDate['max'],
                    'type' => 'VPA',
                    'fieldOfficeId' => $fieldOfficeId,
                ]
            )->fetchAllAssociative();
    }

    public function getById(int $id): array | bool
    {
        return $this->getEntityManager()->getConnection()
            ->executeQuery(
                "SELECT pmd.*, fo.name field_office, r.region_id, r.name region
                    FROM program_materials_development as pmd
                    LEFT JOIN field_offices fo on pmd.field_office_id = fo.field_office_id
                    LEFT JOIN regions r on fo.region_id = r.region_id
                    WHERE pmd.program_materials_development_id = :id AND pmd.deleted_at IS NULL",
                ['id' => $id],
            )->fetchAssociative();
    }

    /**
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function update(int $id, ProgramMaterialsDevelopmentModel $data): string
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $entity = $this->isExistingById($id);

        if (null == $entity) {
            return ResponseEnum::NO_RECORD;
        }

        $entity->setParticulars($data->getParticulars());
        $entity->setDate($this->appDateHelper->convertStringToImmutableDate($data->getDate()));
        $entity->setProgram($data->getProgram());
        $entity->setUtilizedFor($data->getUtilizedFor()['value'] ?? '');
        $entity->setRemarks($data->getRemarks());
        $entity->setFieldOfficeId($data->getFieldOfficeId());
        $entity->setUpdatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        return ResponseEnum::OK;
    }
}
