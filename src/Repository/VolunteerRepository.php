<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\Volunteer;
use App\Enum\Response as ResponseEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use App\Model\Volunteer as VolunteerModel;
use Exception;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @method Volunteer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Volunteer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Volunteer[]    findAll()
 * @method Volunteer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VolunteerRepository extends ServiceEntityRepository
{
    protected const CACHE_TAG = "volunteers";

    public function __construct(
        ManagerRegistry                              $registry,
        private TagAwareCacheInterface               $cache,
        private CacheHelper                          $cacheHelper,
        private AppDateHelper                        $appDateHelper,
        private Helper                               $helper,
        private ResourceFacilitatorSessionRepository $resourceFacilitatorSessionRepository,
        private VolunteerOperationsRepository        $volunteerOperationsRepository,
    ){
        parent::__construct($registry, Volunteer::class);
    }

    /**
     * @throws InvalidArgumentException
     * @throws ORMException
     * @throws Exception
     */
    public function create(VolunteerModel $volunteerData): int | null
    {
        if ($this->isExisting($volunteerData)) {
            return null;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $newVolunteer = new Volunteer();
        $newVolunteer->setFirstName($volunteerData->getFirstName());
        $newVolunteer->setMiddleName($volunteerData->getMiddleName());
        $newVolunteer->setLastName($volunteerData->getLastName());
        $newVolunteer->setSuffix($volunteerData->getSuffix());
        $newVolunteer->setGender($volunteerData->getGender());
        $newVolunteer->setDateOfBirth($volunteerData->getDateOfBirth());
        $newVolunteer->setIsSeniorCitizen($volunteerData->getIsSeniorCitizen());
        $newVolunteer->setIsPwd($volunteerData->getIsPwd());
        $newVolunteer->setFieldOfficeId($volunteerData->getFieldOfficeId());
        $newVolunteer->setDateRecruited($this->appDateHelper->convertStringToImmutableDate($volunteerData->getDateRecruited()));
        $newVolunteer->setRecruitingOfficer($volunteerData->getRecruitingOfficer());
        $newVolunteer->setAge($volunteerData->getAge());
        $newVolunteer->setBirthPlace($volunteerData->getBirthPlace());
        $newVolunteer->setCivilStatus($volunteerData->getCivilStatus());
        $newVolunteer->setReligion($volunteerData->getReligion());
        $newVolunteer->setPresentAddress($volunteerData->getPresentAddress());
        $newVolunteer->setHeight($volunteerData->getHeight());
        $newVolunteer->setWeight($volunteerData->getWeight());
        $newVolunteer->setBloodType($volunteerData->getBloodType());
        $newVolunteer->setOccupation($volunteerData->getOccupation());
        $newVolunteer->setEducationAttainment($volunteerData->getEducationAttainment());
        $newVolunteer->setContactNumber($volunteerData->getContactNumber());
        $newVolunteer->setEmailAddress($volunteerData->getEmailAddress());
        $newVolunteer->setDomesticPartner($volunteerData->getDomesticPartner());
        $newVolunteer->setSpecialSkill($volunteerData->getSpecialSkill());
        $newVolunteer->setEmergencyName($volunteerData->getEmergencyName());
        $newVolunteer->setEmergencyNumber($volunteerData->getEmergencyNumber());
        $newVolunteer->setImage($volunteerData->getImage());
        $newVolunteer->setApplicantSignature($volunteerData->getApplicantSignature());
        $newVolunteer->setDateAccomplished($this->appDateHelper->convertStringToImmutableDate($volunteerData->getDateAccomplished()));
        $newVolunteer->setOfficerSignature($volunteerData->getOfficerSignature());
        $newVolunteer->setDateSigned($this->appDateHelper->convertStringToImmutableDate($volunteerData->getDateSigned()));
        $newVolunteer->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($newVolunteer);
        $this->getEntityManager()->flush();

        return $newVolunteer->getVolunteerId();
    }

    /**
     * @return array<string, mixed>|null
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function list(): ?array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getAllVolunteersKey(),
            'cacheTag' => self::CACHE_TAG
        ];

        return $this->helper->createCachedResponseCustomQuery($params, function() {
            $conn = $this->getEntityManager()->getConnection();

            $sql = "SELECT v.*, fo.name as field_office_name, rg.region_id, rg.name as region_name 
                 FROM volunteer as v " .
                "LEFT JOIN field_offices as fo ON v.field_office_id = fo.field_office_id " .
                "LEFT JOIN regions as rg ON fo.region_id = rg.region_id " .
                "WHERE v.deleted_at IS NULL ORDER BY v.volunteer_id DESC";
            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();

            return $query->fetchAllAssociative();
        });
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws InvalidArgumentException
     */
    public function softDelete(int $id): bool
    {
        $client =$this->isExistingById($id);

        if ($client == null) {
            return false;
        }

        // TODO: Check table constraints

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $client->setDeletedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function update(int $id, VolunteerModel $volunteerData): string
    {
        $volunteer =$this->isExistingById($id);

        if ($volunteer == null) {
            return ResponseEnum::NO_RECORD;
        }

        if ($this->isConflicted($volunteer, $volunteerData)) {
            return ResponseEnum::CONFLICTED_INPUT;
        }

        $this->cache->invalidateTags([self::CACHE_TAG]);

        $volunteer->setFirstName($volunteerData->getFirstName());
        $volunteer->setMiddleName($volunteerData->getMiddleName());
        $volunteer->setLastName($volunteerData->getLastName());
        $volunteer->setSuffix($volunteerData->getSuffix());
        $volunteer->setGender($volunteerData->getGender());
        $volunteer->setDateOfBirth($volunteerData->getDateOfBirth());
        $volunteer->setIsSeniorCitizen($volunteerData->getIsSeniorCitizen());
        $volunteer->setIsPwd($volunteerData->getIsPwd());
        $volunteer->setFieldOfficeId($volunteerData->getFieldOfficeId());
        $volunteer->setDateRecruited($this->appDateHelper->convertStringToImmutableDate($volunteerData->getDateRecruited()));
        $volunteer->setRecruitingOfficer($volunteerData->getRecruitingOfficer());
        $volunteer->setAge($volunteerData->getAge());
        $volunteer->setBirthPlace($volunteerData->getBirthPlace());
        $volunteer->setCivilStatus($volunteerData->getCivilStatus());
        $volunteer->setReligion($volunteerData->getReligion());
        $volunteer->setPresentAddress($volunteerData->getPresentAddress());
        $volunteer->setHeight($volunteerData->getHeight());
        $volunteer->setWeight($volunteerData->getWeight());
        $volunteer->setBloodType($volunteerData->getBloodType());
        $volunteer->setOccupation($volunteerData->getOccupation());
        $volunteer->setEducationAttainment($volunteerData->getEducationAttainment());
        $volunteer->setContactNumber($volunteerData->getContactNumber());
        $volunteer->setEmailAddress($volunteerData->getEmailAddress());
        $volunteer->setDomesticPartner($volunteerData->getDomesticPartner());
        $volunteer->setSpecialSkill($volunteerData->getSpecialSkill());
        $volunteer->setEmergencyName($volunteerData->getEmergencyName());
        $volunteer->setEmergencyNumber($volunteerData->getEmergencyNumber());
        $volunteer->setImage($volunteerData->getImage());
        $volunteer->setApplicantSignature($volunteerData->getApplicantSignature());
        $volunteer->setDateAccomplished($this->appDateHelper->convertStringToImmutableDate($volunteerData->getDateAccomplished()));
        $volunteer->setOfficerSignature($volunteerData->getOfficerSignature());
        $volunteer->setDateSigned($this->appDateHelper->convertStringToImmutableDate($volunteerData->getDateSigned()));
        $volunteer->setUpdatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->flush();

        return ResponseEnum::OK;
    }

    /**
     * @param int $id
     * @return bool|array<string, mixed>
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getById(int $id): bool|array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT v.*, fo.name as field_office_name, rg.region_id, rg.name as region_name 
                 FROM volunteer as v " .
            "LEFT JOIN field_offices as fo ON v.field_office_id = fo.field_office_id " .
            "LEFT JOIN regions as rg ON fo.region_id = rg.region_id " .
            "WHERE v.volunteer_id = $id AND v.deleted_at IS NULL ORDER BY v.volunteer_id DESC";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();

        return $query->fetchAssociative();
    }

    public function isExistingById(int $id): bool | Volunteer
    {
        $client = $this->findOneBy([
            'volunteerId' => $id,
            'deletedAt' => null
        ]);

        return ($client == null) ? false : $client;
    }

    /**
     * @param int $page
     * @param int $pageSize
     * @return array<string, mixed>|null
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function paginated(int $page = 1, int $pageSize = 10): ?array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getVolunteersPaginatedKey($page, $pageSize),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG,
            'pageSize' => $pageSize,
            'page' => $page
        ];

        return $this->helper->createPaginatedResponseCustomQuery($params, function() use ($pageSize, $page) {
            $conn = $this->getEntityManager()->getConnection();
            $startOffset = $pageSize * ($page-1);
            $result = [];

            $sql = "SELECT v.*, fo.name as field_office_name, rg.region_id, rg.name as region_name 
                 FROM volunteer as v " .
                "LEFT JOIN field_offices as fo ON v.field_office_id = fo.field_office_id " .
                "LEFT JOIN regions as rg ON fo.region_id = rg.region_id " .
                "WHERE v.deleted_at IS NULL ORDER BY v.volunteer_id DESC " .
                "LIMIT $pageSize OFFSET $startOffset";
            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();
            $result['data'] = $query->fetchAllAssociative();
            $result['totalItems'] = count($this->findBy([
                'deletedAt' => null
            ]));

            return $result;
        });
    }

    /**
     * @param int $fieldOfficeId
     * @param int $year
     * @param int[] $months
     * @return bool|array<string, mixed>
     */
    public function findByFieldOfficeAndMonthRange(int $fieldOfficeId, int $year, array $months): bool|array
    {
        return $this->createQueryBuilder('v')
            ->where('v.fieldOfficeId = :fieldOfficeId')
            ->andWhere('YEAR(v.dateRecruited) = :year')
            ->andWhere('MONTH(v.dateRecruited) IN (:months)')
            ->setParameter('fieldOfficeId', $fieldOfficeId)
            ->setParameter('year', $year)
            ->setParameter('months', $months, Connection::PARAM_INT_ARRAY)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $fieldOfficeId
     * @param int $quarterId
     * @param int $year
     * @param array $months
     * @return Volunteer[]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function findInactiveVolunteersByFieldOfficeAndMonthRange(
        int $fieldOfficeId,
        int $quarterId,
        int $year,
        array $months
    ): array {
        $activeVolunteers = $this->resourceFacilitatorSessionRepository->getVolunteerIdsByQuarterAndFieldOfficeId($fieldOfficeId, $quarterId);
        $droppedVolunteerIds = $this->volunteerOperationsRepository->findVolunteerIdsByMonthRange($year, $months, 'DROPPED');
        $inActiveVolunteerIds = [];
        $activeVolunteerIds = [];

        foreach ($activeVolunteers as $activeVolunteer) {
            $activeVolunteerIds[] = $activeVolunteer['resource_facilitator_id'];
        };

        foreach ($droppedVolunteerIds as $droppedVolunteerId) {
            if (! in_array(intval($droppedVolunteerId['volunteer_id']), $activeVolunteerIds)) {
                $inActiveVolunteerIds[] = intval($droppedVolunteerId['volunteer_id']);
            }
        }

        return $this->createQueryBuilder('v')
            ->where('v.volunteerId IN (:inActiveVolunteerIds)')
            ->setParameter('inActiveVolunteerIds', $inActiveVolunteerIds, Connection::PARAM_INT_ARRAY)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int[] $ids
     * @return Volunteer[]
     */
    public function findByIds(array $ids): array
    {
        return $this->createQueryBuilder('v')
            ->select('v.firstName, v.middleName, v.lastName, v.gender')
            ->where('v.volunteerId IN (:ids)')
            ->setParameter('ids', $ids, Connection::PARAM_INT_ARRAY)
            ->getQuery()
            ->getResult();
    }

    public function getVpaStartOfQuarter()
    {
        //
    }

    private function isExisting(VolunteerModel $volunteerData): bool
    {
        $volunteer = $this->findOneBy([
            'firstName' => $volunteerData->getFirstName(),
            'middleName' => $volunteerData->getMiddleName(),
            'lastName' => $volunteerData->getLastName(),
            'deletedAt' => null
        ]);

        return $volunteer != null;
    }

    private function isConflicted(Volunteer $fetchedVolunteer, VolunteerModel $volunteerData): bool
    {
        // Fetched and input client is the same.
        // It is trying to update itself.
        if (
            $fetchedVolunteer->getFirstName() === $volunteerData->getFirstName() &&
            $fetchedVolunteer->getMiddleName() === $volunteerData->getMiddleName() &&
            $fetchedVolunteer->getLastName() === $volunteerData->getLastName()
        ) {
            return false;
        }

        // There is an existing record in the database.
        // It is trying to update another record that existing in the database.
        if ($this->isExisting($volunteerData)) {
            return true;
        }

        return false;
    }
}
