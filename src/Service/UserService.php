<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\UserType;
use App\Model\FieldOffice;
use App\Model\Region;
use App\Model\UserAccount;
use App\Repository\UserAccountRepository;
use Doctrine\ORM\NonUniqueResultException;

class UserService implements UserServiceInterface
{
    public function __construct(
        private UserAccountRepository $userAccountRepository
    ){}

    /**
     * @throws NonUniqueResultException
     */
    public function getUserAccount(): UserAccount
    {
        // TODO: use relations in all entities for joining
        $userAccount = $this->userAccountRepository->findAccountByID(1);

        // TODO: Create data migration for all regions and field offices
        // Should fetch region and field office or use the join entity to avoid these
        $region = new Region($userAccount->getRegionId(), "IV-A");
        $fieldOffice = new FieldOffice($userAccount->getFieldOfficeId(), "CSC Field Office");

        // TODO: Use hydrator or create user account mapping
        return new UserAccount(
            $userAccount->getUserAccountId(),
            $userAccount->getEmailAddress(),
            $userAccount->getContactNumber(),
            UserType::from($userAccount->getUserType())->getValue(),
            $userAccount->getStatus(),
            $region->getName(),
            $fieldOffice->getName()
        );
    }
}