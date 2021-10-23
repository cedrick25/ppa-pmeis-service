<?php

namespace App\Controller;

use App\Common\AppFormatter;
use App\Common\AppHydrator;
use App\Model\Quarters as QuartersModel;
use App\Service\TherapeuticCommunityService;
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
        private TherapeuticCommunityService $service,
        private AppHydrator $appHydrator,
        private AppFormatter $appFormatter,
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

            return $this->json($this->service->createQuarters($quarter));
        } catch (ReflectionException $exception) {
            return $this->json($this->appFormatter->formatResponse('Quarter creation failed', null, ['reflection' => $exception->getMessage()]));
        }
    }

    /**
     * @Route("/quarter/list", methods={"GET"})
     */
    public function getAllQuarters(): Response
    {
        return $this->json($this->service->getAllQuarters());
    }
}
