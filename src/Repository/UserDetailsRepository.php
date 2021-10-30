<?php

declare(strict_types=1);

namespace App\Repository;

use App\Common\AppDateHelper;
use App\Entity\UserDetails;
use App\Model\UserAccountWithDetails;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @method UserDetails|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserDetails|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserDetails[]    findAll()
 * @method UserDetails[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserDetailsRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private AppDateHelper $appDateHelper,
    ){
        parent::__construct($registry, UserDetails::class);
    }

    /**
     * @throws Exception
     */
    public function create(int $userAccountId, UserAccountWithDetails $userAccountWithDetails): void
    {
        $userDetails = new UserDetails();
        $userDetails->setUserAccountId($userAccountId);
        $userDetails->setFirstName($userAccountWithDetails->getFirstName());
        $userDetails->setMiddleName($userAccountWithDetails->getMiddleName());
        $userDetails->setLastName($userAccountWithDetails->getLastName());
        $userDetails->setSuffix($userAccountWithDetails->getSuffix());
        $userDetails->setGender($userAccountWithDetails->getGender());
        $userDetails->setDateOfBirth($this->appDateHelper->convertStringToImmutableDate($userAccountWithDetails->getDateOfBirth()));
        $userDetails->setIsSeniorCitizen($userAccountWithDetails->isSeniorCitizen());
        $userDetails->setIsPwd($userAccountWithDetails->isPwd());
        $userDetails->setPositionId($userAccountWithDetails->getPositionId());

        $this->getEntityManager()->persist($userDetails);
        $this->getEntityManager()->flush();
    }
}
