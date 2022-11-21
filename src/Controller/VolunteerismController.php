<?php

declare(strict_types=1);

namespace App\Controller;

use App\Common\AppFormatter;
use App\Common\AppHydrator;
use App\Model\VolunteerOperations as VolunteerOperationsModel;
use App\Model\IdSupport as IdSupportModel;
use App\Model\ResourceMobilization as ResourceMobilizationModel;
use App\Model\VolunteerId as VolunteerIdModel;
use App\Model\ProgramMaterialsDevelopment as ProgramMaterialsDevelopmentModel;
use App\Model\JailDecongestion as JailDecongestionModel;
use App\Model\SpecialAssignment as SpecialAssignmentModel;
use App\Service\Volunteerism\CapabilityBuildingInterface;
use App\Service\Volunteerism\IdInterface;
use App\Service\Volunteerism\IdSupportInterface;
use App\Service\Volunteerism\JailDecongestionInterface;
use App\Service\Volunteerism\OperationsInterface;
use App\Service\Volunteerism\ProgramMaterialsDevelopmentInterface;
use App\Service\Volunteerism\ResourceMobilizationInterface;
use App\Service\Volunteerism\ServicesRenderedInterface;
use App\Model\TechnicalAssistance as TechnicalAssistanceModel;
use App\Service\Volunteerism\SocialMarketingActivitiesInterface;
use App\Service\Volunteerism\SocialMarketingInterface;
use App\Service\Volunteerism\SpecialAssignmentInterface;
use App\Service\Volunteerism\SupportOfRegionToFieldOfficeInterface;
use App\Service\Volunteerism\TechnicalAssistanceInterface;
use App\Model\SocialMarketing as SocialMarketingModel;
use App\Model\SupportOfRegionToFieldOffice as SupportOfRegionToFieldOfficeModel;
use App\Model\VolunteerSupervisions as VolunteerSupervisionsModel;
use App\Model\VpaAssociationInitiatedActivities as VpaAssociationInitiatedActivitiesModel;
use App\Service\Volunteerism\VolunteerSupervisionsInterface;
use App\Service\Volunteerism\VpaAssociationInitiatedActivitiesInterface;
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
        private AppHydrator                                $appHydrator,
        private AppFormatter                               $appFormatter,
        private OperationsInterface                        $operationService,
        private IdInterface                                $idService,
        private ServicesRenderedInterface                  $servicesRenderedService,
        private IdSupportInterface                         $idSupportService,
        private TechnicalAssistanceInterface               $technicalAssistanceService,
        private SocialMarketingActivitiesInterface         $socialMarketingActivitiesService,
        private SocialMarketingInterface                   $socialMarketingService,
        private ProgramMaterialsDevelopmentInterface       $programMaterialsDevelopmentService,
        private ResourceMobilizationInterface              $resourceMobilizationService,
        private JailDecongestionInterface                  $jailDecongestionService,
        private SpecialAssignmentInterface                 $specialAssignmentService,
        private SupportOfRegionToFieldOfficeInterface      $supportOfRegionToFieldOfficeService,
        private VolunteerSupervisionsInterface             $volunteerSupervisionsService,
        private CapabilityBuildingInterface                $capabilityBuildingService,
        private VpaAssociationInitiatedActivitiesInterface $vpaAssociationInitiatedActivities
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
            (int) $request->get("quarterId"),
            (int) $request->get("fieldOfficeId")
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
            return $this->json($this->appFormatter->formatResponse(
                'Creating ID Support failed',
                null,
                ['reflection' => $exception->getMessage()]
            ));
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
     * @Route("/id-support/update/{id}", methods={"POST"})
     */
    public function updateIdSupport(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var IdSupportModel $idSupport */
            $idSupport = $this->appHydrator->convertArrayToObject($data, IdSupportModel::class);

            return $this->json($this->idSupportService->update(
                (int) $request->get("id"),
                $idSupport,
            ));
        } catch (\ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse(
                'Updating ID Support failed',
                null,
                ['reflection' => $exception->getMessage()]
            ));
        }
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
     * @Route("/technical-assistance/{page}/{pageSize}", methods={"GET"})
     */
    public function getPaginatedTechnicalAssistance(Request $request): Response
    {
        return $this->json($this->technicalAssistanceService->getPaginated(
            (int) $request->get("page"),
            (int) $request->get("pageSize")
        ));
    }

    /**
     * @Route("/technical-assistance/by/id/{id}", methods={"GET"})
     */
    public function getTechnicalAssistanceById(Request $request): Response
    {
        return $this->json($this->technicalAssistanceService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/technical-assistance/delete/by/id/{id}", methods={"GET"})
     */
    public function deleteTechnicalAssistanceById(Request $request): Response
    {
        return $this->json($this->technicalAssistanceService->deleteById((int) $request->get("id")));
    }

    /**
     * @Route("/technical-assistance/report/full/{quarterId}/{fieldOfficeId}", methods={"GET"})
     */
    public function getTechnicalAssistanceReport(Request $request): Response
    {
        return $this->json($this->technicalAssistanceService->getReport(
            (int) $request->get("quarterId"),
            (int) $request->get("fieldOfficeId"),
        ));
    }

    /**
     * @Route("/technical-assistance/update/{id}", methods={"POST"})
     */
    public function updateTechnicalAssistance(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var TechnicalAssistanceModel $technicalAssistance */
            $technicalAssistance = $this->appHydrator->convertArrayToObject($data, TechnicalAssistanceModel::class);

            return $this->json($this->technicalAssistanceService->update(
                (int) $request->get("id"),
                $technicalAssistance,
            ));
        } catch (\ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse(
                'Updating Technical Assistance failed',
                null,
                ['reflection' => $exception->getMessage()]
            ));
        }
    }

    /**
     * @Route("/social-marketing-activities/list", methods={"GET"})
     */
    public function getAllSocialMarketingActivities(): Response
    {
        return $this->json($this->socialMarketingActivitiesService->getAll());
    }

    /**
     * @Route("/social-marketing/create", methods={"POST"})
     */
    public function createSocialMarketing(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var SocialMarketingModel $socialMarketing */
            $socialMarketing = $this->appHydrator->convertArrayToObject($data, SocialMarketingModel::class);

            return $this->json($this->socialMarketingService->create($socialMarketing));
        } catch (\ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse(
                'Creating Social Marketing failed',
                null,
                ['reflection' => $exception->getMessage()]
            ));
        }
    }

    /**
     * @Route("/social-marketing/update/{id}", methods={"POST"})
     */
    public function updateSocialMarketing(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var SocialMarketingModel $socialMarketing */
            $socialMarketing = $this->appHydrator->convertArrayToObject($data, SocialMarketingModel::class);

            return $this->json($this->socialMarketingService->update(
                (int) $request->get('id'),
                $socialMarketing
            ));
        } catch (\ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse(
                'Updating Social Marketing failed',
                null,
                ['reflection' => $exception->getMessage()]
            ));
        }
    }

    /**
     * @Route("/social-marketing/list", methods={"GET"})
     */
    public function getAllSocialMarketing(): Response
    {
        return $this->json($this->socialMarketingService->getAll());
    }

    /**
     * @Route("/social-marketing/{page}/{pageSize}", methods={"GET"})
     */
    public function getPaginatedSocialMarketing(Request $request): Response
    {
        return $this->json($this->socialMarketingService->getPaginated(
            $request->query->get('type'),
            (int) $request->get("page"),
            (int) $request->get("pageSize")
        ));
    }

    /**
     * @Route("/social-marketing/by/id/{id}", methods={"GET"})
     */
    public function getSocialMarketingById(Request $request): Response
    {
        return $this->json($this->socialMarketingService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/social-marketing/delete/by/id/{id}", methods={"GET"})
     */
    public function deleteSocialMarketingById(Request $request): Response
    {
        return $this->json($this->socialMarketingService->deleteById((int) $request->get("id")));
    }

    /**
     * @Route("/social-marketing/report/full/{quarterId}/{fieldOfficeId}/{type}", methods={"GET"})
     */
    public function getSocialMarketingReport(Request $request): Response
    {
        return $this->json($this->socialMarketingService->getReport(
            (int) $request->get("quarterId"),
            (int) $request->get("fieldOfficeId"),
            $request->get("type"),
        ));
    }

    /**
     * @Route("/program-materials-development/create", methods={"POST"})
     */
    public function createProgramMaterialsDevelopment(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var ProgramMaterialsDevelopmentModel $programMaterialsDevelopmentModel */
            $programMaterialsDevelopmentModel = $this->appHydrator->convertArrayToObject(
                $data,
                ProgramMaterialsDevelopmentModel::class
            );

            return $this->json($this->programMaterialsDevelopmentService->create($programMaterialsDevelopmentModel));
        } catch (\ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse(
                'Creating Program Materials Development failed',
                null,
                ['reflection' => $exception->getMessage()]
            ));
        }
    }

    /**
     * @Route("/program-materials-development/list", methods={"GET"})
     */
    public function getAllProgramMaterialsDevelopment(): Response
    {
        return $this->json($this->programMaterialsDevelopmentService->getAll());
    }

    /**
     * @Route("/program-materials-development/{page}/{pageSize}", methods={"GET"})
     */
    public function getPaginatedPmd(Request $request): Response
    {
        return $this->json($this->programMaterialsDevelopmentService->getPaginated(
            (int) $request->get("page"),
            (int) $request->get("pageSize")
        ));
    }

    /**
     * @Route("/program-materials-development/by/id/{id}", methods={"GET"})
     */
    public function getIdProgramMaterialsDevelopmentById(Request $request): Response
    {
        return $this->json($this->programMaterialsDevelopmentService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/program-materials-development/delete/{id}", methods={"GET"})
     */
    public function deleteProgramMaterialsDevelopmentById(Request $request): Response
    {
        return $this->json($this->programMaterialsDevelopmentService->deleteById((int) $request->get("id")));
    }

    /**
     * @Route("/program-materials-development/report/full/{quarterId}/{fieldOfficeId}", methods={"GET"})
     */
    public function getProgramMaterialsDevelopmentReport(Request $request): Response
    {
        return $this->json($this->programMaterialsDevelopmentService->getIdSupportReport(
            (int) $request->get("quarterId"),
            (int) $request->get("fieldOfficeId"),
        ));
    }
    /**
     * @Route("/program-materials-development/update/{id}", methods={"POST"})
     */
    public function updateProgramMaterialsDevelopment(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var ProgramMaterialsDevelopmentModel $programMaterialsDevelopmentModel */
            $programMaterialsDevelopmentModel = $this->appHydrator->convertArrayToObject(
                $data,
                ProgramMaterialsDevelopmentModel::class
            );

            return $this->json($this->programMaterialsDevelopmentService->update(
                (int) $request->get("id"),
                $programMaterialsDevelopmentModel,
            ));
        } catch (\ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse(
                'Updating Program Materials Development failed',
                null,
                ['reflection' => $exception->getMessage()]
            ));
        }
    }

    /**
     * @Route("/resource-mobilization/create", methods={"POST"})
     */
    public function createResourceMobilization(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var ResourceMobilizationModel $resourceMobilization */
            $resourceMobilization = $this->appHydrator->convertArrayToObject($data, ResourceMobilizationModel::class);

            return $this->json($this->resourceMobilizationService->create($resourceMobilization));
        } catch (\ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse(
                'Updating Resource Mobilization failed',
                null,
                ['reflection' => $exception->getMessage()]
            ));
        }
    }

    /**
     * @Route("/resource-mobilization/update/{id}", methods={"POST"})
     */
    public function updateResourceMobilization(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var ResourceMobilizationModel $resourceMobilization */
            $resourceMobilization = $this->appHydrator->convertArrayToObject($data, ResourceMobilizationModel::class);

            return $this->json($this->resourceMobilizationService->update(
                (int) $request->get('id'),
                $resourceMobilization,
            ));
        } catch (\ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse(
                'Updating Resource Mobilization failed',
                null,
                ['reflection' => $exception->getMessage()]
            ));
        }
    }

    /**
     * @Route("/resource-mobilization/list", methods={"GET"})
     */
    public function getAllResourceMobilizations(): Response
    {
        return $this->json($this->resourceMobilizationService->getAll());
    }

    /**
     * @Route("/resource-mobilization/{page}/{pageSize}", methods={"GET"})
     */
    public function getPaginatedResMob(Request $request): Response
    {

        return $this->json($this->resourceMobilizationService->getPaginated(
            (int) $request->get("page"),
            (int) $request->get("pageSize")
        ));
    }

    /**
     * @Route("/resource-mobilization/by/id/{id}", methods={"GET"})
     */
    public function getResourceMobilizationById(Request $request): Response
    {
        return $this->json($this->resourceMobilizationService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/resource-mobilization/delete/by/id/{id}", methods={"GET"})
     */
    public function deleteResourceMobilizationById(Request $request): Response
    {
        return $this->json($this->resourceMobilizationService->deleteById((int) $request->get("id")));
    }

    /**
     * @Route("/resource-mobilization/report/full/{quarterId}/{fieldOfficeId}", methods={"GET"})
     */
    public function getResourceMobilizationReport(Request $request): Response
    {
        return $this->json($this->resourceMobilizationService->getReport(
            (int) $request->get("quarterId"),
            (int) $request->get("fieldOfficeId"),
        ));
    }

    /**
     * @Route("/jail-decogenstion/create", methods={"POST"})
     */
    public function createJailDecongestion(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var JailDecongestionModel $jailDecongestion */
            $jailDecongestion = $this->appHydrator->convertArrayToObject($data, JailDecongestionModel::class);

            return $this->json($this->jailDecongestionService->create($jailDecongestion));
        } catch (\ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse('Creating Jail Decongestion failed', null, ['reflection' => $exception->getMessage()]));
        }
    }

    /**
     * @Route("/jail-decogenstion/list", methods={"GET"})
     */
    public function getAllJailDecongestions(): Response
    {
        return $this->json($this->jailDecongestionService->getAll());
    }

    /**
     * @Route("/jail-decogenstion/by/id/{id}", methods={"GET"})
     */
    public function getJailDecongestionById(Request $request): Response
    {
        return $this->json($this->jailDecongestionService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/jail-decogenstion/delete/{id}", methods={"GET"})
     */
    public function deleteJailDecongestionById(Request $request): Response
    {
        return $this->json($this->jailDecongestionService->deleteById((int) $request->get("id")));
    }

    /**
     * @Route("/jail-decogenstion/report/full/{quarterId}/{fieldOfficeId}", methods={"GET"})
     */
    public function getJailDecongestionReport(Request $request): Response
    {
        return $this->json($this->jailDecongestionService->getReport(
            (int) $request->get("quarterId"),
            (int) $request->get("fieldOfficeId"),
        ));
    }

    /**
     * @Route("/special-assignment/create", methods={"POST"})
     */
    public function createSpecialAssignment(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var SpecialAssignmentModel $specialAssignment */
            $specialAssignment = $this->appHydrator->convertArrayToObject($data, SpecialAssignmentModel::class);

            return $this->json($this->specialAssignmentService->create($specialAssignment));
        } catch (\ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse('Creating Special Assignment failed', null, ['reflection' => $exception->getMessage()]));
        }
    }

    /**
     * @Route("/special-assignment/list", methods={"GET"})
     */
    public function getAllSpecialAssignments(): Response
    {
        return $this->json($this->specialAssignmentService->getAll());
    }

    /**
     * @Route("/special-assignment/by/id/{id}", methods={"GET"})
     */
    public function getSpecialAssignmentById(Request $request): Response
    {
        return $this->json($this->specialAssignmentService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/special-assignment/delete/{id}", methods={"GET"})
     */
    public function deleteSpecialAssignmentById(Request $request): Response
    {
        return $this->json($this->specialAssignmentService->deleteById((int) $request->get("id")));
    }

    /**
     * @Route("/special-assignment/report/full/{quarterId}/{fieldOfficeId}", methods={"GET"})
     */
    public function getSpecialAssignmentReport(Request $request): Response
    {
        return $this->json($this->specialAssignmentService->getReport(
            (int) $request->get("quarterId"),
            (int) $request->get("fieldOfficeId"),
        ));
    }

    /**
     * @Route("/support-of-region/create", methods={"POST"})
     */
    public function createSupportOfRegion(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var SupportOfRegionToFieldOfficeModel $supportOfRegionToFieldOffice */
            $supportOfRegionToFieldOffice = $this->appHydrator
                ->convertArrayToObject($data, SupportOfRegionToFieldOfficeModel::class);

            return $this->json($this->supportOfRegionToFieldOfficeService->create($supportOfRegionToFieldOffice));
        } catch (\ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse(
                'Creating Support Of Region To Field Office Support failed',
                null,
                ['reflection' => $exception->getMessage()]
            ));
        }
    }

    /**
     * @Route("/support-of-region/list", methods={"GET"})
     */
    public function getAllSupportOfRegion(): Response
    {
        return $this->json($this->supportOfRegionToFieldOfficeService->getAll());
    }

    /**
     * @Route("/support-of-region/{page}/{pageSize}", methods={"GET"})
     */
    public function getPaginatedSupportOfRegion(Request $request): Response
    {
        return $this->json($this->supportOfRegionToFieldOfficeService->getPaginated(
            (int) $request->get("page"),
            (int) $request->get("pageSize")
        ));
    }

    /**
     * @Route("/support-of-region/by/id/{id}", methods={"GET"})
     */
    public function getSupportOfRegionById(Request $request): Response
    {
        return $this->json($this->supportOfRegionToFieldOfficeService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/support-of-region/delete/{id}", methods={"GET"})
     */
    public function deleteSupportOfRegionById(Request $request): Response
    {
        return $this->json($this->supportOfRegionToFieldOfficeService->deleteById((int) $request->get("id")));
    }

    /**
     * @Route("/support-of-region/report/full/{quarterId}/{regionId}/{category}", methods={"GET"})
     */
    public function getSupportOfRegionReport(Request $request): Response
    {
        return $this->json($this->supportOfRegionToFieldOfficeService->getReport(
            (int) $request->get("quarterId"),
            (int) $request->get("regionId"),
            $request->get("category"),
        ));
    }

    /**
     * @Route("/support-of-region/update/{id}", methods={"POST"})
     */
    public function updateSupportOfRegion(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var SupportOfRegionToFieldOfficeModel $supportOfRegionToFieldOffice */
            $supportOfRegionToFieldOffice = $this->appHydrator
                ->convertArrayToObject($data, SupportOfRegionToFieldOfficeModel::class);

            return $this->json($this->supportOfRegionToFieldOfficeService->update(
                (int) $request->get("id"),
                $supportOfRegionToFieldOffice,
            ));
        } catch (\ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse(
                'Creating Support Of Region To Field Office Support failed',
                null,
                ['reflection' => $exception->getMessage()]
            ));
        }
    }

    /**
     * @Route("/volunteer-supervision/create", methods={"POST"})
     */
    public function createVolunteerSupervision(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var VolunteerSupervisionsModel $volunteerSupervision */
            $volunteerSupervision = $this->appHydrator->convertArrayToObject($data, VolunteerSupervisionsModel::class);

            return $this->json($this->volunteerSupervisionsService->create($volunteerSupervision));
        } catch (\ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse('Creating Volunteer Supervision failed', null, ['reflection' => $exception->getMessage()]));
        }
    }

    /**
     * @Route("/volunteer-supervision/list", methods={"GET"})
     */
    public function getAllVolunteerSupervisions(): Response
    {
        return $this->json($this->volunteerSupervisionsService->getAll());
    }

    /**
     * @Route("/volunteer-supervision/by/id/{id}", methods={"GET"})
     */
    public function getVolunteerSupervisionById(Request $request): Response
    {
        return $this->json($this->volunteerSupervisionsService->getById((int) $request->get("id")));
    }

    /**
     * @Route("/volunteer-supervision/delete/{id}", methods={"GET"})
     */
    public function deleteVolunteerSupervisionById(Request $request): Response
    {
        return $this->json($this->volunteerSupervisionsService->deleteById((int) $request->get("id")));
    }

    /**
     * @Route("/volunteer-supervision/report/full/{quarterId}/{fieldOfficeId}", methods={"GET"})
     */
    public function getVolunteerSupervisionReport(Request $request): Response
    {
        return $this->json($this->volunteerSupervisionsService->getReport(
            (int) $request->get("quarterId"),
            (int) $request->get("fieldOfficeId")
        ));
    }

    /**
     * @Route("/volunteer-supervision/{page}/{pageSize}", methods={"GET"})
     */
    public function getPaginatedVolunteerSupervision(Request $request): Response
    {
        return $this->json($this->volunteerSupervisionsService->getPaginated(
            (int) $request->get("page"),
            (int) $request->get("pageSize")
        ));
    }

    /**
     * @Route("/volunteer-supervision/update/{id}", methods={"POST"})
     */
    public function updateVolunteerSupervision(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var VolunteerSupervisionsModel $volunteerSupervision */
            $volunteerSupervision = $this->appHydrator->convertArrayToObject($data, VolunteerSupervisionsModel::class);

            return $this->json($this->volunteerSupervisionsService->update(
                (int) $request->get("id"),
                $volunteerSupervision,
            ));
        } catch (\ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse(
                'Updating Volunteer Supervision failed',
                null,
                ['reflection' => $exception->getMessage()])
            );
        }
    }

    /**
     * @Route("/capability-building/create", methods={"POST"})
     */
    public function createCapabilityBuilding(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            return $this->json($this->capabilityBuildingService->create($data));
        } catch (\ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse('Creating capability building failed', null, ['reflection' => $exception->getMessage()]));
        }
    }

    /**
     * @Route("/capability-building/list", methods={"GET"})
     */
    public function getAllCapabilityBuildings(): Response
    {
        return $this->json($this->capabilityBuildingService->getAll());
    }

    /**
     * @Route("/capability-building/report/full/{quarterId}/{fieldOfficeId}/{type}", methods={"GET"})
     */
    public function getCapabilityBuildingReport(Request $request): Response
    {
        return $this->json($this->capabilityBuildingService->getReport(
            (int) $request->get("quarterId"),
            (int) $request->get("fieldOfficeId"),
            $request->get("type")
        ));
    }

    /**
     * @Route("/capability-building/delete/{id}", methods={"GET"})
     */
    public function deleteCapabilityBuildingById(Request $request): Response
    {
        return $this->json($this->capabilityBuildingService->deleteById((int) $request->get("id")));
    }

    /**
     * @Route("/capability-building/{page}/{pageSize}", methods={"GET"})
     */
    public function getPaginatedCapabilityBuilding(Request $request): Response
    {
        return $this->json($this->capabilityBuildingService->getPaginated(
            (int) $request->get("page"),
            (int) $request->get("pageSize")
        ));
    }

    /**
     * @Route("/capability-building/update/{id}", methods={"POST"})
     */
    public function updateCapabilityBuilding(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            return $this->json($this->capabilityBuildingService->update(
                (int) $request->get("id"),
                $data
            ));
        } catch (\ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse(
                'Updating vpa association initiated activities failed',
                null,
                ['reflection' => $exception->getMessage()]
            ));
        }
    }

    /**
     * @Route("/capability-building/by/id/{id}", methods={"GET"})
     */
    public function getCapabilityBuildingById(Request $request): Response
    {
        return $this->json($this->capabilityBuildingService->getById(
            (int) $request->get("id"),
        ));
    }

    /**
     * @Route("/vpa-association-initiated-ctivity/create", methods={"POST"})
     */
    public function createVpaAssociationInitiatedActivity(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var VpaAssociationInitiatedActivitiesModel $vpaAssociationInitiatedActivity */
            $vpaAssociationInitiatedActivity = $this->appHydrator->convertArrayToObject(
                $data,
                VpaAssociationInitiatedActivitiesModel::class
            );

            return $this->json($this->vpaAssociationInitiatedActivities->create($vpaAssociationInitiatedActivity));
        } catch (\ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse(
                'Creating vpa association initiated activities failed',
                null,
                ['reflection' => $exception->getMessage()])
            );
        }
    }

    /**
     * @Route("/vpa-association-initiated-ctivity/list", methods={"GET"})
     */
    public function vpaAssociationInitiatedActivities(): Response
    {
        return $this->json($this->vpaAssociationInitiatedActivities->getAll());
    }

    /**
     * @Route("/vpa-association-initiated-ctivity/delete/{id}", methods={"GET"})
     */
    public function deleteVpaAssociationInitiatedActivityById(Request $request): Response
    {
        return $this->json($this->vpaAssociationInitiatedActivities->deleteById((int) $request->get("id")));
    }

    /**
     * @Route("/vpa-association-initiated-ctivity/report/full/{quarterId}/{fieldOfficeId}", methods={"GET"})
     */
    public function getVpaAssociationInitiatedActivity(Request $request): Response
    {
        return $this->json($this->vpaAssociationInitiatedActivities->getReport(
            (int) $request->get("quarterId"),
            (int) $request->get("fieldOfficeId")
        ));
    }

    /**
     * @Route("/vpa-association-initiated-ctivity/{page}/{pageSize}", methods={"GET"})
     */
    public function getPaginatedVolunteerAssociation(Request $request): Response
    {
        return $this->json($this->vpaAssociationInitiatedActivities->getPaginated(
            (int) $request->get("page"),
            (int) $request->get("pageSize")
        ));
    }

    /**
     * @Route("/vpa-association-initiated-ctivity/by/id/{id}", methods={"GET"})
     */
    public function getVpaAssociationById(Request $request): Response
    {
        return $this->json($this->vpaAssociationInitiatedActivities->getById((int) $request->get("id")));
    }

    /**
     * @Route("/vpa-association-initiated-ctivity/update/{id}", methods={"POST"})
     */
    public function updateVpaAssociationInitiatedActivity(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            /** @var VpaAssociationInitiatedActivitiesModel $vpaAssociationInitiatedActivity */
            $vpaAssociationInitiatedActivity = $this->appHydrator->convertArrayToObject(
                $data,
                VpaAssociationInitiatedActivitiesModel::class
            );

            return $this->json($this->vpaAssociationInitiatedActivities->update(
                (int) $request->get("id"),
                $vpaAssociationInitiatedActivity
            ));
        } catch (\ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse(
                'Updating vpa association initiated activities failed',
                null,
                ['reflection' => $exception->getMessage()]
            ));
        }
    }
}