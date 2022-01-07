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
        $spreadsheet = $this->prepare();

        $spreadsheet->getActiveSheet()->getRowDimension(2)->setRowHeight(40);

        return $spreadsheet;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();

        $spreadsheet->getActiveSheet()->getStyle("A9:AB14")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $spreadsheet;
    }

    public function footer(): Spreadsheet
    {
        $spreadsheet = $this->body();
        $spreadsheet->getActiveSheet()->setCellValue('X' . $this->lastFilledOutCellY + 1, 'Total Number of:   1)  VPAs involved (Headcount)');

        return $spreadsheet;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function prepare(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        $textAndCoordinates = [
            'Z1' => 'FIELD OFFICE IQPR FORM  -  PPA- PLD-FR-004',
            'B2' => 'INTEGRATED QUARTERLY PERFORMANCE REPORT',
            // To be updated with dynamic data
            'A3' => 'FIELD OFFICE :  STA. ROSA CITY PAROLE AND PROBATION OFFICE',
            'AA3' => '1st Quarter, CY 2020',
            'A5' => 'I.  PROGRAM  IMPLEMENTATION',
            'A7' => 'A.  THERAPEUTIC COMMUNITY LADDERIZED PROGRAM (TCLP)',
            'A8' => "Table I.A.1 - CLIENTS' / FSG INVOLVEMENT BY PHASE/ SESSION/ ACTIVITY/TREATMENT CATEGORY",
            // Make a way to make (#) bold
            'D9' => 'Treatment Category  (3)',
            'P9' => 'Session',
            'R9' => 'Number of Clients Involved  (6)',
            'Z9' => 'Resource Person/ Facilitator   (7)',
            'AB9' => 'Remarks  (8)',
            'O10' => 'Date  and Venue',
            'P10' => '(5)',
            'A10' => 'Phase',
            'B10' => 'Batch',
            'C10' => 'Session/Activity Title',
            'R10' => 'Active',
            'X10' => 'Others',
            'Z10' => 'Name',
            'AA10' => 'Role',
            'AB10' => 'No. of trees planted/',
            'C11' => '(based on TCLP  Manuals)',
            'D11' => 'MTCS',
            'I11' => 'RA',
            'N11' => 'FSG*',
            'O11' => '(4)',
            'R11' => 'PS',
            'S11' => 'PR',
            'T11' => 'PD',
            'U11' => 'JICL',
            'V11' => 'FTMDO',
            'W11' => 'Total',
            'X11' => 'Pet',
            'Y11' => 'Term',
            'Z11' => '(Indicate if PPO, VPA,',
            'AB11' => 'Community Services and',
            'A12' => '(1)',
            'C12' => '(2)',
            'D12' => 'RBM',
            'E12' => 'AEP',
            'F12' => 'S',
            'G12' => 'CI',
            'H12' => 'PVS',
            'I12' => 'RBM',
            'J12' => 'AEP',
            'K12' => 'S',
            'L12' => 'CI',
            'M12' => 'PVS',
            'N12' => '(Indicate No. of',
            'P12' => 'LI',
            'Q12' => 'LO',
            'Z12' => 'ERP.   If VPA, specify',
            'AB12' => 'Other Related Activities/',
            'N13' => 'FSG Participants)',
            'Z13' => 'if TC trained)',
            'AB13' => 'Coop./ Self-Help Asso.'
        ];

        $mergesCoordinates = [
            "B2:AB2", "P9:Q9", "R9:Y9", "Z9:AA9", "P10:Q10", "R10:W10", "X10:Y10", "D9:N10", "D11:H11", "I11:M11", "R11:R13",
            "S11:S13", "T11:T13", "U11:U13", "V11:V13", "W11:W13", "X11:X13", "Y11:Y13", "A12:B12", "D12:D13", "D12:D13", "D12:D13",
            "E12:E13", "F12:F13", "G12:G13", "H12:H13", "I12:I13", "J12:J13", "K12:K13", "L12:L13", "M12:M13"
        ];

        $boldCoordinates = [
            "Z1", "B2:AB2", "A3", "A5", "A7", "A8", "A10", "P10", "B10", "D11:H11", "I11:M11", "N11", "O11", "A12:B12", "C12", "D12:M12"
        ];

        $verticalAlignedCoordinates = [
            "E11:E12"  => "center", "B2:AB2" => "center", "D9:N10"  => "center", "D11:D12"  => "center", "F11:F12"  => "center",
            "G11:G12"  => "center", "H11:H12"  => "center", "I11:I12"  => "center", "J11:J12"  => "center", "K11:K12"  => "center",
            "L11:L12"  => "center", "M11:M12"  => "center", "R11:R13"  => "center", "S11:S13"  => "center", "T11:T13"  => "center",
            "U11:U13"  => "center", "V11:V13"  => "center", "W11:W13"  => "center", "X11:X13"  => "center", "Y11:Y13"  => "center",
        ];

        $horizontalAlignedCoordinates = [
            "B2:AB2"  => "center", "D9:N10"  => "center", "R9:Y9"  => "center", "Z9:AA9"  => "center", "AB9"  => "center",
            "X10:Y10"  => "center", "D11:H11"  => "center", "D11:D12"  => "center", "E11:E12"  => "center", "F11:F12"  => "center",
            "G11:G12"  => "center", "H11:H12"  => "center", "I11:I12"  => "center", "J11:J12"  => "center", "K11:K12"  => "center",
            "L11:L12"  => "center", "M11:M12"  => "center", "I11:M11"  => "center", "N11"  => "center", "R11:R13"  => "center",
            "S11:S13"  => "center", "T11:T13"  => "center", "U11:U13"  => "center", "V11:V13"  => "center", "W11:W13"  => "center",
            "X11:X13"  => "center", "Y11:Y13"  => "center",
        ];

        $fontSizeAndCoordinates = [
            "B2:AB2" => 16, "R11:R13" => 9, "S11:S13" => 9, "T11:T13" => 9, "U11:U13" => 9, "V11:V13" => 9, "W11:W13" => 9,
            "X11:X13" => 9, "Y11:Y13" => 9
        ];

        foreach ($textAndCoordinates as $coordinate=>$text) {
            $spreadsheet->getActiveSheet()->setCellValue($coordinate, $text);
        }

        foreach ($mergesCoordinates as $coordinate) {
            $spreadsheet->getActiveSheet()->mergeCells($coordinate);
        }

        foreach ($boldCoordinates as $coordinate) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getFont()->setBold(true);
        }

        foreach ($verticalAlignedCoordinates as $coordinate=>$alignment) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getAlignment()->setVertical($alignment);
        }

        foreach ($horizontalAlignedCoordinates as $coordinate=>$alignment) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getAlignment()->setHorizontal($alignment);
        }

        foreach ($fontSizeAndCoordinates as $coordinate=>$fontSize) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getFont()->setSize($fontSize);
        }

        return $spreadsheet;
    }
}