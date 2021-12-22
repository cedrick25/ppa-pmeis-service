<?php

namespace App\Report\Table;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use \PhpOffice\PhpSpreadsheet\IOFactory;

class TCIA1 implements Form
{
    private const TABLE_NAME = 'TCIA1';

    public function __construct(
        private int $lastFilledOutCellY = 1
    ){}

    public function supports(string $tableName): bool
    {
        return self::TABLE_NAME === $tableName;
    }

    /**
     * @throws Exception
     */
    public function generate(): void
    {
        $spreadsheet = $this->footer();
        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");

        // User .env for saved file path
        $writer->save("/home/jewicked/Downloads/". self::TABLE_NAME .".xlsx");
    }

    public function header(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        $spreadsheet->getActiveSheet()->setCellValue('Z1', 'FIELD OFFICE IQPR FORM  -  PPA- PLD-FR-004');

        return $spreadsheet;
    }

    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();
        $spreadsheet->getActiveSheet()->setCellValue('A14', 'Pre-morning');

        return $spreadsheet;
    }

    public function footer(): Spreadsheet
    {
        $spreadsheet = $this->body();
        $spreadsheet->getActiveSheet()->setCellValue('X' . $this->lastFilledOutCellY + 1, 'Total Number of:   1)  VPAs involved (Headcount)');

        return $spreadsheet;
    }
}