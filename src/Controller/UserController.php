<?php

declare(strict_types=1);

namespace App\Controller;

use App\Common\AppFormatter;
use App\Common\AppHydrator;
use App\Model\UserAccountWithDetails;
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
        private AppFormatter $appFormatter,
    ){}

    /**
     */
    public function index(): Response
    {
        return $this->json($this->userService->getUserByID(1));
    }

    public function register(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            /** @var $user UserAccountWithDetails */
            $user = $this->appHydrator->convertArrayToObject($data, UserAccountWithDetails::class);

            return $this->json($this->userService->register($user));
        } catch (ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse('User creation failed', null, ['reflection' => $exception->getMessage()]));
        }
    }
}
