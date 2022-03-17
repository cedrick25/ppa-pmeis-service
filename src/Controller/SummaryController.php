<?php

namespace App\Controller;

use App\Service\Summary\FieldOffice\TherapeuticCommunity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/summary/field-office")
 */
class SummaryController extends AbstractController
{
    public function __construct(
      private TherapeuticCommunity $therapeuticCommunityService,
    ){}

    /**
     * @Route("/tc1/{quarterId}/{field_office_id}", methods={"GET"})
     */
    public function getTC1(Request $request): Response
    {
        return $this->json($this->therapeuticCommunityService->getTC1(
            (int) $request->get("quarterId"),
            (int) $request->get("field_office_id")
        ));
    }
}