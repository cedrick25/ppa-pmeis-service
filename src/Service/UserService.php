<?php

declare(strict_types=1);

namespace App\Service;

use App\Common\AppFormatter;
use App\Model\UserAccountWithDetails;
use App\Repository\UserAccountRepository;
use Doctrine\ORM\ORMException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserService implements UserServiceInterface
{
    public const USER_CREATION_SUCCESS = "User creation successful.";
    public const USER_CREATION_FAILED = "User creation failed.";
    public const USER_VALIDATION_FAILED = "User validation failed.";

    public function __construct(
        private UserAccountRepository $userAccountRepository,
        private ValidatorInterface    $validator,
        private AppFormatter          $appFormatter,
    ){}

    /**
     * @return array<string, mixed>
     * @throws InvalidArgumentException
     */
    public function getByID(int $id): array
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
     */
    public function register(UserAccountWithDetails $userAccountWithDetails): array
    {
        try {
            $errors = $this->validator->validate($userAccountWithDetails);

            if (count($errors) > 0) {
                return $this->appFormatter->formatResponse(self::USER_VALIDATION_FAILED, null, $this->appFormatter->formatErrors($errors));
            }
            $userAccountId = $this->userAccountRepository->create($userAccountWithDetails);

            if ($userAccountId == null) {
                return $this->appFormatter->formatResponse(self::USER_CREATION_FAILED, null, ['app' => 'Email address already exist']);
            }

            return $this->appFormatter->formatResponse(self::USER_CREATION_SUCCESS, ['id' => $userAccountId]);
        } catch (ORMException $exception) {
            return $this->appFormatter->formatResponse(self::USER_CREATION_FAILED, null, ['orm' => $exception->getMessage()]);
        } catch (\Exception $e) {
            return $this->appFormatter->formatResponse(self::USER_CREATION_FAILED, null, ['app' => $e->getMessage()]);
        }
    }
}