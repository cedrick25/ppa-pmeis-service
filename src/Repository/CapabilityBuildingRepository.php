<?php

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\CapabilityBuilding;
use App\Model\CapabilityBuilding as CapabilityBuildingModel;
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
     * @throws InvalidArgumentException
     * @throws ORMException
     * @throws \Exception
     */
    public function create(CapabilityBuildingModel $data): void
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $newCapabilityBuilding = new CapabilityBuilding();
        $newCapabilityBuilding->setType($data->getType());
        $newCapabilityBuilding->setSubtype($data->getSubtype());
        $newCapabilityBuilding->setTitle($data->getTitle());
        $newCapabilityBuilding->setDate($this->appDateHelper->convertStringToImmutableDate($data->getDate()));
        $newCapabilityBuilding->setNoOfParticipants($data->getNoOfParticipants());
        $newCapabilityBuilding->setNames($data->getNames());
        $newCapabilityBuilding->setIsPwd($data->isPwd());
        $newCapabilityBuilding->setIsSeniorCitizen($data->isSeniorCitizen());
        $newCapabilityBuilding->setNotManagerialSupervisory($data->getNotManagerialSupervisory());
        $newCapabilityBuilding->setNotTechnical($data->getNotTechnical());
        $newCapabilityBuilding->setNotFoundation($data->getNotFoundation());
        $newCapabilityBuilding->setNoOfTrainingHours($data->getNoOfTrainingHours());
        $newCapabilityBuilding->setTcInHouse($data->getTcInHouse());
        $newCapabilityBuilding->setTcOutHouse($data->getTcOutHouse());
        $newCapabilityBuilding->setRemarks($data->getRemarks());
        $newCapabilityBuilding->setFieldOfficeId($data->getFieldOfficeId());
        $newCapabilityBuilding->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($newCapabilityBuilding);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();
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
                  AND cb.date BETWEEN CAST('$min' AS DATE) AND CAST('$max' AS DATE)";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();

        return $query->fetchAllAssociative();
    }
}
