<?php

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\JailDecongestion;
use App\Entity\ResourceMobilization;
use App\Model\JailDecongestion as JailDecongestionModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method JailDecongestion|null find($id, $lockMode = null, $lockVersion = null)
 * @method JailDecongestion|null findOneBy(array $criteria, array $orderBy = null)
 * @method JailDecongestion[]    findAll()
 * @method JailDecongestion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JailDecongestionRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "jail_decongestions";

    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper $cacheHelper,
        private Helper $helper,
        private AppDateHelper $appDateHelper,
    ) {
        parent::__construct($registry, JailDecongestion::class);
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllJailDecongestionKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function() {
            return $this->createQueryBuilder('jd')
                ->where('jd.deletedAt IS NULL')
                ->orderBy('jd.jailDecongestionId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     * @throws \Exception
     */
    public function create(JailDecongestionModel $data): int | null
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $newJailDecongestion = new JailDecongestion();
        $newJailDecongestion->setDate($this->appDateHelper->convertStringToImmutableDate($data->getDate()));
        $newJailDecongestion->setNameAddress($data->getNameAddress());
        $newJailDecongestion->setJailVenue($data->isJailVenue());
        $newJailDecongestion->setJailOffice($data->isJailOffice());
        $newJailDecongestion->setProbation($data->getProbation());
        $newJailDecongestion->setClemency($data->getClemency());
        $newJailDecongestion->setReferralPao($data->getReferralPao());
        $newJailDecongestion->setReferralProsecution($data->getReferralProsecution());
        $newJailDecongestion->setReferralOthers($data->getReferralOthers());
        $newJailDecongestion->setGcta($data->getGcta());
        $newJailDecongestion->setRecognizance($data->getRecognizance());
        $newJailDecongestion->setPersonResponsible($data->getPersonResponsible());
        $newJailDecongestion->setRemarks($data->getRemarks());
        $newJailDecongestion->setFieldOfficeId($data->getFieldOfficeId());
        $newJailDecongestion->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($newJailDecongestion);
        $this->getEntityManager()->flush();

        return $newJailDecongestion->getJailDecongestionId();
    }

    /**
     * @throws InvalidArgumentException
     * @throws ORMException
     */
    public function delete(int $id): bool
    {
        $jailDecongestion = $this->isExistingById($id);
        if (! $jailDecongestion) {
            return false;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->getEntityManager()->remove($jailDecongestion);
        $this->getEntityManager()->flush();

        return true;
    }

    public function isExistingById(int $id): bool | JailDecongestion
    {
        $jailDecongestion = $this->findOneBy([
            'jailDecongestionId' => $id,
            'deletedAt' => null
        ]);

        return ($jailDecongestion == null) ? false : $jailDecongestion;
    }

    /**
     * @param string[] $minMaxDate
     * @param int $fieldOfficeId
     * @return array<int, array<string, mixed>>
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function findByDateRange(array $minMaxDate, int $fieldOfficeId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $min = $minMaxDate['min'];
        $max = $minMaxDate['max'];

        $sql = "SELECT jd.* FROM jail_decongestion as jd
                WHERE jd.field_office_id = $fieldOfficeId AND jd.date BETWEEN CAST('$min' AS DATE) AND CAST('$max' AS DATE)
                ORDER BY jd.date DESC";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();

        return $query->fetchAllAssociative();
    }
}
