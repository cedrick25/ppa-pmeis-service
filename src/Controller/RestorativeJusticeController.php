<?php

declare(strict_types=1);

namespace App\Controller;

use App\Common\AppFormatter;
use App\Common\AppHydrator;
use App\Model\RJConductProcesses as RJConductProcessesModel;
use App\Service\RestorativeJustice\ConductProcessesInterface;
use App\Service\RestorativeJustice\OffensesInterface;
use App\Service\RestorativeJustice\RJOutcomesInterface;
use App\Service\RestorativeJustice\RJProcessesInterface;
use App\Service\RestorativeJustice\RJProcessStatusInterface;
use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/rj")
 */
class RestorativeJusticeController extends AbstractController
{
    public function __construct(
        private AppHydrator $appHydrator,
        private AppFormatter $appFormatter,
        private ConductProcessesInterface $conductProcessesService,
        private OffensesInterface $offensesService,
        private RJProcessesInterface $rjProcessesService,
        private RJProcessStatusInterface $rjProcessStatusService,
        private RJOutcomesInterface $rjOutcomesService,
    ){}

    /**
     * @Route("/conduct-process/create", methods={"POST"})
     */
    public function createRJConductProcess(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var RJConductProcessesModel $RJConductProcesses */
            $RJConductProcesses = $this->appHydrator->convertArrayToObject($data, RJConductProcessesModel::class);

            return $this->json($this->conductProcessesService->create($RJConductProcesses));
        } catch (ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse('Creating RJ conduct processes failed', null, ['reflection' => $exception->getMessage()]));
        }
    }

    /**
     * @Route("/conduct-process/list", methods={"GET"})
     */
    public function getAllRJConductProcess(): Response
    {
        return $this->json($this->conductProcessesService->getAll());
    }

    /**
     * @Route("/conduct-process/by/id/{id}", methods={"GET"})
     */
    public function getRJConductProcessById(Request $request): Response
    {
        return $this->json($this->conductProcessesService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/conduct-process/delete/{id}", methods={"GET"})
     */
    public function deleteRJConductProcessById(Request $request): Response
    {
        return $this->json($this->conductProcessesService->deleteById((int) $request->get("id")));
    }

    /**
     * @Route("/conduct-process/rjib1/full/{clientId}/{quarterId}/{fieldOfficeId}", methods={"GET"})
     */
    public function getRJIB1(Request $request): Response
    {
        return $this->json($this->conductProcessesService->getRJIB1(
            (int) $request->get("clientId"),
            (int) $request->get("quarterId"),
            (int) $request->get("fieldOfficeId"),
        ));
    }

    /**
     * @Route("/offense/create/{name}/{type}", methods={"GET"})
     */
    public function createOffense(Request $request): Response
    {
        return $this->json($this->offensesService->create(
            $request->get("name"),
            $request->get("type"))
        );
    }

    /**
     * @Route("/offense/list", methods={"GET"})
     */
    public function getAllOffenses(): Response
    {
        return $this->json($this->offensesService->getAll());
    }

    /**
     * @Route("/offense/by/id/{id}", methods={"GET"})
     */
    public function getOffenseById(Request $request): Response
    {
        return $this->json($this->offensesService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/offense/update/{id}/{name}/{type}", methods={"GET"})
     */
    public function updateOffenseById(Request $request): Response
    {
        return $this->json($this->offensesService->updateById(
            (int) $request->get("id"),
            $request->get("name"),
            $request->get("type"))
        );
    }

    /**
     * @Route("/offense/delete/{id}", methods={"GET"})
     */
    public function deleteOffenseById(Request $request): Response
    {
        return $this->json($this->offensesService->deleteById((int) $request->get("id")));
    }

    /**
     * @Route("/rj-process/list", methods={"GET"})
     */
    public function getAllRJProcesses(): Response
    {
        return $this->json($this->rjProcessesService->getAll());
    }

    /**
     * @Route("/rj-process/by/id/{id}", methods={"GET"})
     */
    public function getRJProcessById(Request $request): Response
    {
        return $this->json($this->rjProcessesService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/rj-process-status/list", methods={"GET"})
     */
    public function getAllRJProcessesStatus(): Response
    {
        return $this->json($this->rjProcessStatusService->getAll());
    }

    /**
     * @Route("/rj-process-status/by/id/{id}", methods={"GET"})
     */
    public function getRJProcessStatusById(Request $request): Response
    {
        return $this->json($this->rjProcessStatusService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/rj-outcomes/list", methods={"GET"})
     */
    public function getAllRJOutcomes(): Response
    {
        return $this->json($this->rjOutcomesService->getAll());
    }

    /**
     * @Route("/rj-outcomes/by/id/{id}", methods={"GET"})
     */
    public function getRJOutcomeById(Request $request): Response
    {
        return $this->json($this->rjOutcomesService->getById((int) $request->get("id")));
    }
}
