<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\Volunteer;
use App\Enum\Common;
use App\Enum\Response as ResponseEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use App\Model\Volunteer as VolunteerModel;
use Exception;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use DateTime;

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
        $newVolunteer->setIsTcTrained($volunteerData->getIsTcTrained());
        $newVolunteer->setDateRecruited(
            $this->appDateHelper->convertStringToImmutableDate($volunteerData->getDateRecruited())
        );
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
        $newVolunteer->setDateAccomplished(
            $this->appDateHelper->convertStringToImmutableDate($volunteerData->getDateAccomplished())
        );
        $newVolunteer->setDateSigned(
            $this->appDateHelper->convertStringToImmutableDate($volunteerData->getDateSigned())
        );
        $newVolunteer->setDateAppointed(
            $this->appDateHelper->convertStringToImmutableDate($volunteerData->getDateAppointed())
        );
        $newVolunteer->setVpaStatus($volunteerData->getVpaStatus());
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

        return $this->helper->createCachedResponseCustomQuery($params, function () {
            $conn = $this->getEntityManager()->getConnection();

            $sql = "SELECT v.*, fo.name as field_office_name, rg.region_id, rg.name as region_name,
                    v.civil_status as civil_status_id, v.religion as religion_id, v.occupation as occupation_id,
                    v.education_attainment as education_attainment_id, cvs.name as civil_status, r.name as religion,
                    o.name as occupation, eb.name as education_attainment, v.is_tc_trained
                 FROM volunteer as v
                 LEFT JOIN field_offices as fo ON v.field_office_id = fo.field_office_id
                 LEFT JOIN regions as rg ON fo.region_id = rg.region_id
                 LEFT JOIN civil_status as cvs ON v.civil_status = cvs.civil_status_id
                 LEFT JOIN religion as r ON v.religion = r.religion_id
                 LEFT JOIN occupation as o ON v.occupation = o.occupation_id
                 LEFT JOIN education_background as eb ON v.education_attainment = eb.education_background_id
                 WHERE v.deleted_at IS NULL AND v.date_appointed IS NOT NULL ORDER BY v.volunteer_id DESC";
            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();

            return $query->fetchAllAssociative();
        });
    }

    /**
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
        $volunteer->setIsTcTrained($volunteerData->getIsTcTrained());
        $volunteer->setDateRecruited(
            $this->appDateHelper->convertStringToImmutableDate($volunteerData->getDateRecruited())
        );
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
        $volunteer->setDateAccomplished(
            $this->appDateHelper->convertStringToImmutableDate($volunteerData->getDateAccomplished())
        );
        $volunteer->setDateSigned($this->appDateHelper->convertStringToImmutableDate($volunteerData->getDateSigned()));
        $volunteer->setDateAppointed(
            $this->appDateHelper->convertStringToImmutableDate($volunteerData->getDateAppointed())
        );
        $volunteer->setVpaStatus($volunteerData->getVpaStatus());
        $volunteer->setUpdatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->flush();

        return ResponseEnum::OK;
    }

    /**
     * @param int $id
     * @return bool|array<string, mixed>
     * @throws \Doctrine\DBAL\Exception
     */
    public function getById(int $id): bool|array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT v.*, fo.name as field_office_name, rg.region_id, rg.name as region_name,
                    v.civil_status as civil_status_id, v.religion as religion_id, v.occupation as occupation_id,
                    v.education_attainment as education_attainment_id, cvs.name as civil_status, v.is_tc_trained,
                    r.name as religion, o.name as occupation, eb.name as education_attainment
                FROM volunteer as v
                LEFT JOIN field_offices as fo ON v.field_office_id = fo.field_office_id
                LEFT JOIN regions as rg ON fo.region_id = rg.region_id
                LEFT JOIN civil_status as cvs ON v.civil_status = cvs.civil_status_id
                LEFT JOIN religion as r ON v.religion = r.religion_id
                LEFT JOIN occupation as o ON v.occupation = o.occupation_id
                LEFT JOIN education_background as eb ON v.education_attainment = eb.education_background_id
                WHERE v.volunteer_id = $id AND v.deleted_at IS NULL ORDER BY v.volunteer_id DESC";
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
     * @return array<string, mixed>|null
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function paginated(string $status, int $page = 1, int $pageSize = 10): ?array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getVolunteersPaginatedKey($page, $pageSize),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG,
            'pageSize' => $pageSize,
            'page' => $page
        ];

        return $this->helper->createPaginatedResponseCustomQuery($params, function () use ($status, $pageSize, $page) {
            $conn = $this->getEntityManager()->getConnection();
            $startOffset = $pageSize * ($page-1);
            $result = [];

            $sql = "SELECT v.*, fo.name as field_office_name, rg.region_id, rg.name as region_name,
                    v.civil_status as civil_status_id, v.religion as religion_id, v.occupation as occupation_id,
                    v.education_attainment as education_attainment_id, v.is_tc_trained, cvs.name as civil_status,
                    r.name as religion, o.name as occupation, eb.name as education_attainment
                FROM volunteer as v
                LEFT JOIN field_offices as fo ON v.field_office_id = fo.field_office_id
                LEFT JOIN regions as rg ON fo.region_id = rg.region_id
                LEFT JOIN civil_status as cvs ON v.civil_status = cvs.civil_status_id
                LEFT JOIN religion as r ON v.religion = r.religion_id
                LEFT JOIN occupation as o ON v.occupation = o.occupation_id
                LEFT JOIN education_background as eb ON v.education_attainment = eb.education_background_id
                WHERE v.deleted_at IS NULL AND v.vpa_status = '$status' ORDER BY v.volunteer_id DESC ";

            $result['totalItems'] = $this->helper->getCustomQueryPaginatedTotalItems($conn, $sql);

            $sql .= "LIMIT $pageSize OFFSET $startOffset";

            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();
            $result['data'] = $query->fetchAllAssociative();

            return $result;
        });
    }

    /**
     * @return array<string, mixed>|null
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function paginatedExpiring(int $page = 1, int $pageSize = 10): ?array
    {
        $params = [
            'cacheKey' => $this->cacheHelper->getVolunteersPaginatedKey($page, $pageSize),
            'expiration' => $this->cacheHelper->getExpirationDateTime(),
            'cacheTag' => self::CACHE_TAG,
            'pageSize' => $pageSize,
            'page' => $page
        ];

        return $this->helper->createPaginatedResponseCustomQuery($params, function () use ($pageSize, $page) {
            $conn = $this->getEntityManager()->getConnection();
            $startOffset = $pageSize * ($page-1);
            $result = [];
            $monthsInterval = Common::VPA_NUMBER_OF_MONTHS_APPOINTMENT - Common::NUMBER_OF_MONTHS_BEFORE_VPA_EXPIRATION;

            $sql = "SELECT v.*, fo.name as field_office_name, rg.region_id, rg.name as region_name,
                    v.civil_status as civil_status_id, v.religion as religion_id, v.occupation as occupation_id,
                    v.education_attainment as education_attainment_id, v.is_tc_trained, cvs.name as civil_status,
                    r.name as religion, o.name as occupation, eb.name as education_attainment
                FROM volunteer as v
                LEFT JOIN field_offices as fo ON v.field_office_id = fo.field_office_id
                LEFT JOIN regions as rg ON fo.region_id = rg.region_id
                LEFT JOIN civil_status as cvs ON v.civil_status = cvs.civil_status_id
                LEFT JOIN religion as r ON v.religion = r.religion_id
                LEFT JOIN occupation as o ON v.occupation = o.occupation_id
                LEFT JOIN education_background as eb ON v.education_attainment = eb.education_background_id
                WHERE v.deleted_at IS NULL AND NOW() >= DATE_ADD(v.date_appointed, INTERVAL $monthsInterval MONTH)
                ORDER BY v.volunteer_id DESC ";

            $result['totalItems'] = $this->helper->getCustomQueryPaginatedTotalItems($conn, $sql);

            $sql .= "LIMIT $pageSize OFFSET $startOffset";

            $stmt = $conn->prepare($sql);
            $query = $stmt->executeQuery();
            $volunteers = $query->fetchAllAssociative();

            $expirationMonths = Common::VPA_NUMBER_OF_MONTHS_APPOINTMENT;

            foreach($volunteers as $volunteer) {
                $expirationDate = new DateTime($volunteer['date_appointed']. " + $expirationMonths months");
                $currentDate = new DateTime('now');

                if ($currentDate > $expirationDate) {
                    continue;
                }

                $volunteer['days_before_expiration'] = $currentDate->diff($expirationDate)->days;
                $result['data'][] = $volunteer;
            }

            return $result;
        });
    }

    /**
     * @param int $fieldOfficeId
     * @param int $year
     * @param int[] $months
     * @return Volunteer[]
     */
    public function findByFieldOfficeAndMonthRange(int $fieldOfficeId, int $year, array $months): array
    {
        // also needed to be appointed status in operations
        /** @var Volunteer[] */
        return $this->createQueryBuilder('v')
            ->where('v.fieldOfficeId = :fieldOfficeId')
            ->andWhere('YEAR(v.dateRecruited) = :year')
            ->andWhere('MONTH(v.dateRecruited) IN (:months)')
            ->andWhere("v.vpaStatus <> 'DROPPED' ")
            ->andWhere('v.dateAppointed IS NOT NULL')
            ->setParameter('fieldOfficeId', $fieldOfficeId)
            ->setParameter('year', $year)
            ->setParameter('months', $months, Connection::PARAM_INT_ARRAY)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $fieldOfficeId
     * @return Volunteer[]
     */
    public function findByFieldOffice(int $fieldOfficeId): array
    {
        $query = $this->getEntityManager()->getConnection()->executeQuery(
            "SELECT volunteer.*, fo.name as field_office FROM volunteer
                LEFT JOIN field_offices as fo ON volunteer.field_office_id = fo.field_office_id
                WHERE fo.field_office_id = :fieldOfficeId AND volunteer.deleted_at IS NULL",
            ['fieldOfficeId' => $fieldOfficeId]
        );

        return $query->fetchAllAssociative();
    }

    /**
     * @param int $fieldOfficeId
     * @return Volunteer[]
     */
    public function findAppointedVpaByFieldOffice(int $fieldOfficeId): array
    {
        /** @var Volunteer[] */
        return $this->createQueryBuilder('v')
            ->where('v.fieldOfficeId = :fieldOfficeId')
            ->andWhere("v.dateAppointed <= DATE_ADD(v.dateAppointed, 24, 'MONTH')")
            ->andWhere("v.vpaStatus = 'APPOINTED'")
            ->setParameter('fieldOfficeId', $fieldOfficeId)
            ->getQuery()
            ->getResult();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getAppointedVpaDuringQuarter(array $minMaxDate, int $fieldOfficeId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $min = $minMaxDate['min'];
        $max = $minMaxDate['max'];

        $sql = "SELECT v.volunteer_id FROM volunteer AS v
                WHERE v.field_office_id = $fieldOfficeId
                  AND v.vpa_status = 'APPOINTED'
                  AND v.date_appointed BETWEEN CAST('$min' AS DATE) AND CAST('$max' AS DATE)";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();

        return $query->fetchAllAssociative();
    }

    /**
     * @param int $fieldOfficeId
     * @param int $year
     * @param array $months
     * @param array $activeVolunteers
     * @return Volunteer[]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function findInactiveVolunteersByFieldOfficeAndMonthRange(
        int $fieldOfficeId,
        int $year,
        array $months,
        array $activeVolunteers
    ): array {
        $appointedVolunteers = $this->volunteerOperationsRepository
            ->findVolunteerIdsByMonthRange($year, $months, 'APPOINTED');
        $reAppointedVolunteers = $this->volunteerOperationsRepository
            ->findVolunteerIdsByMonthRange($year, $months, 'REAPPOINTED');
        $inActiveVolunteerIds = [];
        $activeVolunteerIds = [];

        foreach ($activeVolunteers as $activeVolunteer) {
            $activeVolunteerIds[] = $activeVolunteer['resource_facilitator_id'];
        }

        foreach ($appointedVolunteers as $appointedVolunteer) {
            if (! in_array(intval($appointedVolunteer['volunteer_id']), $activeVolunteerIds)) {
                $inActiveVolunteerIds[] = intval($appointedVolunteer['volunteer_id']);
            }
        }

        foreach ($reAppointedVolunteers as $reAppointedVolunteer) {
            if (! in_array(intval($reAppointedVolunteer['volunteer_id']), $activeVolunteerIds)) {
                $inActiveVolunteerIds[] = intval($reAppointedVolunteer['volunteer_id']);
            }
        }

        return $this->createQueryBuilder('v')
            ->where('v.volunteerId IN (:inActiveVolunteerIds)')
            ->andWhere('v.fieldOfficeId = :fieldOfficeId')
            ->andWhere('v.deletedAt IS NULL')
            ->setParameter('fieldOfficeId', $fieldOfficeId)
            ->setParameter('inActiveVolunteerIds', $inActiveVolunteerIds, Connection::PARAM_INT_ARRAY)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $fieldOfficeId
     * @param int[] $volunteerIds
     * @return Volunteer[]
     */
    public function findInactiveVolunteersByFieldOffice(
        int $fieldOfficeId,
        array $volunteerIds
    ): array {

        return $this->createQueryBuilder('v')
            ->where('v.volunteerId IN (:inActiveVolunteerIds)')
            ->andWhere('v.fieldOfficeId = :fieldOfficeId')
            ->andWhere('v.deletedAt IS NULL')
            ->setParameter('fieldOfficeId', $fieldOfficeId)
            ->setParameter('inActiveVolunteerIds', $volunteerIds, Connection::PARAM_INT_ARRAY)
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
            ->select()
            ->where('v.volunteerId IN (:ids)')
            ->andWhere('v.deletedAt IS NULL')
            ->andWhere("v.vpaStatus = 'APPOINTED' ")
            ->andWhere('v.dateAppointed IS NOT NULL')
            ->setParameter('ids', $ids, Connection::PARAM_INT_ARRAY)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $regionId
     * @return array<int, mixed>
     * @throws \Doctrine\DBAL\Exception
     */
    public function findByRegionId(int $regionId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT volunteer.*, fo.name as field_office FROM volunteer
                LEFT JOIN field_offices as fo ON volunteer.field_office_id = fo.field_office_id
                LEFT JOIN regions as rg ON fo.region_id = rg.region_id
                WHERE rg.region_id = $regionId AND volunteer.deleted_at IS NULL";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();

        return $query->fetchAllAssociative();
    }

    /**
     * @return Volunteer[]
     */
    public function findApplicants(): array
    {
        return $this->createQueryBuilder('v')
            ->where('v.dateAppointed IS NULL')
            ->andWhere("v.vpaStatus = 'APPLICANT'")
            ->andWhere('v.deletedAt IS NULL')
            ->getQuery()
            ->getResult();
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function updateVolunteerStatus(array $data): string
    {
        $volunteer =$this->isExistingById($data['id']);

        if ($volunteer == null) {
            return ResponseEnum::NO_RECORD;
        }

        if (isset($data['dateAppointed'])) {
            $volunteer->setDateAppointed($this->appDateHelper->convertStringToImmutableDate($data['dateAppointed']));
        }

        if ($data['status'] == 'REAPPOINTED') {
            $data['status'] = 'APPOINTED';
        }

        $volunteer->setVpaStatus($data['status']);
        $volunteer->setUpdatedAt($this->appDateHelper->getCurrentImmutableDate());

        if ('APPOINTED' == $data['status']) {
            $volunteer->setDateAppointed($this->appDateHelper->getCurrentImmutableDate());
        }

        $this->getEntityManager()->flush();
        $this->cache->invalidateTags([self::CACHE_TAG]);

        return ResponseEnum::OK;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getDroppedVolunteer(array $minMaxDate, int $fieldOfficeId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $min = $minMaxDate['min'];
        $max = $minMaxDate['max'];

        $sql = "SELECT v.volunteer_id FROM volunteer AS v
                WHERE v.field_office_id = $fieldOfficeId
                  AND v.vpa_status = 'DROPPED'
                  AND v.date_appointed BETWEEN CAST('$min' AS DATE) AND CAST('$max' AS DATE)";
        $stmt = $conn->prepare($sql);
        $query = $stmt->executeQuery();

        return $query->fetchAllAssociative();
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
