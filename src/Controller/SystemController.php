<?php

namespace App\Controller;

use App\Service\System\AuditTrail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

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

    /**
     * @Route("/download/{page}/{pageSize}/audit_trail.csv", methods={"GET"})
     */
    public function download(Request $request): Response
    {
        $fileName = 'audit_logs' . '-' . time() . '.csv';
        $data = $this->auditTrailService->download(
            (int) $request->get("page"),
            (int) $request->get("pageSize"),
            (string) $request->query->get("column"),
            (string) $request->query->get("value"),
            (string) $request->query->get("jsonColumn"),
        );

        $encoder = [new CsvEncoder()];
        $normalizer = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizer, $encoder);
        $content = $serializer->serialize($data, 'csv');

        $response = new Response($content);
        $response->headers->set('Content-Encoding', 'UTF-8');
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $fileName);

        return $response;

    }
}