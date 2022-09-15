<?php

namespace App\Controller;

use App\Service\System\AuditTrail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/system/audit-trail")
 */
class SystemController extends AbstractController
{
    public function __construct(
       private AuditTrail $auditTrailService,
    ) {
    }

    /**
     * @Route("/{page}/{pageSize}", methods={"GET"})
     */
    public function getPaginated(Request $request): Response
    {
        return $this->json($this->auditTrailService->paginated(
            (int) $request->get("page"),
            (int) $request->get("pageSize"),
            (string) $request->query->get("column"),
            (string) $request->query->get("value"),
            (string) $request->query->get("jsonColumn"),
        ));
    }
}