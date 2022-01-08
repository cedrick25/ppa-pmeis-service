<?php

namespace App\Report;

use App\Report\Table\Form;
use Ds\Map;

class Report
{

    /**
     * @var Map<string, Form>
     */
    private Map $reports;

    /**
     * @param iterable<Form> $reports
     */
    public function __construct(iterable $reports) {
        $this->reports = new Map($reports);
    }

    /**
     * @param array<string, mixed> $data
     * @return string|null
     */
    public function create(array $data): ?string
    {
        /** @var Form $report */
        foreach ($this->reports as $report) {
            if ($report->supports($data['table_name'])) {
                return $report->generate($data);
            }
        }

        return null;
    }
}