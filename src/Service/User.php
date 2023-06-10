<?php

declare(strict_types=1);

namespace App\Service;

use App\Common\AppFormatter;
use App\Common\AppHydrator;
use App\Enum\AuditTrailActions;
use App\Enum\Response as ResponseEnum;
use App\Model\UserAccountWithDetails;
use App\Plugin\PpaApiClient;
use App\Repository\UserAccountRepository;
use App\Repository\UserOtpRepository;
use App\Service\System\AuditTrail;
use Doctrine\ORM\ORMException;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class User implements UserInterface
{
    private string $shortName;

    public const USER_CREATION_SUCCESS = "User creation successful.";
    public const USER_CREATION_FAILED = "User creation failed.";
    public const USER_VALIDATION_FAILED = "User validation failed.";

    public function __construct(
        private UserAccountRepository       $repository,
        private ValidatorInterface          $validator,
        private AppFormatter                $appFormatter,
        private AuditTrail                  $auditTrail,
        private AppHydrator                 $hydrator,
        private UserPasswordHasherInterface $userPasswordHasher,
        private PpaApiClient                $ppaApiClient,
        private UserOtpRepository           $userOtpRepository,
        private JWTTokenManagerInterface    $jWTTokenManager,
    ) {
        $class = new \ReflectionClass($this);
        $this->shortName = $class->getShortName();
    }

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
        } catch (CacheException|InvalidArgumentException $exception) {
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

            $this->auditTrail->log(AuditTrailActions::CREATE, $userAccountWithDetails->jsonSerialize(), $this->shortName, $userAccountId);

            return $this->appFormatter->formatResponse(self::USER_CREATION_SUCCESS, ['id' => $userAccountId]);
        } catch (ORMException $exception) {
            return $this->appFormatter->formatResponse(self::USER_CREATION_FAILED, null, ['orm' => $exception->getMessage()]);
        } catch (\Exception | InvalidArgumentException $e) {
            return $this->appFormatter->formatResponse(self::USER_CREATION_FAILED, null, ['app' => $e->getMessage()]);
        }
    }

    /**
     * @return string|null
     */
    public function login(string $email, string $password, bool $encrypted): ?string
    {
        if ($encrypted) {
            $email = base64_decode($email);
            $password = base64_decode($password);
        }

        $user = $this->repository->findOneBy((['emailAddress' => $email]));

        if (null == $user) {
            return null;
        }

        $isPasswordValid = $this->userPasswordHasher->isPasswordValid($user, $password);

        if (!$isPasswordValid) {
            return null;
        }

        // if ($encrypted) {
        //     return [
        //         'token' => $this->jWTTokenManager->create($user),
        //     ];
        // }

        $otp = bin2hex(openssl_random_pseudo_bytes(5));
        $otp = substr($otp, 0, 5);
        $message =  "Your PMEIS OTP is " . (string) $otp;

        $isEmailOtpSent = $this->ppaApiClient->sendEmail($email, $message, $user->getUserAccountId());
        
        if (!$isEmailOtpSent) {
            return null;
        }

        if (null != $user->getContactNumber()) {
            $this->ppaApiClient->sendSMS($user->getContactNumber(), $message, $user->getUserAccountId());
        }

        $this->userOtpRepository->create($user->getUserAccountId(), (string) $otp);

        return '/api/user/verify';
    }

    public function deleteById(int $id): array
    {
        try {
            $isDeleted = $this->repository->softDelete($id);

            if (! $isDeleted) {
                return $this->appFormatter->formatResponse(ResponseEnum::DELETING_FAILED, null, ['app' => ResponseEnum::NO_DATA]);
            }

            $this->auditTrail->log(AuditTrailActions::DELETE, [], $this->shortName, $id);

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

            $this->auditTrail->log(AuditTrailActions::UPDATE, $userAccountWithDetails->jsonSerialize(), $this->shortName, $id);

            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_SUCCESS, null);
        } catch (Exception $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['app' => $e->getMessage()]);
        } catch (InvalidArgumentException $e) {
            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_FAILED, null, ['cache' => $e->getMessage()]);
        }
    }

    public function getPaginated(int $page, int $pageSize): array
    {
        try {
            $users = $this->repository->paginated($page, $pageSize);

            if (sizeof($users) == 0) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $users);
        } catch (CacheException|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }
}