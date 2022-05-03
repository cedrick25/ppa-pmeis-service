<?php

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\SocialMarketing;
use App\Model\SocialMarketing as SocialMarketingModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method SocialMarketing|null find($id, $lockMode = null, $lockVersion = null)
 * @method SocialMarketing|null findOneBy(array $criteria, array $orderBy = null)
 * @method SocialMarketing[]    findAll()
 * @method SocialMarketing[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SocialMarketingRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "social_marketing";

    public function __construct(
        ManagerRegistry                $registry,
        private TagAwareCacheInterface $cache,
        private CacheHelper            $cacheHelper,
        private Helper                 $helper,
        private AppDateHelper          $appDateHelper,
        private VolunteerRepository    $volunteerRepository,
        private UserDetailsRepository  $userDetailsRepository,
    ) {
        parent::__construct($registry, SocialMarketing::class);
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function list(): array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllSocialMarketingKey(),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponse($params, function() {
            return $this->createQueryBuilder('sm')
                ->where('sm.deletedAt IS NULL')
                ->orderBy('sm.socialMarketingId', 'DESC')
                ->getQuery()
                ->getResult();
        });
    }

    /**
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function create(SocialMarketingModel $data): int|null
    {
        $this->cache->invalidateTags([self::CACHE_TAG]);

        $newSocialMarketing = new SocialMarketing();
        $newSocialMarketing->setSocialMarketingActivityId($data->getSocialMarketingActivityId());
        $newSocialMarketing->setActivityName($data->getActivityName());
        $newSocialMarketing->setDate($this->appDateHelper->convertStringToImmutableDate($data->getDate()));
        $newSocialMarketing->setVenue($data->getVenue());
        $newSocialMarketing->setParticipants($data->getParticipants());
        $newSocialMarketing->setParticipantType($data->getParticipantType());
        $newSocialMarketing->setType($data->getType());
        $newSocialMarketing->setPersonnelId($data->getPersonnelId());
        $newSocialMarketing->setPersonnelRole($data->getPersonnelRole());
        $newSocialMarketing->setVpaId($data->getVpaId());
        $newSocialMarketing->setVpaRole($data->getVpaRole());
        $newSocialMarketing->setRemarks($data->getRemarks());
        $newSocialMarketing->setFieldOfficeId($data->getFieldOfficeId());
        $newSocialMarketing->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($newSocialMarketing);
        $this->getEntityManager()->flush();

        return $newSocialMarketing->getSocialMarketingId();
    }

    /**
     * @throws InvalidArgumentException
     * @throws ORMException
     */
    public function delete(int $id): bool
    {
        $socialMarketing = $this->isExistingById($id);
        if (! $socialMarketing) {
            return false;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $this->getEntityManager()->remove($socialMarketing);
        $this->getEntityManager()->flush();

        return true;
    }

    public function isExistingById(int $id): bool | SocialMarketing
    {
        $socialMarketing = $this->findOneBy([
            'socialMarketingId' => $id,
            'deletedAt' => null
        ]);

        return ($socialMarketing == null) ? false : $socialMarketing;
    }

    /**
     * @param string[] $minMaxDate
     * @param int $fieldOfficeId
     * @param string $type
     * @return array<int|string, array<int, array<string, mixed>>>
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function findByDateRange(array $minMaxDate, int $fieldOfficeId, string $type): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $min = $minMaxDate['min'];
        $max = $minMaxDate['max'];

        $sql = "SELECT sm.*, fo.name as field_office, sma.name as social_marketing_activity FROM social_marketing as sm
                LEFT JOIN field_offices as fo ON sm.field_office_id = fo.field_office_id
                LEFT JOIN social_marketing_activities as sma ON sm.social_marketing_activity_id = sma.id
                WHERE sm.field_office_id = $fieldOfficeId AND sm.type = '$type' 
                  AND sm.date BETWEEN CAST('$min' AS DATE) AND CAST('$max' AS DATE)";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();
        $rows = $query->fetchAllAssociative();
        $result = [];

        foreach ($rows as $row) {
            $vpa = $this->volunteerRepository->find(intval($row['vpa_id']));
            $userDetail = $this->userDetailsRepository->findOneBy(['userAccountId' => $row['personnel_id']]);
            if ($userDetail !== null) {
                $row['personnel_name'] = $userDetail->getFirstName() . ' ' . $userDetail->getMiddleName() . ' ' . $userDetail->getLastName();
            } else {
                $row['personnel_name'] = '';
            }
            if ($vpa !== null) {
                $row['vpa_name'] = $vpa->getFirstName() . ' ' . $vpa->getMiddleName() . ' ' . $vpa->getLastName();
            } else {
                $row['vpa_name'] = '';
            }

            $result[$row['social_marketing_activity_id']][] = $row;
        }

        return $result;
    }

    /**
     * @param string[] $minMaxDate
     * @param int $fieldOfficeId
     * @param string $type
     * @return array<int|string, array<int, array<string, mixed>>>
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getVolunteerIdsByDateRange(array $minMaxDate, int $fieldOfficeId, string $type): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $min = $minMaxDate['min'];
        $max = $minMaxDate['max'];

        $sql = "SELECT DISTINCT v.volunteer_id 
                FROM social_marketing as sm
                LEFT JOIN volunteer as v ON v.volunteer_id = sm.vpa_id
                WHERE sm.field_office_id = $fieldOfficeId AND sm.type = '$type' 
                  AND sm.date BETWEEN CAST('$min' AS DATE) AND CAST('$max' AS DATE)";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();
        $result = $query->fetchAllAssociative();

        return $result;
    }
}
