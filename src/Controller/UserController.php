<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\UserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController
{
    public function __construct(private UserServiceInterface $userService)
    {
        
    }
    public function index(): Response
    {
        // TODO: Convert class object to json response
        return $this->json($this->userService->getUserAccount()->getContactNumber());
    }
}
