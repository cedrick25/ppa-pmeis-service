<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\VolunteerOperations;
use App\Model\VolunteerOperations as VolunteerOperationsModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method VolunteerOperations|null find($id, $lockMode = null, $lockVersion = null)
 * @method VolunteerOperations|null findOneBy(array $criteria, array $orderBy = null)
 * @method VolunteerOperations[]    findAll()
 * @method VolunteerOperations[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VolunteerOperationsRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "volunteer_operations";

    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private Helper $helper,
        private AppDateHelper $appDateHelper,
    ){
        parent::__construct($registry, VolunteerOperations::class);
    }

    /**
     * @return VolunteerOperations[]
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllVolunteerOperationsKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function() {
            return $this->createQueryBuilder('vo')
                ->orderBy('vo.volunteerOperationId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @throws InvalidArgumentException
     * @throws ORMException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws \Exception
     */
    public function create(VolunteerOperationsModel $data): int | null
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $newVolunteerOperations = new VolunteerOperations();
        $newVolunteerOperations->setVolunteerId($data->getVolunteerId());
        $newVolunteerOperations->setStatus($data->getStatus());
        $newVolunteerOperations->setDate($this->appDateHelper->convertStringToImmutableDate($data->getDate()));
        $newVolunteerOperations->setReason($data->getReason());
        $newVolunteerOperations->setDroppedBy($data->getDroppedBy());

        $this->getEntityManager()->persist($newVolunteerOperations);
        $this->getEntityManager()->flush();

        return $newVolunteerOperations->getVolunteerId();
    }

    public function isExistingById(int $id): bool | VolunteerOperations
    {
        $volunteerOperation = $this->findOneBy([
            'volunteerOperationId' => $id
        ]);

        return ($volunteerOperation == null) ? false : $volunteerOperation;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function findByFieldOfficeAndMonthRange(int $fieldOfficeId, int $year, array $months, string $status): array
    {
        $months = implode(',', $months);
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT vo.*, v.first_name, v.middle_name, v.last_name, v.gender, v.is_pwd, v.is_senior_citizen  
                FROM volunteer_operations as vo " .
            "LEFT JOIN volunteer as v ON vo.volunteer_id = v.volunteer_id " .
            "WHERE v.field_office_id = $fieldOfficeId AND YEAR(vo.date) = $year 
            AND MONTH(vo.date) IN ($months) AND vo.status = '$status' ORDER BY vo.date DESC";
        dd($sql);

        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();

        return $query->fetchAllAssociative();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function findVolunteerIdsByMonthRange(int $year, array $months, string $status): array
    {
        $months = implode(',', $months);
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT volunteer_id, date, reason from volunteer_operations WHERE volunteer_operation_id IN
                (SELECT MAX(volunteer_operation_id) FROM volunteer_operations GROUP BY volunteer_id) AND
                YEAR(date) = $year AND MONTH(date) IN ($months) AND status = '$status'";

        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();

        return $query->fetchAllAssociative();
    }
}
