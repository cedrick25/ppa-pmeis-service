<?php

declare(strict_types=1);

namespace App\Service;

use App\Common\AppErrorFormatter;
use App\Model\UserAccountWithDetails;
use App\Repository\UserAccountRepository;
use Doctrine\ORM\ORMException;
use Psr\Cache\InvalidArgumentException;
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
     */
    public function register(UserAccountWithDetails $userAccountWithDetails): array
    {
        try {
            $errors = $this->validator->validate($userAccountWithDetails);

            if (count($errors) > 0) {
                return $this->formatResponse('User validation failed', null, $this->appErrorFormatter->format($errors));
            }
            $userAccountId = $this->userAccountRepository->createUser($userAccountWithDetails);

            if ($userAccountId == 0) {
                return $this->formatResponse('User creation failed', null, ['app' => 'Email address already exist']);
            }

            return $this->formatResponse('User creation successful', ['id' => $userAccountId]);
        } catch (ORMException $exception) {
            return $this->formatResponse('User creation failed', null, ['orm' => $exception->getMessage()]);
        } catch (\Exception $e) {
            return $this->formatResponse('User creation failed', null, ['app' => $e->getMessage()]);
        }
    }

    /**
     * @param string $message
     * @param array<string, string>|null $data
     * @param array<string, string>|null $errors
     * @return array<string, mixed>
     */
    private function formatResponse(string $message, ?array $data, ?array $errors = null): array
    {
        $response = ['message' => $message];
        if ($data != null) {
            $response['data'] = $data;
        }
        if ($errors != null) {
            $response['errors'] = $errors;
        }

        return $response;
    }
}