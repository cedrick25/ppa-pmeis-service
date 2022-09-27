<?php

namespace App\Report;

use App\Enum\SystemSettingNames;
use App\Report\Table\Form;
use App\Service\System\SystemCodeSettings;
use Ds\Map;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Report
{

    /**
     * @var Map<string, Form>
     */
    private Map $reports;

    /**
     * @param iterable<Form> $reports
     */
    public function __construct(iterable $reports, private SystemCodeSettings $systemCodeSettings) {
        $this->reports = new Map($reports);
    }

    /**
     * @param array<string, mixed> $data
     * @return BinaryFileResponse|null
     */
    public function create(array $data): ?BinaryFileResponse
    {
        $data[SystemSettingNames::GENERATED_REPORTS_CODE] = $this->systemCodeSettings->getByName(SystemSettingNames::GENERATED_REPORTS_CODE);

        /** @var Form $report */
        foreach ($this->reports as $report) {
            if ($report->supports($data['table_name'])) {
                return $report->generate($data);
            }
        }

        return null;
    }
}