<?php

namespace App\Report\Table;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

interface Form
{
    public function supports(string $tableName): bool;

    public function generate(array $data): BinaryFileResponse;

    public function header(): Spreadsheet;

    public function footer(): Spreadsheet;
}