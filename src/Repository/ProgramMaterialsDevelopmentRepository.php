<?php

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\ProgramMaterialsDevelopment;
use App\Model\ProgramMaterialsDevelopment as ProgramMaterialsDevelopmentModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\ORMException;
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
        private VolunteerRepository $volunteerRepository,
        private UserDetailsRepository $userDetailsRepository,
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

        return $this->helper->createCachedResponse($params, function() {
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
     * @throws ORMException
     */
    public function create(ProgramMaterialsDevelopmentModel $data): int | null
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $newProgramMaterialsDevelopment = new ProgramMaterialsDevelopment();
        $newProgramMaterialsDevelopment->setParticulars($data->getParticulars());
        $newProgramMaterialsDevelopment->setDate($this->appDateHelper->convertStringToImmutableDate($data->getDate()));
        $newProgramMaterialsDevelopment->setPersonResponsibleType($data->getPersonResponsibleType());
        $newProgramMaterialsDevelopment->setVpaPpoId($data->getVpaPpoId());
        $newProgramMaterialsDevelopment->setUtilizedFor($data->getUtilizedFor());
        $newProgramMaterialsDevelopment->setRemarks($data->getRemarks());
        $newProgramMaterialsDevelopment->setFieldOfficeId($data->getFieldOfficeId());
        $newProgramMaterialsDevelopment->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($newProgramMaterialsDevelopment);
        $this->getEntityManager()->flush();

        return $newProgramMaterialsDevelopment->getProgramMaterialsDevelopmentId();
    }

    /**
     * @throws InvalidArgumentException
     * @throws ORMException
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
        $programMaterialsDevelopment = $this->findOneBy([
            'programMaterialsDevelopmentId' => $id,
            'deletedAt' => null
        ]);

        return ($programMaterialsDevelopment == null) ? false : $programMaterialsDevelopment;
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

        $sql = "SELECT pmd.* FROM program_materials_development as pmd
                WHERE pmd.field_office_id = $fieldOfficeId AND pmd.date BETWEEN CAST('$min' AS DATE) AND CAST('$max' AS DATE)";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();

        return $query->fetchAllAssociative();
    }
}
