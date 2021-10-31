<?php

declare(strict_types=1);

namespace App\Service;

use App\Common\AppFormatter;
use App\Enum\Response as ResponseEnum;
use App\Model\UserAccountWithDetails;
use App\Repository\UserAccountRepository;
use Doctrine\ORM\ORMException;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class User implements UserInterface
{
    public const USER_CREATION_SUCCESS = "User creation successful.";
    public const USER_CREATION_FAILED = "User creation failed.";
    public const USER_VALIDATION_FAILED = "User validation failed.";

    public function __construct(
        private UserAccountRepository $repository,
        private ValidatorInterface    $validator,
        private AppFormatter          $appFormatter,
    ){}

    public function getAll(): array
    {
        try {
            $userAccounts = $this->repository->findWithDetails();
            $accounts = [];

            if ($userAccounts == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            foreach ($userAccounts as $userAccount) {
                unset($userAccount["password"]);
                $userAccount["user_account_id"] = (int) $userAccount["user_account_id"];
                $userAccount["user_detail_id"] = (int) $userAccount["user_detail_id"];
                $userAccount["field_office_id"] = (int) $userAccount["status"];
                $userAccount["region_id"] = (int) $userAccount["region_id"];
                $userAccount["status"] = (int) $userAccount["status"];
                $userAccount["is_pwd"] = (bool) $userAccount["is_pwd"];
                $userAccount["is_senior_citizen"] = (bool) $userAccount["is_senior_citizen"];
                $userAccount["position_id"] = (int) $userAccount["position_id"];

                $accounts[] = $userAccount;
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $accounts);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    /**
     * @return array<string, mixed>
     * @throws
     */
    public function getByID(int $id): array
    {
        try {
            $userAccount = $this->repository->findWithDetails($id);

            if ($userAccount == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            unset($userAccount["password"]);
            $userAccount["user_account_id"] = (int) $userAccount["user_account_id"];
            $userAccount["user_detail_id"] = (int) $userAccount["user_detail_id"];
            $userAccount["field_office_id"] = (int) $userAccount["status"];
            $userAccount["region_id"] = (int) $userAccount["region_id"];
            $userAccount["status"] = (int) $userAccount["status"];
            $userAccount["is_pwd"] = (bool) $userAccount["is_pwd"];
            $userAccount["is_senior_citizen"] = (bool) $userAccount["is_senior_citizen"];
            $userAccount["position_id"] = (int) $userAccount["position_id"];

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $userAccount);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
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
            $userAccountId = $this->repository->create($userAccountWithDetails);

            if ($userAccountId == null) {
                return $this->appFormatter->formatResponse(self::USER_CREATION_FAILED, null, ['app' => 'Email address already exist']);
            }

            return $this->appFormatter->formatResponse(self::USER_CREATION_SUCCESS, ['id' => $userAccountId]);
        } catch (ORMException $exception) {
            return $this->appFormatter->formatResponse(self::USER_CREATION_FAILED, null, ['orm' => $exception->getMessage()]);
        } catch (\Exception | InvalidArgumentException $e) {
            return $this->appFormatter->formatResponse(self::USER_CREATION_FAILED, null, ['app' => $e->getMessage()]);
        }
    }

    public function deleteById(int $id): array
    {
        try {
            $isDeleted = $this->repository->softDelete($id);

            if (! $isDeleted) {
                return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['app' => ResponseEnum::NO_DATA]);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_SUCCESS, null);
        } catch (InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['cache' => $exception->getMessage()]);
        } catch (ORMException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['orm' => $exception->getMessage()]);
        }
    }

    public function updateById(int $id, UserAccountWithDetails $userAccountWithDetails): array
    {
        try {
            $isUpdated = $this->repository->update($id, $userAccountWithDetails);

            if ($isUpdated !== ResponseEnum::OK) {
                return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['app' => $isUpdated]);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_SUCCESS, null);
        } catch (Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['app' => $e->getMessage()]);
        } catch (InvalidArgumentException $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['cache' => $e->getMessage()]);
        }
    }
}