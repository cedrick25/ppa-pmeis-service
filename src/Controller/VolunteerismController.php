<?php

declare(strict_types=1);

namespace App\Controller;

use App\Common\AppFormatter;
use App\Common\AppHydrator;
use App\Model\VolunteerOperations as VolunteerOperationsModel;
use App\Service\Volunteerism\OperationsInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/volunteerism")
 */
class VolunteerismController extends AbstractController
{
    public function __construct(
        private AppHydrator         $appHydrator,
        private AppFormatter        $appFormatter,
        private OperationsInterface $operationService,
    ){}

    /**
     * @Route("/operations/create", methods={"POST"})
     */
    public function createOperations(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var VolunteerOperationsModel $operations */
            $operations = $this->appHydrator->convertArrayToObject($data, VolunteerOperationsModel::class);

            return $this->json($this->operationService->create($operations));
        } catch (\ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse('Volunteer operations failed', null, ['reflection' => $exception->getMessage()]));
        }
    }

    /**
     * @Route("/operations/list", methods={"GET"})
     */
    public function getAllOperations(): Response
    {
        return $this->json($this->operationService->getAll());
    }

    /**
     * @Route("/operations/by/id/{id}", methods={"GET"})
     */
    public function getOperationById(Request $request): Response
    {
        return $this->json($this->operationService->getById((int) $request->get("id")));
    }
}