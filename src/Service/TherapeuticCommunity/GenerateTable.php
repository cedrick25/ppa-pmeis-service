<?php

namespace App\Service\TherapeuticCommunity;

use App\Common\AppFormatter;
use App\Enum\Response as ResponseEnum;
use App\Report\Report;

class GenerateTable implements GenerateTableInterface
{
    public function __construct(
        private AppFormatter    $appFormatter,
        private Report          $report
    ){}

    public function generate(): array
    {
        try {
            $filePath = $this->report->create('TCIA1');

            return $this->appFormatter->formatResponse(ResponseEnum::GENERATING_SUCCESS, $filePath);
        } catch (\Exception $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::GENERATING_FAILED, null, ['app' => $exception->getMessage()]);
        }
    }
}