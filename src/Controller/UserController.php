<?php

declare(strict_types=1);

namespace App\Controller;

use App\Common\ObjectToArray;
use App\Service\UserServiceInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class UserController extends AbstractController
{
    public function __construct(
        private UserServiceInterface $userService,
    ){}

    /**
     */
    public function index(): Response
    {
        return $this->json($this->userService->getUserByID(1));
    }

    public function authenticate(): Response
    {
        return $this->json([
           'username' => "icaminajerick@gmail.com",
           'role' => 'ROLE_ADMIN'
        ]);
    }

    public function register(): Response
    {

        return $this->json("Okayed");
    }
}
