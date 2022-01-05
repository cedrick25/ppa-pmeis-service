<?php

namespace App\Report\Table;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use \PhpOffice\PhpSpreadsheet\IOFactory;

class TCIA1 implements Form
{
    private const TABLE_NAME = "TCIA1";

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
    public function generate(): string
    {
        $spreadsheet = $this->footer();
        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");

        $filePath = $_ENV['XLSX_PATH_FILE'] . self::TABLE_NAME . "-" . time() .".xlsx";
        $writer->save($filePath);

        return $filePath;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function header(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        $spreadsheet->getActiveSheet()->setCellValue('Z1', 'FIELD OFFICE IQPR FORM  -  PPA- PLD-FR-004');
        $spreadsheet->getActiveSheet()->setCellValue('B2', 'INTEGRATED QUARTERLY PERFORMANCE REPORT');
        $spreadsheet->getActiveSheet()->mergeCells("B2:AB2");
        // To be updated with dynamic data
        $spreadsheet->getActiveSheet()->setCellValue('A3', 'FIELD OFFICE :  STA. ROSA CITY PAROLE AND PROBATION OFFICE');
        $spreadsheet->getActiveSheet()->setCellValue('AA3', '1st Quarter, CY 2020');

        $spreadsheet->getActiveSheet()->setCellValue('A5', 'I.  PROGRAM  IMPLEMENTATION');
        $spreadsheet->getActiveSheet()->setCellValue('A7', 'A.  THERAPEUTIC COMMUNITY LADDERIZED PROGRAM (TCLP)');
        $spreadsheet->getActiveSheet()->setCellValue('A8', "Table I.A.1 - CLIENTS' / FSG INVOLVEMENT BY PHASE/ SESSION/ ACTIVITY/TREATMENT CATEGORY");

        $spreadsheet->getActiveSheet()->getStyle("Z1")->getFont()->setBold(true);
        $spreadsheet->getActiveSheet()->getStyle("B2:AB2")->getFont()->setSize("16");
        $spreadsheet->getActiveSheet()->getStyle("B2:AB2")->getFont()->setBold(true);
        $spreadsheet->getActiveSheet()->getStyle("B2:AB2")->getAlignment()->setHorizontal("center");
        $spreadsheet->getActiveSheet()->getStyle("B2:AB2")->getAlignment()->setVertical("center");
        $spreadsheet->getActiveSheet()->getRowDimension(2)->setRowHeight(40);
        $spreadsheet->getActiveSheet()->getStyle("A3")->getFont()->setBold(true);
        $spreadsheet->getActiveSheet()->getStyle("A5")->getFont()->setBold(true);
        $spreadsheet->getActiveSheet()->getStyle("A7")->getFont()->setBold(true);
        $spreadsheet->getActiveSheet()->getStyle("A8")->getFont()->setBold(true);

        return $spreadsheet;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();
        $spreadsheet->getActiveSheet()->setCellValue('D9', 'Treatment Category  (3)');
        $spreadsheet->getActiveSheet()->setCellValue('P9', 'Session');
        // Make a way to make (#) bold
        $spreadsheet->getActiveSheet()->setCellValue('R9', 'Number of Clients Involved  (6)');
        $spreadsheet->getActiveSheet()->setCellValue('Z9', 'Resource Person/ Facilitator   (7)');
        $spreadsheet->getActiveSheet()->setCellValue('AB9', 'Remarks  (8)');

        $spreadsheet->getActiveSheet()->setCellValue('O10', 'Date  and Venue');
        $spreadsheet->getActiveSheet()->setCellValue('P10', '(5)');
        $spreadsheet->getActiveSheet()->setCellValue('A10', 'Phase');
        $spreadsheet->getActiveSheet()->setCellValue('B10', 'Batch');
        $spreadsheet->getActiveSheet()->setCellValue('C10', 'Session/Activity Title');
        $spreadsheet->getActiveSheet()->setCellValue('R10', 'Active');
        $spreadsheet->getActiveSheet()->setCellValue('X10', 'Others');
        $spreadsheet->getActiveSheet()->setCellValue('Z10', 'Name');
        $spreadsheet->getActiveSheet()->setCellValue('AA10', 'Role');
        $spreadsheet->getActiveSheet()->setCellValue('AB10', 'No. of trees planted/');
        $spreadsheet->getActiveSheet()->setCellValue('C11', '(based on TCLP  Manuals)');

        $spreadsheet->getActiveSheet()->mergeCells("P9:Q9");
        $spreadsheet->getActiveSheet()->mergeCells("R9:Y9");
        $spreadsheet->getActiveSheet()->mergeCells("Z9:AA9");
        $spreadsheet->getActiveSheet()->mergeCells("P10:Q10");
        $spreadsheet->getActiveSheet()->mergeCells("R10:W10");
        $spreadsheet->getActiveSheet()->mergeCells("X10:Y10");
        $spreadsheet->getActiveSheet()->mergeCells("D9:N10");

        $spreadsheet->getActiveSheet()->getStyle("D9:N10")->getAlignment()->setHorizontal("center");
        $spreadsheet->getActiveSheet()->getStyle("D9:N10")->getAlignment()->setVertical("center");
        $spreadsheet->getActiveSheet()->getStyle("R9:Y9")->getAlignment()->setHorizontal("center");
        $spreadsheet->getActiveSheet()->getStyle("Z9:AA9")->getAlignment()->setHorizontal("center");
        $spreadsheet->getActiveSheet()->getStyle("AB9")->getAlignment()->setHorizontal("center");
        $spreadsheet->getActiveSheet()->getStyle("X10:Y10")->getAlignment()->setHorizontal("center");

        $spreadsheet->getActiveSheet()->getStyle("A9:AB14")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle("A10")->getFont()->setBold(true);
        $spreadsheet->getActiveSheet()->getStyle("P10")->getFont()->setBold(true);
        $spreadsheet->getActiveSheet()->getStyle("B10")->getFont()->setBold(true);

        return $spreadsheet;
    }

    public function footer(): Spreadsheet
    {
        $spreadsheet = $this->body();
        $spreadsheet->getActiveSheet()->setCellValue('X' . $this->lastFilledOutCellY + 1, 'Total Number of:   1)  VPAs involved (Headcount)');

        return $spreadsheet;
    }
}