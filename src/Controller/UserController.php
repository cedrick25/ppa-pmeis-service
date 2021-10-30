<?php

declare(strict_types=1);

namespace App\Controller;

use App\Common\AppFormatter;
use App\Common\AppHydrator;
use App\Model\UserAccountWithDetails;
use App\Service\UserInterface;
use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/user")
 */
class UserController extends AbstractController
{
    public function __construct(
        private UserInterface $userService,
        private AppHydrator   $appHydrator,
        private AppFormatter  $appFormatter,
    ){}

    /**
     * @Route("/list", methods={"GET"})
     */
    public function list(): Response
    {
        return $this->json($this->userService->getAll());
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

    /**
     * @Route("/{id}", methods={"GET"})
     */
    public function getById(Request $request): Response
    {
        return $this->json($this->userService->getByID((int) $request->get("id")));
    }

    /**
     * @Route("/delete/{id}", methods={"GET"})
     */
    public function deleteQuarterById(Request $request): Response
    {
        return $this->json($this->userService->deleteById((int) $request->get("id")));
    }
}
