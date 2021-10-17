<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\UserType;
use App\Model\FieldOffice;
use App\Model\Region;
use App\Model\UserAccount;

class UserService implements UserServiceInterface
{
    public function getUserAccount(): UserAccount
    {
        $region = new Region(1, "IV-A");
        $fieldOffice = new FieldOffice(1, "CSC Field Office");
        $userIsActive = 1;

        return new UserAccount(
            1,
            "test.user@test.com",
            "09884522345",
            "HashHash",
            UserType::FO(),
            $userIsActive,
            $region,
            $fieldOffice
        );
    }
}