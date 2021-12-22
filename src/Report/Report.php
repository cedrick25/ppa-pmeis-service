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
    public function __construct(iterable $reports){
        $this->reports = new Map($reports);
    }

    public function create(string $tableName): void
    {
        /** @var Form $report */
        foreach ($this->reports as $report) {
            if ($report->supports($tableName)) {
                $report->generate();
            }
        }
    }
}