<?php

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\TechnicalAssistance;
use App\Model\TechnicalAssistance as TechnicalAssistanceModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method TechnicalAssistance|null find($id, $lockMode = null, $lockVersion = null)
 * @method TechnicalAssistance|null findOneBy(array $criteria, array $orderBy = null)
 * @method TechnicalAssistance[]    findAll()
 * @method TechnicalAssistance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TechnicalAssistanceRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "technical_assistance";

    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private Helper $helper,
        private AppDateHelper $appDateHelper,
        private VolunteerRepository $volunteerRepository,
        private UserDetailsRepository $userDetailsRepository,
    ) {
        parent::__construct($registry, TechnicalAssistance::class);
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllTechnicalAssistanceKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function () {
            return $this->createQueryBuilder('ta')
                ->where('ta.deletedAt IS NULL')
                ->orderBy('ta.id', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function create(TechnicalAssistanceModel $data): int | null
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $newTechnicalAssistance = new TechnicalAssistance();
        $newTechnicalAssistance->setActivityName($data->getActivityName());
        $newTechnicalAssistance->setAgencyName($data->getAgencyName());
        $newTechnicalAssistance->setDate($this->appDateHelper->convertStringToImmutableDate($data->getDate()));
        $newTechnicalAssistance->setVenue($data->getVenue());
        $newTechnicalAssistance->setFieldOfficeId($data->getFieldOfficeId());
        $newTechnicalAssistance->setParticipantsNo($data->getParticipantsNo());
        $newTechnicalAssistance->setRemarks($data->getRemarks());
        $newTechnicalAssistance->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($newTechnicalAssistance);
        $this->getEntityManager()->flush();

        return $newTechnicalAssistance->getId();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function delete(int $id): bool
    {
        $technicalAssistance = $this->isExistingById($id);
        if (! $technicalAssistance) {
            return false;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->getEntityManager()->remove($technicalAssistance);
        $this->getEntityManager()->flush();

        return true;
    }

    public function isExistingById(int $id): bool | TechnicalAssistance
    {
        $technicalAssistance = $this->findOneBy([
            'id' => $id,
            'deletedAt' => null
        ]);

        return ($technicalAssistance == null) ? false : $technicalAssistance;
    }

    /**
     * @param string[] $minMaxDate
     * @param int $fieldOfficeId
     * @return array<int, array<string, mixed>>
     * @throws Exception
     */
    public function findByDateRange(array $minMaxDate, int $fieldOfficeId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $min = $minMaxDate['min'];
        $max = $minMaxDate['max'];

        $sql = "SELECT ta.*, fo.name as field_office FROM technical_assistance as ta
                LEFT JOIN field_offices as fo ON ta.field_office_id = fo.field_office_id
                WHERE ta.field_office_id = $fieldOfficeId
                AND ta.date BETWEEN CAST('$min' AS DATE)
                AND CAST('$max' AS DATE)";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();
        $rows = $query->fetchAllAssociative();
        $result = [];

        foreach ($rows as $row) {
            if ($row['personnel_id'] != null) {
                $userDetail = $this->userDetailsRepository->findOneBy(['userAccountId' => $row['personnel_id']]);
                $name = $userDetail->getFirstName() . ' ' . $userDetail->getMiddleName() . ' ' . $userDetail->getLastName();
                $row['role'] = $row['personnel_role'];
            } else {
                $vpa = $this->volunteerRepository->find(intval($row['vpa_id']));
                $name = $vpa->getFirstName() . ' ' . $vpa->getMiddleName() . ' ' . $vpa->getLastName();
                $row['role'] = $row['vpa_role'];
            }
            $row['name'] = $name;
            $result[] = $row;
        }

        return $result;
    }
}
