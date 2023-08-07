<?php

declare(strict_types=1);

namespace App\Controller;

use App\Common\AppFormatter;
use App\Common\AppHydrator;
use App\Model\RJConductProcesses as ConductProcessesModel;
use App\Model\RJRelatedActivities as RelatedActivitiesModel;
use App\Model\RjRelatedRestitutions as RelatedRestitutionsModel;
use App\Service\RestorativeJustice\ConductProcessesInterface;
use App\Service\RestorativeJustice\OffensesInterface;
use App\Service\RestorativeJustice\OutcomesInterface;
use App\Service\RestorativeJustice\PaymentFormsInterface;
use App\Service\RestorativeJustice\PaymentModesInterface;
use App\Service\RestorativeJustice\ProcessesInterface;
use App\Service\RestorativeJustice\ProcessStatusInterface;
use App\Service\RestorativeJustice\RelatedActivitiesInterface;
use App\Service\RestorativeJustice\RelatedRestitutionsInterface;
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
        private AppHydrator                         $appHydrator,
        private AppFormatter                        $appFormatter,
        private ConductProcessesInterface           $conductProcessesService,
        private OffensesInterface                   $offensesService,
        private ProcessesInterface                  $processesService,
        private ProcessStatusInterface              $processStatusService,
        private OutcomesInterface                   $outcomesService,
        private RelatedActivitiesInterface          $relatedActivitiesService,
        private PaymentFormsInterface               $paymentFormsService,
        private PaymentModesInterface               $paymentModesService,
        private RelatedRestitutionsInterface        $relatedRestitutionsService,
    ){}

    /**
     * @Route("/conduct-process/create", methods={"POST"})
     */
    public function createConductProcess(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var ConductProcessesModel $conductProcesses */
            $conductProcesses = $this->appHydrator->convertArrayToObject($data, ConductProcessesModel::class);

            return $this->json($this->conductProcessesService->create($conductProcesses));
        } catch (ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse('Creating RJ conduct processes failed', null, ['reflection' => $exception->getMessage()]));
        }
    }

    /**
     * @Route("/conduct-process/list", methods={"GET"})
     */
    public function getAllConductProcess(): Response
    {
        return $this->json($this->conductProcessesService->getAll());
    }

    /**
     * @Route("/conduct-process/by/id/{id}", methods={"GET"})
     */
    public function getConductProcessById(Request $request): Response
    {
        return $this->json($this->conductProcessesService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/conduct-process/delete/{id}", methods={"GET"})
     */
    public function deleteConductProcessById(Request $request): Response
    {
        return $this->json($this->conductProcessesService->deleteById((int) $request->get("id")));
    }

    /**
     * @Route("/conduct-process/rjib1/full/{quarterId}/{fieldOfficeId}", methods={"GET"})
     */
    public function getRJIB1(Request $request): Response
    {
        return $this->json($this->conductProcessesService->getRJIB1(
            (int) $request->get("quarterId"),
            (int) $request->get("fieldOfficeId"),
        ));
    }

    /**
     * @Route("/conduct-process/update/{id}", methods={"POST"})
     */
    public function updateConductProcessById(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        /** @var ConductProcessesModel $conductProcesses */
        $conductProcesses = $this->appHydrator->convertArrayToObject($data, ConductProcessesModel::class);

        return $this->json($this->conductProcessesService->update((int) $request->get("id"), $conductProcesses));
    }

    /**
     * @Route("/conduct-process/{page}/{pageSize}", methods={"GET"})
     */
    public function getPaginatedConductedProcess(Request $request): Response
    {
        return $this->json($this->conductProcessesService->getPaginated(
            (int) $request->get("page"),
            (int) $request->get("pageSize"),
            (int) $request->query->get('field_office_id')
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
     * @Route("/process/list", methods={"GET"})
     */
    public function getAllProcesses(): Response
    {
        return $this->json($this->processesService->getAll());
    }

    /**
     * @Route("/process/by/id/{id}", methods={"GET"})
     */
    public function getProcessById(Request $request): Response
    {
        return $this->json($this->processesService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/process-status/list", methods={"GET"})
     */
    public function getAllProcessesStatus(): Response
    {
        return $this->json($this->processStatusService->getAll());
    }

    /**
     * @Route("/process-status/by/id/{id}", methods={"GET"})
     */
    public function getProcessStatusById(Request $request): Response
    {
        return $this->json($this->processStatusService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/outcomes/list", methods={"GET"})
     */
    public function getAllOutcomes(): Response
    {
        return $this->json($this->outcomesService->getAll());
    }

    /**
     * @Route("/outcomes/by/id/{id}", methods={"GET"})
     */
    public function getRJOutcomeById(Request $request): Response
    {
        return $this->json($this->outcomesService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/related-activities/create", methods={"POST"})
     */
    public function createRelatedActivities(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var RelatedActivitiesModel $relatedActivities */
            $relatedActivities = $this->appHydrator->convertArrayToObject($data, RelatedActivitiesModel::class);

            return $this->json($this->relatedActivitiesService->create($relatedActivities));
        } catch (ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse('Creating RJ conduct processes failed', null, ['reflection' => $exception->getMessage()]));
        }
    }

    /**
     * @Route("/related-activities/list", methods={"GET"})
     */
    public function getAllRelatedActivities(): Response
    {
        return $this->json($this->relatedActivitiesService->getAll());
    }

    /**
     * @Route("/related-activities/by/id/{id}", methods={"GET"})
     */
    public function getRelatedActivity(Request $request): Response
    {
        return $this->json($this->relatedActivitiesService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/related-activities/delete/{id}", methods={"GET"})
     */
    public function deleteRelatedActivityById(Request $request): Response
    {
        return $this->json($this->relatedActivitiesService->deleteById((int) $request->get("id")));
    }

    /**
     * @Route("/related-activities/rjib2/full/{quarterId}/{fieldOfficeId}", methods={"GET"})
     */
    public function getRJIB2(Request $request): Response
    {
        return $this->json($this->relatedActivitiesService->getRJIB2Data(
            (int) $request->get("quarterId"),
            (int) $request->get("fieldOfficeId"),
        ));
    }

    /**
     * @Route("/related-activities/update/{id}", methods={"POST"})
     */
    public function updateRelatedActivityById(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        /** @var RelatedActivitiesModel $relatedActivities */
        $relatedActivities = $this->appHydrator->convertArrayToObject($data, RelatedActivitiesModel::class);

        return $this->json($this->relatedActivitiesService->update((int) $request->get("id"), $relatedActivities));
    }

    /**
     * @Route("/related-activities/{page}/{pageSize}", methods={"GET"})
     */
    public function getPaginatedRelatedActivities(Request $request): Response
    {
        return $this->json($this->relatedActivitiesService->getPaginated(
            (int) $request->get("page"),
            (int) $request->get("pageSize"),
            (int) $request->query->get('field_office_id')
        ));
    }

    /**
     * @Route("/payment-forms/list", methods={"GET"})
     */
    public function getAllPaymentForms(): Response
    {
        return $this->json($this->paymentFormsService->getAll());
    }

    /**
     * @Route("/payment-forms/by/id/{id}", methods={"GET"})
     */
    public function getPaymentFormById(Request $request): Response
    {
        return $this->json($this->paymentFormsService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/payment-modes/list", methods={"GET"})
     */
    public function getAllPaymentModes(): Response
    {
        return $this->json($this->paymentModesService->getAll());
    }

    /**
     * @Route("/payment-modes/by/id/{id}", methods={"GET"})
     */
    public function getPaymentModeById(Request $request): Response
    {
        return $this->json($this->paymentModesService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/related-restitutions/create", methods={"POST"})
     */
    public function createRelatedRestitution(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var RelatedRestitutionsModel $relatedRestitutions */
            $relatedRestitutions = $this->appHydrator->convertArrayToObject($data, RelatedRestitutionsModel::class);

            return $this->json($this->relatedRestitutionsService->create($relatedRestitutions));
        } catch (ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse('Creating RJ related restitutions failed', null, ['reflection' => $exception->getMessage()]));
        }
    }

    /**
     * @Route("/related-restitutions/list", methods={"GET"})
     */
    public function getAllRelatedRestitutions(): Response
    {
        return $this->json($this->relatedRestitutionsService->getAll());
    }

    /**
     * @Route("/related-restitutions/by/id/{id}", methods={"GET"})
     */
    public function getRelatedRestitution(Request $request): Response
    {
        return $this->json($this->relatedRestitutionsService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/related-restitutions/delete/{id}", methods={"GET"})
     */
    public function deleteRelatedRestitutionById(Request $request): Response
    {
        return $this->json($this->relatedRestitutionsService->deleteById((int) $request->get("id")));
    }

    /**
     * @Route("/related-restitutions/rjib3/full/{quarterId}/{fieldOfficeId}", methods={"GET"})
     */
    public function getRJIB3(Request $request): Response
    {
        return $this->json($this->relatedRestitutionsService->getRJIB3Data(
            (int) $request->get("quarterId"),
            (int) $request->get("fieldOfficeId"),
        ));
    }

    /**
     * @Route("/related-restitutions/loadform/{fieldOfficeId}/{clientId}", methods={"GET"})
     */
    public function loadForm(Request $request): Response
    {
        return $this->json($this->relatedRestitutionsService->loadForm(
            (int) $request->get("fieldOfficeId"),
            (int) $request->get("clientId"),
        ));
    }
}