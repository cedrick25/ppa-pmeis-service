<?php

declare(strict_types=1);

namespace App\Service;

use App\Common\AppErrorFormatter;
use App\Model\UserAccount as UserAccountModel;
use App\Repository\UserAccountRepository;
use Doctrine\ORM\ORMException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserService implements UserServiceInterface
{
    public function __construct(
        private UserAccountRepository $userAccountRepository,
        private ValidatorInterface $validator,
        private AppErrorFormatter $appErrorFormatter,
    ){}

    /**
     * @return array<string, mixed>
     * @throws InvalidArgumentException
     */
    public function getUserByID(int $id): array
    {
        $userAccount = $this->userAccountRepository->findAccountWithDetailsByID($id);
        unset($userAccount["password"]);
        $userAccount["user_account_id"] = (int) $userAccount["user_account_id"];
        $userAccount["user_detail_id"] = (int) $userAccount["user_detail_id"];
        $userAccount["field_office_id"] = (int) $userAccount["status"];
        $userAccount["region_id"] = (int) $userAccount["region_id"];
        $userAccount["status"] = (int) $userAccount["status"];
        $userAccount["is_pwd"] = (bool) $userAccount["is_pwd"];
        $userAccount["is_senior_citizen"] = (bool) $userAccount["is_senior_citizen"];

        return $userAccount;
    }

    /**
     * @return array<string, mixed>
     * @throws ORMException
     */
    public function register(UserAccountModel $userAccount): array
    {
        $errors = $this->validator->validate($userAccount);

        if (count($errors) > 0) {
            return [
              'message' => 'User validation failed',
              'errors' => $this->appErrorFormatter->format($errors)
            ];
        }

        return [
            'message' => 'User creation successful',
            'id' => $this->userAccountRepository->createUser($userAccount)
        ];
    }
}