<?php

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\CapabilityBuilding;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method CapabilityBuilding|null find($id, $lockMode = null, $lockVersion = null)
 * @method CapabilityBuilding|null findOneBy(array $criteria, array $orderBy = null)
 * @method CapabilityBuilding[]    findAll()
 * @method CapabilityBuilding[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CapabilityBuildingRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "capability_building";

    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private Helper $helper,
        private AppDateHelper $appDateHelper,
    ) {
        parent::__construct($registry, CapabilityBuilding::class);
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllCapabilityBuildingsKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function() {
            return $this->createQueryBuilder('cb')
                ->where('cb.deletedAt IS NULL')
                ->orderBy('cb.capabilityBuildingId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws InvalidArgumentException
     * @throws ORMException
     * @throws \Doctrine\Persistence\Mapping\MappingException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws \Exception
     */
    public function batchCreate(array $data): void
    {
        $participantCount = count($data['participants']);

        foreach ($data['participants'] as $participant) {
            $newCapabilityBuilding = new CapabilityBuilding();
            $newCapabilityBuilding->setType($data['type']);
            $newCapabilityBuilding->setSubtype($data['subtype']);
            $newCapabilityBuilding->setTitle($data['title']);
            $newCapabilityBuilding->setStartDate($this->appDateHelper->convertStringToImmutableDate($data['startDate']));
            $newCapabilityBuilding->setEndDate($this->appDateHelper->convertStringToImmutableDate($data['endDate']));
            $newCapabilityBuilding->setNoOfParticipants($participantCount);
            $newCapabilityBuilding->setNames($participant['id']['label']);
            // TODO: Fetch from real table base type
            $newCapabilityBuilding->setIsPwd(false);
            $newCapabilityBuilding->setIsSeniorCitizen(false);
            $newCapabilityBuilding->setNotManagerialSupervisory($data['notManagerialSupervisory'] ?? '');
            $newCapabilityBuilding->setNotTechnical($data['notTechnical'] ?? '');
            $newCapabilityBuilding->setNotFoundation($data['notFoundation'] ?? '');
            $newCapabilityBuilding->setNoOfTrainingHours($data['noOfTrainingHours']);
            $newCapabilityBuilding->setTcInHouse($data['tcInHouse'] ?? '');
            $newCapabilityBuilding->setTcOutHouse($data['tcOutHouse'] ?? '');
            $newCapabilityBuilding->setRemarks($participant['remarks']);
            $newCapabilityBuilding->setFieldOfficeId($data['fieldOfficeId']);
            $newCapabilityBuilding->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

            $this->getEntityManager()->persist($newCapabilityBuilding);
        }

        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear(CapabilityBuilding::class);
        $this->cache->invalidateTags([self::CACHE_TAG]);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function findReport(array $minMaxDate, int $fieldOfficeId, string $type): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $min = $minMaxDate['min'];
        $max = $minMaxDate['max'];

        $sql = "SELECT cb.* FROM capability_building as cb
                WHERE cb.field_office_id = $fieldOfficeId
                  AND cb.type = '$type'
                  AND cb.start_date >= CAST('$min' AS DATE) AND cb.end_date <= CAST('$max' AS DATE)";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();

        return $query->fetchAllAssociative();
    }
}
