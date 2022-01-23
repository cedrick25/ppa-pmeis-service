<?php

namespace App\Controller;

use App\Common\AppFormatter;
use App\Common\AppHydrator;
use App\Model\RJConductProcesses as RJConductProcessesModel;
use App\Service\RestorativeJustice\ConductProcessesInterface;
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

}
