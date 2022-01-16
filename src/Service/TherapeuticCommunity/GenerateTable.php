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

    /**
     * @param array<string, mixed> $data
     * @return array
     */
    public function generate($data): array
    {
        try {
            $fileData = $this->report->create($data);

            // TODO: remove generated file
            return $this->appFormatter->formatResponse(ResponseEnum::GENERATING_SUCCESS, $fileData);
        } catch (\Exception $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::GENERATING_FAILED, null, ['app' => $exception->getMessage()]);
        }
    }
}