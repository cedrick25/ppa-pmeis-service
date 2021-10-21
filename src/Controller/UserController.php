<?php

declare(strict_types=1);

namespace App\Controller;

use App\Common\AppHydrator;
use App\Model\UserAccount as UserAccountModel;
use App\Service\UserServiceInterface;
use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function register(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        /** @var $user UserAccountModel */
        $user = $this->appHydrator->convertArrayToObject($data, UserAccountModel::class);

        return $this->json($this->userService->register($user));
    }
}
