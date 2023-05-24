<?php

declare(strict_types=1);

namespace App\Controller;

use App\Common\AppFormatter;
use App\Common\AppHydrator;
use App\Model\UserAccountWithDetails;
use App\Service\PositionInterface;
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
        private UserInterface     $userService,
        private AppHydrator       $appHydrator,
        private AppFormatter      $appFormatter,
        private PositionInterface $positionService,
    ){}

    /**
     * @Route("/a", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->json("23");
    }

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
            
            /** @var UserAccountWithDetails $user */
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
    public function deleteById(Request $request): Response
    {
        return $this->json($this->userService->deleteById((int) $request->get("id")));
    }

    /**
     * @Route("/update/{id}", methods={"POST"})
     */
    public function update(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var UserAccountWithDetails $user */
            $user = $this->appHydrator->convertArrayToObject($data, UserAccountWithDetails::class);

            return $this->json($this->userService->updateById((int) $request->get("id"), $user));
        } catch (ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse('User update failed', null, ['reflection' => $exception->getMessage()]));
        }
    }

    /**
     * @Route("/paginated/{page}/{pageSize}", methods={"GET"})
     */
    public function getPaginatedUsers(Request $request): Response
    {
        return $this->json($this->userService->getPaginated(
            (int) $request->get("page"),
            (int) $request->get("pageSize")
        ));
    }

    /**
     * @Route("/position/list", methods={"GET"})
     */
    public function getAllPositions(): Response
    {
        return $this->json($this->positionService->getAll());
    }

    /**
     * @Route("/position/create/{name}", methods={"GET"})
     */
    public function createPosition(Request $request): Response
    {
        return $this->json($this->positionService->create($request->get("name")));
    }

    /**
     * @Route("/position/by/id/{id}", methods={"GET"})
     */
    public function getPositionById(Request $request): Response
    {
        return $this->json($this->positionService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/position/{page}/{pageSize}", methods={"GET"})
     */
    public function getPaginatedPositions(Request $request): Response
    {
        return $this->json($this->positionService->getPaginated(
            (int) $request->get("page"),
            (int) $request->get("pageSize")
        ));
    }
}
