<?php

declare(strict_types=1);

namespace App\Controller;

use App\Common\AppHydrator;
use App\Model\UserAccount as UserAccountModel;
use App\Service\UserServiceInterface;
use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController
{
    public function __construct(
        private UserServiceInterface $userService,
        private AppHydrator $appHydrator,
    ){}

    /**
     */
    public function index(): Response
    {
        return $this->json($this->userService->getUserByID(1));
    }

    /**
     * @throws ReflectionException
     */
    public function register(): Response
    {
        $data = [
            "contactNumber" => "", "password" => "", "userType" => "", "status" => 1, "region" => 1, "fieldOffice" => 1, "emailAddress" => "testing!"
        ];

        /** @var $user UserAccountModel */
        $user = $this->appHydrator->convertArrayToObject($data, UserAccountModel::class);

        $this->userService->register($user);

        return $this->json("Okayed");
    }
}
