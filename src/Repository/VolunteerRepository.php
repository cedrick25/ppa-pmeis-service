<?php

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Common\CacheHelper;
use App\Entity\Volunteer;
use App\Enum\Response as ResponseEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use App\Model\Volunteer as VolunteerModel;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @method Volunteer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Volunteer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Volunteer[]    findAll()
 * @method Volunteer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VolunteerRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private CacheInterface $cache,
        private CacheHelper $cacheHelper,
        private AppDateHelper $appDateHelper,
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

        $this->cache->delete($this->cacheHelper->getAllVolunteersKey());

        $newVolunteer = new Volunteer();
        $newVolunteer->setFirstName($volunteerData->getFirstName());
        $newVolunteer->setMiddleName($volunteerData->getMiddleName());
        $newVolunteer->setLastName($volunteerData->getLastName());
        $newVolunteer->setSuffix($volunteerData->getSuffix());
        $newVolunteer->setGender($volunteerData->getGender());
        $newVolunteer->setDateOfBirth($this->appDateHelper->convertStringToImmutableDate($volunteerData->getDateOfBirth()));
        $newVolunteer->setIsSeniorCitizen($volunteerData->getIsSeniorCitizen());
        $newVolunteer->setIsPwd($volunteerData->getIsPwd());
        $newVolunteer->setFieldOfficeId($volunteerData->getFieldOfficeId());
        $newVolunteer->setCreatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->persist($newVolunteer);
        $this->getEntityManager()->flush();

        return $newVolunteer->getVolunteerId();
    }

    /**
     * @throws InvalidArgumentException
     * @return Volunteer[]
     */
    public function list(): array
    {
        $cacheKey = $this->cacheHelper->getAllVolunteersKey();
        $expiration = $this->cacheHelper->getExpirationDateTime(24);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($cacheKey, $expiration) {
            $item->expiresAt($expiration);

            return $this->createQueryBuilder('v')
                ->andWhere('v.deletedAt IS NULL')
                ->orderBy('v.volunteerId', 'DESC')
                ->getQuery()
                ->getResult();
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

        $this->cache->delete($this->cacheHelper->getAllVolunteersKey());

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

        $this->cache->delete($this->cacheHelper->getAllVolunteersKey());

        $volunteer->setFirstName($volunteerData->getFirstName());
        $volunteer->setMiddleName($volunteerData->getMiddleName());
        $volunteer->setLastName($volunteerData->getLastName());
        $volunteer->setSuffix($volunteerData->getSuffix());
        $volunteer->setGender($volunteerData->getGender());
        $volunteer->setDateOfBirth($this->appDateHelper->convertStringToImmutableDate($volunteerData->getDateOfBirth()));
        $volunteer->setIsSeniorCitizen($volunteerData->getIsSeniorCitizen());
        $volunteer->setIsPwd($volunteerData->getIsPwd());
        $volunteer->setFieldOfficeId($volunteerData->getFieldOfficeId());
        $volunteer->setUpdatedAt($this->appDateHelper->getCurrentImmutableDate());

        $this->getEntityManager()->flush();

        return ResponseEnum::OK;
    }

    private function isExisting(VolunteerModel $volunteerData): bool
    {
        $client = $this->findOneBy([
            'firstName' => $volunteerData->getFirstName(),
            'middleName' => $volunteerData->getMiddleName(),
            'lastName' => $volunteerData->getLastName(),
            'deletedAt' => null
        ]);

        return $client != null;
    }

    public function isExistingById(int $id): bool | Volunteer
    {
        $client = $this->findOneBy([
            'volunteerId' => $id,
            'deletedAt' => null
        ]);

        return ($client == null) ? false : $client;
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
