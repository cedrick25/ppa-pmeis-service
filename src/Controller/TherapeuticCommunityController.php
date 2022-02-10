<?php

declare(strict_types=1);

namespace App\Controller;

use App\Common\AppFormatter;
use App\Common\AppHydrator;
use App\Model\ClientSessions as ClientSessionModel;
use App\Model\Quarters as QuartersModel;
use App\Model\ResourceFacilitatorSession as ResourceFacilitatorSessionModel;
use App\Model\Sessions as SessionsModel;
use App\Service\CivilStatusInterface;
use App\Service\EducationBackgroundInterface;
use App\Service\OccupationInterface;
use App\Service\ReligionInterface;
use App\Service\TherapeuticCommunity\ClientSessionsInterface;
use App\Service\TherapeuticCommunity\ClientTypesInterface;
use App\Service\TherapeuticCommunity\FieldOfficesInterface;
use App\Service\TherapeuticCommunity\GenerateTableInterface;
use App\Service\TherapeuticCommunity\PhasesInterface;
use App\Service\TherapeuticCommunity\QuartersInterface;
use App\Service\TherapeuticCommunity\RegionsInterface;
use App\Service\TherapeuticCommunity\ResourceFacilitatorSessionInterface;
use App\Service\TherapeuticCommunity\SessionActivitiesInterface;
use App\Service\TherapeuticCommunity\SessionRemarksInterface;
use App\Service\TherapeuticCommunity\SessionsInterface;
use App\Service\TherapeuticCommunity\TreatmentCategoriesInterface;
use App\Service\TherapeuticCommunity\VenuesInterface;
use App\Service\TherapeuticCommunity\ClientsInterface;
use App\Service\TherapeuticCommunity\VolunteerInterface;
use Symfony\Component\Routing\Annotation\Route;
use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Model\ClientTypes as ClientTypesModel;
use App\Model\Clients as ClientModel;
use App\Model\Volunteer as VolunteerModel;

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
        private VolunteerInterface $volunteerService,
        private ResourceFacilitatorSessionInterface $resourceFacilitatorSessionService,
        private RegionsInterface $regionService,
        private GenerateTableInterface $generateTable,
        private SessionRemarksInterface $sessionRemarkService,
        private CivilStatusInterface $civilStatusService,
        private EducationBackgroundInterface $educationBackgroundService,
        private OccupationInterface $occupationService,
        private ReligionInterface $religionService,
    ){}

    /**
     * @Route("/", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->json([
            'XLSX_PATH_FILE' => $_ENV['XLSX_PATH_FILE'],
        ]);
    }

    /**
     * @Route("/quarter/create", methods={"POST"})
     */
    public function createQuarter(Request $request): Response
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
     * @Route("/quarter/{page}/{pageSize}", methods={"GET"})
     */
    public function getPaginatedQuarters(Request $request): Response
    {
        return $this->json($this->quartersService->getPaginated(
            (int) $request->get("page"),
            (int) $request->get("pageSize")
        ));
    }

    /**
     * @Route("/quarter/{page}/{pageSize}", methods={"POST"})
     */
    public function searchPaginatedQuarters(Request $request): Response
    {
        $searchData = json_decode($request->getContent(), true);

        return $this->json($this->quartersService->searchPaginated(
            $searchData['field'],
            $searchData['query'],
            (int) $request->get("page"),
            (int) $request->get("pageSize")
        ));
    }

    /**
     * @Route("/quarter/by/id/{id}", methods={"GET"})
     */
    public function getQuarterById(Request $request): Response
    {
        return $this->json($this->quartersService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/quarter/tca1/part1/{id}/{field_office_id}", methods={"GET"})
     */
    public function getTCA1Part1(Request $request): Response
    {
        return $this->json($this->quartersService->getTCA1Part1(
            (int) $request->get("id"),
            (int) $request->get("field_office_id")
        ));
    }

    /**
     * @Route("/quarter/tca1/part2/{id}/{field_office_id}", methods={"GET"})
     */
    public function getTCA1Part2(Request $request): Response
    {
        return $this->json($this->quartersService->getTCA1Part2(
            (int) $request->get("id"),
            (int) $request->get("field_office_id")
        ));
    }

    /**
     * @Route("/quarter/by/year/{year}", methods={"GET"})
     */
    public function getQuarterByYear(Request $request): Response
    {
        return $this->json($this->quartersService->getByYear($request->get("year")));
    }

    /**
     * @Route("/phases/list", methods={"GET"})
     */
    public function getAllPhases(): Response
    {
        return $this->json($this->phasesService->getAll());
    }

    /**
     * @Route("/phases/{page}/{pageSize}", methods={"GET"})
     */
    public function getPaginatedPhases(Request $request): Response
    {
        return $this->json($this->phasesService->getPaginated(
            (int) $request->get("page"),
            (int) $request->get("pageSize")
        ));
    }

    /**
     * @Route("/phases/by/id/{id}", methods={"GET"})
     */
    public function getPhaseById(Request $request): Response
    {
        return $this->json($this->phasesService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/field-office/list", methods={"GET"})
     */
    public function getAllFieldOffices(): Response
    {
        return $this->json($this->fieldOfficesService->getAll());
    }

    /**
     * @Route("/field-office/{page}/{pageSize}", methods={"GET"})
     */
    public function getPaginatedFieldOffices(Request $request): Response
    {
        return $this->json($this->fieldOfficesService->getPaginated(
            (int) $request->get("page"),
            (int) $request->get("pageSize")
        ));
    }

    /**
     * @Route("/field-office/by/region/{regionId}", methods={"GET"})
     */
    public function getFieldOfficesByRegionId(Request $request): Response
    {
        return $this->json($this->fieldOfficesService->getByRegion(
            (int) $request->get("regionId")
        ));
    }

    /**
     * @Route("/field-office/by/id/{id}", methods={"GET"})
     */
    public function getFieldOfficesById(Request $request): Response
    {
        return $this->json($this->fieldOfficesService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/region/list", methods={"GET"})
     */
    public function getAllRegions(): Response
    {
        return $this->json($this->regionService->getAll());
    }

    /**
     * @Route("/region/by/id/{id}", methods={"GET"})
     */
    public function getRegionById(Request $request): Response
    {
        return $this->json($this->regionService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/region/{page}/{pageSize}", methods={"GET"})
     */
    public function getPaginatedRegions(Request $request): Response
    {
        return $this->json($this->regionService->getPaginated(
            (int) $request->get("page"),
            (int) $request->get("pageSize")
        ));
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
    public function createSessionActivity(Request $request): Response
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
     * @Route("/session-activity/update/{id}/{name}", methods={"GET"})
     */
    public function updateSessionActivity(Request $request): Response
    {
        return $this->json($this->sessionActivitiesService->updateById((int) $request->get("id"), $request->get("name")));
    }

    /**
     * @Route("/session-activity/by/id/{id}", methods={"GET"})
     */
    public function getSessionActivityById(Request $request): Response
    {
        return $this->json($this->sessionActivitiesService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/session-activity/{page}/{pageSize}", methods={"GET"})
     */
    public function getPaginatedSessionActivities(Request $request): Response
    {
        return $this->json($this->sessionActivitiesService->getPaginated(
            (int) $request->get("page"),
            (int) $request->get("pageSize")
        ));
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
     * @Route("/venue/update/{id}/{name}", methods={"GET"})
     */
    public function updateVenue(Request $request): Response
    {
        return $this->json($this->venuesService->updateById((int) $request->get("id"), $request->get("name")));
    }

    /**
     * @Route("/venue/by/id/{id}", methods={"GET"})
     */
    public function getVenueById(Request $request): Response
    {
        return $this->json($this->venuesService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/venue/{page}/{pageSize}", methods={"GET"})
     */
    public function getPaginatedVenues(Request $request): Response
    {
        return $this->json($this->venuesService->getPaginated(
            (int) $request->get("page"),
            (int) $request->get("pageSize")
        ));
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
     * @Route("/session/create-with-clients-and-facilitators", methods={"POST"})
     */
    public function createSessionWithClientsAndFacilitators(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var SessionsModel $session */
            $session = $this->appHydrator->convertArrayToObject($data, SessionsModel::class);

            return $this->json($this->sessionService->createWithClientsAndFacilitators($session));
        } catch (ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse('Creating session failed', null, ['reflection' => $exception->getMessage()]));
        }
    }

    /**
     * @Route("/session/list-with-clients-and-facilitators", methods={"GET"})
     */
    public function getAllSessionsWithClientsAndFacilitators(): Response
    {
        return $this->json($this->sessionService->getAllWithClientsAndFacilitators());
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
     * @Route("/session/update-with-client-facilitators/{id}", methods={"POST"})
     */
    public function updateByIdWithClientAndFacilitators(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var SessionsModel $session */
            $session = $this->appHydrator->convertArrayToObject($data, SessionsModel::class);

            return $this->json($this->sessionService->updateByIdWithClientAndFacilitators((int) $request->get("id"), $session));
        } catch (ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse('Updating session failed', null, ['reflection' => $exception->getMessage()]));
        }
    }

    /**
     * @Route("/session/by/id/{id}", methods={"GET"})
     */
    public function getSessionById(Request $request): Response
    {
        return $this->json($this->sessionService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/session/{page}/{pageSize}", methods={"GET"})
     */
    public function getPaginatedSessions(Request $request): Response
    {
        return $this->json($this->sessionService->getPaginated(
            (int) $request->get("page"),
            (int) $request->get("pageSize")
        ));
    }

    /**
     * @Route("/session/tca2-6-8/full/{quarterId}/{role}", methods={"GET"})
     */
    public function getTCIA2(Request $request): Response
    {
        return $this->json($this->sessionService->getTCIA2(
            (int) $request->get("quarterId"),
            (string) $request->get("role")
        ));
    }

    /**
     * @Route("/session-remarks/list", methods={"GET"})
     */
    public function getAllSessionRemarks(): Response
    {
        return $this->json($this->sessionRemarkService->getAll());
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
     * @Route("/client-type/{page}/{pageSize}", methods={"GET"})
     */
    public function getPaginatedClientTypes(Request $request): Response
    {
        return $this->json($this->clientTypeService->getPaginated(
            (int) $request->get("page"),
            (int) $request->get("pageSize")
        ));
    }

    /**
     * @Route("/client-type/by/id/{id}", methods={"GET"})
     */
    public function getClientTypeById(Request $request): Response
    {
        return $this->json($this->clientTypeService->getById((int) $request->get("id")));
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
     * @Route("/treatment-category/update/{id}/{name}", methods={"GET"})
     */
    public function updateTreatmentCategory(Request $request): Response
    {
        return $this->json($this->treatmentCategoryService->updateById((int) $request->get("id"), $request->get("name")));
    }

    /**
     * @Route("/treatment-category/by/id/{id}", methods={"GET"})
     */
    public function getTreatmentCategoryById(Request $request): Response
    {
        return $this->json($this->treatmentCategoryService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/treatment-category/{page}/{pageSize}", methods={"GET"})
     */
    public function getPaginatedTreatmentCategories(Request $request): Response
    {
        return $this->json($this->treatmentCategoryService->getPaginated(
            (int) $request->get("page"),
            (int) $request->get("pageSize")
        ));
    }

    /**
     * @Route("/treatment-category/header", methods={"GET"})
     */
    public function getAllTreatmentCategoriesHeader(): Response
    {
        return $this->json($this->treatmentCategoryService->getHeader());
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
     * @Route("/client/{page}/{pageSize}", methods={"GET"})
     */
    public function getPaginatedClients(Request $request): Response
    {
        return $this->json($this->clientService->getPaginated(
            (int) $request->get("page"),
            (int) $request->get("pageSize")
        ));
    }

    /**
     * @Route("/client/by/id/{id}", methods={"GET"})
     */
    public function getClientById(Request $request): Response
    {
        return $this->json($this->clientService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/client/by/type/{id}", methods={"GET"})
     */
    public function getClientByClientTypeId(Request $request): Response
    {
        return $this->json($this->clientService->getByClientId((int) $request->get("id")));
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

    /**
     * @Route("/client-session/{page}/{pageSize}", methods={"GET"})
     */
    public function getPaginatedClientSessions(Request $request): Response
    {
        return $this->json($this->clientSessionService->getPaginated(
            (int) $request->get("page"),
            (int) $request->get("pageSize")
        ));
    }

    /**
     * @Route("/client-session/by/id/{id}", methods={"GET"})
     */
    public function getClientSessionById(Request $request): Response
    {
        return $this->json($this->clientSessionService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/client-session/paginated/search/{query}", methods={"GET"})
     */
    public function getClientSessionBySearch(Request $request): Response
    {
        $direction = $request->query->get("direction");
        dd($request->get("query"));
//        return $this->json($this->clientSessionService->getById($request->get("query")));
    }

    /**
     * @Route("/volunteer/create", methods={"POST"})
     */
    public function createVolunteer(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var VolunteerModel $volunteer */
            $volunteer = $this->appHydrator->convertArrayToObject($data, VolunteerModel::class);

            return $this->json($this->volunteerService->create($volunteer));
        } catch (ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse('Creating volunteer failed', null, ['reflection' => $exception->getMessage()]));
        }
    }

    /**
     * @Route("/volunteer/list", methods={"GET"})
     */
    public function getAllVolunteers(): Response
    {
        return $this->json($this->volunteerService->getAll());
    }

    /**
     * @Route("/volunteer/delete/{id}", methods={"GET"})
     */
    public function deleteVolunteerById(Request $request): Response
    {
        return $this->json($this->volunteerService->deleteById((int) $request->get("id")));
    }

    /**
     * @Route("/volunteer/update/{id}", methods={"POST"})
     */
    public function updateVolunteerById(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var VolunteerModel $volunteer */
            $volunteer = $this->appHydrator->convertArrayToObject($data, VolunteerModel::class);

            return $this->json($this->volunteerService->updateById((int) $request->get("id"), $volunteer));
        } catch (ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse('Updating volunteer session failed', null, ['reflection' => $exception->getMessage()]));
        }
    }

    /**
     * @Route("/volunteer/by/id/{id}", methods={"GET"})
     */
    public function getVolunteerById(Request $request): Response
    {
        return $this->json($this->volunteerService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/volunteer/{page}/{pageSize}", methods={"GET"})
     */
    public function getPaginatedVolunteers(Request $request): Response
    {
        return $this->json($this->volunteerService->getPaginated(
            (int) $request->get("page"),
            (int) $request->get("pageSize")
        ));
    }

    /**
     * @Route("/volunteer/by/{quarterId}/{fieldOfficeId}", methods={"GET"})
     */
    public function getByFieldOfficeAndMonthRange(Request $request): Response
    {
        return $this->json($this->volunteerService->getByFieldOfficeAndMonthRange(
            (int) $request->get("quarterId"),
            (int) $request->get("fieldOfficeId")
        ));
    }

    /**
     * @Route("/volunteer/update-volunteer-status", methods={"POST"})
     */
    public function updateVolunteerStatus(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            return $this->json($this->volunteerService->updateVolunteerStatus($data));
        } catch (ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse('Updating volunteer session failed', null, ['reflection' => $exception->getMessage()]));
        }
    }

    /**
     * @Route("/volunteer/socio-demographic/by/quarter-id/{quarterId}", methods={"GET"})
     */
    public function getConsolidatedSocioDemographic(Request $request): Response
    {
        return $this->json($this->volunteerService->getConsolidatedSocioDemographic((int) $request->get("quarterId")));
    }

    /**
     * @Route("/applicants/list", methods={"GET"})
     */
    public function getAllApplicants(): Response
    {
        return $this->json($this->volunteerService->getApplicants());
    }

    /**
     * @Route("/volunteer/database/{quarterId}/{fieldOfficeId}", methods={"GET"})
     */
    public function getVPADatabase(Request $request): Response
    {
        return $this->json($this->volunteerService->getVPADatabase(
            (int) $request->get("fieldOfficeId"),
            (int) $request->get("quarterId")
        ));
    }

    /**
     * @Route("/volunteer/monitoring/{quarterId}/{fieldOfficeId}", methods={"GET"})
     */
    public function getVpaMonitoring(Request $request): Response
    {
        return $this->json($this->volunteerService->getVpaMonitoring(
            (int) $request->get("fieldOfficeId"),
            (int) $request->get("quarterId")
        ));
    }

    /**
     * @Route("/resource-facilitator-session/create", methods={"POST"})
     */
    public function createResourceFacilitatorSession(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var ResourceFacilitatorSessionModel $resourceFacilitatorSession */
            $resourceFacilitatorSession = $this->appHydrator->convertArrayToObject($data, ResourceFacilitatorSessionModel::class);

            return $this->json($this->resourceFacilitatorSessionService->create($resourceFacilitatorSession));
        } catch (ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse('Creating resource facilitator session failed', null, ['reflection' => $exception->getMessage()]));
        }
    }

    /**
     * @Route("/resource-facilitator-session/list", methods={"GET"})
     */
    public function getAllResourceFacilitatorSessions(): Response
    {
        return $this->json($this->resourceFacilitatorSessionService->getAll());
    }

    /**
     * @Route("/resource-facilitator-session/delete/{id}", methods={"GET"})
     */
    public function deleteResourceFacilitatorSessionById(Request $request): Response
    {
        return $this->json($this->resourceFacilitatorSessionService->deleteById((int) $request->get("id")));
    }

    /**
     * @Route("/resource-facilitator-session/update/{id}", methods={"POST"})
     */
    public function updateResourceFacilitatorSessionById(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var ResourceFacilitatorSessionModel $resourceFacilitatorSession */
            $resourceFacilitatorSession = $this->appHydrator->convertArrayToObject($data, ResourceFacilitatorSessionModel::class);

            return $this->json($this->resourceFacilitatorSessionService->updateById((int) $request->get("id"), $resourceFacilitatorSession));
        } catch (ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse('Updating resource facilitator session failed', null, ['reflection' => $exception->getMessage()]));
        }
    }

    /**
     * @Route("/resource-facilitator-session/by/id/{id}", methods={"GET"})
     */
    public function getResourceFacilitatorSessionById(Request $request): Response
    {
        return $this->json($this->resourceFacilitatorSessionService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/resource-facilitator-session/{page}/{pageSize}", methods={"GET"})
     */
    public function getPaginatedResourceFacilitatorSession(Request $request): Response
    {
        return $this->json($this->resourceFacilitatorSessionService->getPaginated(
            (int) $request->get("page"),
            (int) $request->get("pageSize")
        ));
    }

    /**
     * @Route("/resource-facilitator-session/vpa3/full/{fieldOfficeId}/{quarterId}", methods={"GET"})
     */
    public function getVP3(Request $request): Response
    {
        return $this->json($this->resourceFacilitatorSessionService->getVPA3(
            (int) $request->get("fieldOfficeId"),
            (int) $request->get("quarterId"),
        ));
    }

    /**
     * @Route("/generate-table", methods={"POST"})
     */
    public function generate(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        return $this->json($this->generateTable->generate($data));
    }

    /**
     * @Route("/civil-status/list", methods={"GET"})
     */
    public function getAllCivilStatuses(): Response
    {
        return $this->json($this->civilStatusService->getAll());
    }

    /**
     * @Route("/educational-background/list", methods={"GET"})
     */
    public function getAllEducationBackgrounds(): Response
    {
        return $this->json($this->educationBackgroundService->getAll());
    }

    /**
     * @Route("/occupation/list", methods={"GET"})
     */
    public function getAllOccupations(): Response
    {
        return $this->json($this->occupationService->getAll());
    }

    /**
     * @Route("/religion/list", methods={"GET"})
     */
    public function getAllReligions(): Response
    {
        return $this->json($this->religionService->getAll());
    }
}
