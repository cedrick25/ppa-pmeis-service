<?php

declare(strict_types=1);

namespace App\Controller;

use App\Common\AppFormatter;
use App\Common\AppHydrator;
use App\Model\ClientSessions as ClientSessionModel;
use App\Model\Quarters as QuartersModel;
use App\Model\Sessions as SessionsModel;
use App\Service\TherapeuticCommunity\ClientSessionsInterface;
use App\Service\TherapeuticCommunity\ClientTypesInterface;
use App\Service\TherapeuticCommunity\FieldOfficesInterface;
use App\Service\TherapeuticCommunity\PhasesInterface;
use App\Service\TherapeuticCommunity\QuartersInterface;
use App\Service\TherapeuticCommunity\SessionActivitiesInterface;
use App\Service\TherapeuticCommunity\SessionsInterface;
use App\Service\TherapeuticCommunity\TreatmentCategoriesInterface;
use App\Service\TherapeuticCommunity\VenuesInterface;
use App\Service\TherapeuticCommunity\ClientsInterface;
use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\ClientTypes as ClientTypesModel;
use App\Model\Clients as ClientModel;

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
        private SessionActivitiesInterface $sessionActivitiesService,
        private VenuesInterface $venuesService,
        private SessionsInterface $sessionService,
        private ClientTypesInterface $clientTypeService,
        private TreatmentCategoriesInterface $treatmentCategoryService,
        private ClientsInterface $clientService,
        private ClientSessionsInterface $clientSessionService,
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

            return $this->json($this->quartersService->create($quarter));
        } catch (ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse('Creating quarter failed', null, ['reflection' => $exception->getMessage()]));
        }
    }

    /**
     * @Route("/quarter/list", methods={"GET"})
     */
    public function getAllQuarters(): Response
    {
        return $this->json($this->quartersService->getAll());
    }

    /**
     * @Route("/quarter/delete/{id}", methods={"GET"})
     */
    public function deleteQuarterById(Request $request): Response
    {
        return $this->json($this->quartersService->deleteById((int) $request->get("id")));
    }

    /**
     * @Route("/quarter/update/{id}", methods={"POST"})
     */
    public function updateQuarter(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var QuartersModel $quarter */
            $quarter = $this->appHydrator->convertArrayToObject($data, QuartersModel::class);

            return $this->json($this->quartersService->updateById((int) $request->get("id"), $quarter));
        } catch (ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse('Updating quarter failed', null, ['reflection' => $exception->getMessage()]));
        }
    }

    /**
     * @Route("/phases/list", methods={"GET"})
     */
    public function getAllPhases(): Response
    {
        return $this->json($this->phasesService->getAll());
    }

    /**
     * @Route("/field-office/list", methods={"GET"})
     */
    public function getAllFieldOffice(): Response
    {
        return $this->json($this->fieldOfficesService->getAll());
    }

    /**
     * @Route("/session-activity/list", methods={"GET"})
     */
    public function getAllSessionActivities(): Response
    {
        return $this->json($this->sessionActivitiesService->getAll());
    }

    /**
     * @Route("/session-activity/create/{name}", methods={"GET"})
     */
    public function createNewSessionActivity(Request $request): Response
    {
        return $this->json($this->sessionActivitiesService->create($request->get("name")));
    }

    /**
     * @Route("/session-activity/delete/{id}", methods={"GET"})
     */
    public function deleteSessionActivityById(Request $request): Response
    {
        return $this->json($this->sessionActivitiesService->deleteById((int) $request->get("id")));
    }

    /**
     * @Route("/venue/create/{name}", methods={"GET"})
     */
    public function createVenue(Request $request): Response
    {
        return $this->json($this->venuesService->create($request->get("name")));
    }

    /**
     * @Route("/venue/list", methods={"GET"})
     */
    public function getAllVenues(): Response
    {
        return $this->json($this->venuesService->getAll());
    }

    /**
     * @Route("/venue/delete/{id}", methods={"GET"})
     */
    public function deleteVenueById(Request $request): Response
    {
        return $this->json($this->venuesService->deleteById((int) $request->get("id")));
    }

    /**
     * @Route("/session/create", methods={"POST"})
     */
    public function createSession(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var SessionsModel $session */
            $session = $this->appHydrator->convertArrayToObject($data, SessionsModel::class);

            return $this->json($this->sessionService->create($session));
        } catch (ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse('Creating session failed', null, ['reflection' => $exception->getMessage()]));
        }
    }

    /**
     * @Route("/session/list", methods={"GET"})
     */
    public function getAllSessions(): Response
    {
        return $this->json($this->sessionService->getAll());
    }

    /**
     * @Route("/session/delete/{id}", methods={"GET"})
     */
    public function deleteSessionById(Request $request): Response
    {
        return $this->json($this->sessionService->deleteById((int) $request->get("id")));
    }

    /**
     * @Route("/session/update/{id}", methods={"POST"})
     */
    public function updateSessionById(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var SessionsModel $session */
            $session = $this->appHydrator->convertArrayToObject($data, SessionsModel::class);

            return $this->json($this->sessionService->updateById((int) $request->get("id"), $session));
        } catch (ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse('Updating session failed', null, ['reflection' => $exception->getMessage()]));
        }
    }

    /**
     * @Route("/client-type/create", methods={"POST"})
     */
    public function createClientType(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var ClientTypesModel $clientType */
            $clientType = $this->appHydrator->convertArrayToObject($data, ClientTypesModel::class);

            return $this->json($this->clientTypeService->create($clientType));
        } catch (ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse('Creating session failed', null, ['reflection' => $exception->getMessage()]));
        }
    }

    /**
     * @Route("/client-type/list", methods={"GET"})
     */
    public function getAllClientTypes(): Response
    {
        return $this->json($this->clientTypeService->getAll());
    }

    /**
     * @Route("/client-type/delete/{id}", methods={"GET"})
     */
    public function deleteClientTypeById(Request $request): Response
    {
        return $this->json($this->clientTypeService->deleteById((int) $request->get("id")));
    }

    /**
     * @Route("/treatment-category/list", methods={"GET"})
     */
    public function getAllTreatmentCategories(): Response
    {
        return $this->json($this->treatmentCategoryService->getAll());
    }

    /**
     * @Route("/treatment-category/create/{name}", methods={"GET"})
     */
    public function createTreatmentCategory(Request $request): Response
    {
        return $this->json($this->treatmentCategoryService->create($request->get("name")));
    }

    /**
     * @Route("/treatment-category/delete/{id}", methods={"GET"})
     */
    public function deleteTreatmentCategoryById(Request $request): Response
    {
        return $this->json($this->treatmentCategoryService->deleteById((int) $request->get("id")));
    }

    /**
     * @Route("/client/create", methods={"POST"})
     */
    public function createClient(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var ClientModel $client */
            $client = $this->appHydrator->convertArrayToObject($data, ClientModel::class);

            return $this->json($this->clientService->create($client));
        } catch (ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse('Creating client failed', null, ['reflection' => $exception->getMessage()]));
        }
    }

    /**
     * @Route("/client/list", methods={"GET"})
     */
    public function getAllClients(): Response
    {
        return $this->json($this->clientService->getAll());
    }

    /**
     * @Route("/client/delete/{id}", methods={"GET"})
     */
    public function deleteClientById(Request $request): Response
    {
        return $this->json($this->clientService->deleteById((int) $request->get("id")));
    }

    /**
     * @Route("/client/update/{id}", methods={"POST"})
     */
    public function updateClientById(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var ClientModel $client */
            $client = $this->appHydrator->convertArrayToObject($data, ClientModel::class);

            return $this->json($this->clientService->updateById((int) $request->get("id"), $client));
        } catch (ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse('Updating client failed', null, ['reflection' => $exception->getMessage()]));
        }
    }

    /**
     * @Route("/client-session/create", methods={"POST"})
     */
    public function createClientSession(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var ClientSessionModel $clientSession */
            $clientSession = $this->appHydrator->convertArrayToObject($data, ClientSessionModel::class);

            return $this->json($this->clientSessionService->create($clientSession));
        } catch (ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse('Creating client session failed', null, ['reflection' => $exception->getMessage()]));
        }
    }

    /**
     * @Route("/client-session/list", methods={"GET"})
     */
    public function getAllClientSessions(): Response
    {
        return $this->json($this->clientSessionService->getAll());
    }

    /**
     * @Route("/client-session/delete/{id}", methods={"GET"})
     */
    public function deleteClientSessionsById(Request $request): Response
    {
        return $this->json($this->clientSessionService->deleteById((int) $request->get("id")));
    }

    /**
     * @Route("/client-session/update/{id}", methods={"POST"})
     */
    public function updateClientSessionById(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var ClientSessionModel $clientSession */
            $clientSession = $this->appHydrator->convertArrayToObject($data, ClientSessionModel::class);

            return $this->json($this->clientSessionService->updateById((int) $request->get("id"), $clientSession));
        } catch (ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse('Updating client session failed', null, ['reflection' => $exception->getMessage()]));
        }
    }
}
