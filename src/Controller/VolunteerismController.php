<?php

declare(strict_types=1);

namespace App\Controller;

use App\Common\AppFormatter;
use App\Common\AppHydrator;
use App\Model\VolunteerOperations as VolunteerOperationsModel;
use App\Model\IdSupport as IdSupportModel;
use App\Model\VolunteerId as VolunteerIdModel;
use App\Service\Volunteerism\IdInterface;
use App\Service\Volunteerism\IdSupportInterface;
use App\Service\Volunteerism\OperationsInterface;
use App\Service\Volunteerism\ServicesRenderedInterface;
use App\Model\TechnicalAssistance as TechnicalAssistanceModel;
use App\Service\Volunteerism\TechnicalAssistanceInterface;
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
        private AppHydrator                  $appHydrator,
        private AppFormatter                 $appFormatter,
        private OperationsInterface          $operationService,
        private IdInterface                  $idService,
        private ServicesRenderedInterface    $servicesRenderedService,
        private IdSupportInterface           $idSupportService,
        private TechnicalAssistanceInterface $technicalAssistanceService,
    ){}

    /**
     * @Route("/operations/create", methods={"POST"})
     */
    public function createOperation(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var VolunteerOperationsModel $operation */
            $operation = $this->appHydrator->convertArrayToObject($data, VolunteerOperationsModel::class);

            return $this->json($this->operationService->create($operation));
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

    /**
     * @Route("/by/field-office-quarter/{fieldOfficeId}/{quarterId}", methods={"GET"})
     */
    public function getVolunteerOperationsByFieldOfficeAndMonthRange(Request $request): Response
    {
        return $this->json($this->operationService->getVPA2(
            (int) $request->get("fieldOfficeId"),
            (int) $request->get("quarterId")
        ));
    }

    /**
     * @Route("/id/create", methods={"POST"})
     */
    public function createId(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var VolunteerIdModel $id */
            $id = $this->appHydrator->convertArrayToObject($data, VolunteerIdModel::class);

            return $this->json($this->idService->create($id));
        } catch (\ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse('Volunteer id failed', null, ['reflection' => $exception->getMessage()]));
        }
    }

    /**
     * @Route("/id/list", methods={"GET"})
     */
    public function getAllIds(): Response
    {
        return $this->json($this->idService->getAll());
    }

    /**
     * @Route("/id/by/id/{id}", methods={"GET"})
     */
    public function getIdById(Request $request): Response
    {
        return $this->json($this->idService->getById($request->get("id")));
    }

    /**
     * @Route("/services-rendered/list", methods={"GET"})
     */
    public function getAllServicesRendered(): Response
    {
        return $this->json($this->servicesRenderedService->getAll());
    }

    /**
     * @Route("/services-rendered/by/id/{id}", methods={"GET"})
     */
    public function getgetAllServiceRenderedById(Request $request): Response
    {
        return $this->json($this->servicesRenderedService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/id-support/create", methods={"POST"})
     */
    public function createIdSupport(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var IdSupportModel $idSupport */
            $idSupport = $this->appHydrator->convertArrayToObject($data, IdSupportModel::class);

            return $this->json($this->idSupportService->create($idSupport));
        } catch (\ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse('Creating ID Support failed', null, ['reflection' => $exception->getMessage()]));
        }
    }

    /**
     * @Route("/id-support/list", methods={"GET"})
     */
    public function getAllIdSupport(): Response
    {
        return $this->json($this->idSupportService->getAll());
    }

    /**
     * @Route("/id-support/by/id/{id}", methods={"GET"})
     */
    public function getIdSupportById(Request $request): Response
    {
        return $this->json($this->idSupportService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/id-support/delete/{id}", methods={"GET"})
     */
    public function deleteIdSupportById(Request $request): Response
    {
        return $this->json($this->idSupportService->deleteById((int) $request->get("id")));
    }

    /**
     * @Route("/id-support/report/full/{quarterId}/{fieldOfficeId}", methods={"GET"})
     */
    public function getIdSupportReport(Request $request): Response
    {
        return $this->json($this->idSupportService->getIdSupportReport(
            (int) $request->get("quarterId"),
            (int) $request->get("fieldOfficeId"),
        ));
    }

    /**
     * @Route("/technical-assistance/create", methods={"POST"})
     */
    public function createTechnicalAssistance(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var TechnicalAssistanceModel $technicalAssistance */
            $technicalAssistance = $this->appHydrator->convertArrayToObject($data, TechnicalAssistanceModel::class);

            return $this->json($this->technicalAssistanceService->create($technicalAssistance));
        } catch (\ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse('Creating Technical Assistance failed', null, ['reflection' => $exception->getMessage()]));
        }
    }

    /**
     * @Route("/technical-assistance/list", methods={"GET"})
     */
    public function getAllTechnicalAssistance(): Response
    {
        return $this->json($this->technicalAssistanceService->getAll());
    }

    /**
     * @Route("/technical-assistance/by/id/{id}", methods={"GET"})
     */
    public function getTechnicalAssistanceById(Request $request): Response
    {
        return $this->json($this->technicalAssistanceService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/technical-assistance/delete/{id}", methods={"GET"})
     */
    public function deleteTechnicalAssistanceById(Request $request): Response
    {
        return $this->json($this->technicalAssistanceService->deleteById((int) $request->get("id")));
    }
}