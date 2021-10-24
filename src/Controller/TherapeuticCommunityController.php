<?php

declare(strict_types=1);

namespace App\Controller;

use App\Common\AppFormatter;
use App\Common\AppHydrator;
use App\Model\Quarters as QuartersModel;
use App\Service\TherapeuticCommunity\FieldOfficesInterface;
use App\Service\TherapeuticCommunity\PhasesInterface;
use App\Service\TherapeuticCommunity\QuartersInterface;
use App\Service\TherapeuticCommunity\SessionActivitiesInterface;
use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/tc")
 */
class TherapeuticCommunityController extends AbstractController
{
    public function __construct(
        private AppHydrator $appHydrator,
        private AppFormatter $appFormatter,
        private FieldOfficesInterface $fieldOfficesService,
        private PhasesInterface $phasesService,
        private QuartersInterface $quartersService,
        private SessionActivitiesInterface $sessionActivitiesService
    ){}

    /**
     * @Route("/", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to nothingness',
        ]);
    }

    /**
     * @Route("/quarter/create", methods={"POST"})
     */
    public function createNewQuarter(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var QuartersModel $quarter */
            $quarter = $this->appHydrator->convertArrayToObject($data, QuartersModel::class);

            return $this->json($this->quartersService->createQuarters($quarter));
        } catch (ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse('Quarter creation failed', null, ['reflection' => $exception->getMessage()]));
        }
    }

    /**
     * @Route("/quarter/list", methods={"GET"})
     */
    public function getAllQuarters(): Response
    {
        return $this->json($this->quartersService->getAllQuarters());
    }

    /**
     * @Route("/quarter/delete/{id}", methods={"GET"})
     */
    public function deleteQuarterById(Request $request): Response
    {
        return $this->json($this->quartersService->deleteQuarterById((int) $request->get("id")));
    }

    /**
     * @Route("/phases/list", methods={"GET"})
     */
    public function getAllPhases(): Response
    {
        return $this->json($this->phasesService->getAllPhases());
    }

    /**
     * @Route("/field-office/list", methods={"GET"})
     */
    public function getAllFieldOffice(): Response
    {
        return $this->json($this->fieldOfficesService->getAllFieldOffices());
    }

    /**
     * @Route("/session-activity/list", methods={"GET"})
     */
    public function getAllSessionActivities(): Response
    {
        return $this->json($this->sessionActivitiesService->getAllSessionActivities());
    }

    /**
     * @Route("/session-activity/create/{name}", methods={"GET"})
     */
    public function createNewSessionActivity(Request $request): Response
    {
        return $this->json($this->sessionActivitiesService->createSessionActivity($request->get("name")));
    }

    /**
     * @Route("/session-activity/delete/{id}", methods={"GET"})
     */
    public function deleteSessionActivityById(Request $request): Response
    {
        return $this->json($this->sessionActivitiesService->deleteSessionActivityById((int) $request->get("id")));
    }
}
