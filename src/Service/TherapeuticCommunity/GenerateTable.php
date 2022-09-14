<?php

namespace App\Service\TherapeuticCommunity;

use App\Common\AppFormatter;
use App\Enum\AuditTrailActions;
use App\Enum\Response as ResponseEnum;
use App\Report\Report;
use App\Service\System\AuditTrail;

class GenerateTable implements GenerateTableInterface
{
    private string $shortName;

    public function __construct(
        private AppFormatter    $appFormatter,
        private Report          $report,
        private AuditTrail      $auditTrail,
    ){
        $class = new \ReflectionClass($this);
        $this->shortName = $class->getShortName();
    }

    /**
     * @param array<string, mixed> $data
     * @return array
     */
    public function generate($data): array
    {
        try {
            $fileData = $this->report->create($data);

            $this->auditTrail->log(
                AuditTrailActions::GENERATE_REPORT,
                $data,
                $this->shortName,
            );

            // TODO: remove generated file
            return $this->appFormatter->formatResponse(ResponseEnum::GENERATING_SUCCESS, $fileData);
        } catch (\Exception $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::GENERATING_FAILED, null, ['app' => $exception]);
        }
    }
}