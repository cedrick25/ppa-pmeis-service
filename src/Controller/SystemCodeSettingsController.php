<?php

namespace App\Controller;

use App\Service\System\SystemCodeSettings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/system-code-settings")
 */
class SystemCodeSettingsController extends AbstractController
{
    public function __construct(
        private SystemCodeSettings $service,
    ) {
    }

    /**
     * @Route("/list", methods={"GET"})
     */
    public function getAll(): Response
    {
        return $this->json($this->service->listAll());
    }

    /**
     * @Route("/create", methods={"POST"})
     */
    public function createCode(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $response = $this->service->create($data["name"], $data["value"]);

        return $this->json([
            'message' => $response
        ]);
    }

    /**
     * @Route("/update", methods={"POST"})
     */
    public function updateCode(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $response = $this->service->update($data["id"], $data["name"], $data["value"]);

        return $this->json([
            'message' => $response
        ]);
    }

    /**
     * @Route("/delete/{id}", methods={"GET"})
     */
    public function deleteOffenseById(Request $request): Response
    {
        $response = $this->service->delete($request->get("id"));

        return $this->json([
            'message' => $response
        ]);
    }

    /**
     * @Route("/by/id/{id}", methods={"GET"})
     */
    public function getById(Request $request): Response
    {
        $response = $this->service->getById($request->get("id"));

        return $this->json([
            'message' => $response
        ]);
    }
}