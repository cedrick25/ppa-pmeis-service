<?php

declare(strict_types=1);

namespace App\Controller;

use App\Common\ObjectToArray;
use App\Service\UserServiceInterface;
use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

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
}
