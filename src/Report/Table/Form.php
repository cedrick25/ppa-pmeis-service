<?php

namespace App\Report\Table;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

interface Form
{
    public function supports(string $tableName): bool;

    public function generate(): void;

    public function header(): Spreadsheet;

    public function footer(): Spreadsheet;
}