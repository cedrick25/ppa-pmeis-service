<?php

namespace App\Report\Table;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

interface Form
{
    public function supports(string $tableName): bool;

    public function generate(array $data): string;

    public function header(): Spreadsheet;

    public function footer(): Spreadsheet;
}