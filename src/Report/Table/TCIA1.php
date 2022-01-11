<?php

namespace App\Report\Table;

use App\Repository\FieldOfficesRepository;
use App\Repository\TreatmentCategoriesRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use \PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TCIA1 implements Form
{
    private const TABLE_NAME = "TCIA1";

    /**
     * @param int $lastFilledOutCellY
     * @param array<string, mixed> $data
     */
    public function __construct(
        private FieldOfficesRepository $fieldOfficesRepository,
        private TreatmentCategoriesRepository $treatmentCategoriesRepository,
        private int $lastFilledOutCellY = 14,
        private array $data = [],
    ){}

    public function supports(string $tableName): bool
    {
        return self::TABLE_NAME === $tableName;
    }

    /**
     * @param array<string, mixed> $data
     * @return BinaryFileResponse
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function generate(array $data): BinaryFileResponse
    {
        $this->data = $data;

        $spreadsheet = $this->footer();
        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");

        $filePath = $_ENV['XLSX_PATH_FILE'] . self::TABLE_NAME . "-" . time() .".xlsx";
        $writer->save($filePath);

        return new BinaryFileResponse($filePath);
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function header(): Spreadsheet
    {
        $spreadsheet = $this->prepare();

        $spreadsheet->getActiveSheet()->getRowDimension(2)->setRowHeight(40);
        $spreadsheet->getActiveSheet()->getStyle("AA3")->getFont()->setItalic(true);

        return $spreadsheet;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();

        $spreadsheet->getActiveSheet()->getStyle("A9:AB14")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $liLOColumn = ['LI' => 'P', 'LO' => 'Q'];
        $treatmentCategoriesColumn = [
            'MTCS' => [
                'RBM' => 'D', 'AEP' => 'E', 'S' => 'F', 'CI' => 'G', 'PVS' => 'H'
            ],
            'RA' => [
                'RBM' => 'I', 'AEP' => 'J', 'S' => 'K', 'CI' => 'L', 'PVS' => 'M'
            ]
        ];

        foreach ($this->data['part1'] as $index=>$rows) {
            $this->lastFilledOutCellY++;
            $treatmentCategory = $this->treatmentCategoriesRepository->find($rows['treatment_category_id']);
            $tcExplodedName = explode('-', $treatmentCategory->getName());

            $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, $rows['phase_name']);
            $spreadsheet->getActiveSheet()->setCellValue("B" . $this->lastFilledOutCellY, $rows['batch']);
            $spreadsheet->getActiveSheet()->setCellValue("C" . $this->lastFilledOutCellY, $rows['session_activity_title']);
            $spreadsheet->getActiveSheet()->setCellValue(
                $treatmentCategoriesColumn[$tcExplodedName[0]][$tcExplodedName[1]] . $this->lastFilledOutCellY, "√");
            $spreadsheet->getActiveSheet()->setCellValue("N" . $this->lastFilledOutCellY, $rows['fsg']);
            $spreadsheet->getActiveSheet()->setCellValue(
                "O" . $this->lastFilledOutCellY,
                $rows['venue'] . '/ ' . $rows['date'] . '/ ' . $rows['period'] . ' Session'
            );
            $spreadsheet->getActiveSheet()->getStyle("O" . $this->lastFilledOutCellY)->getAlignment()->setWrapText(true);

            $part2Row = $this->data['part2'][$index];
            $spreadsheet->getActiveSheet()->setCellValue($liLOColumn[$part2Row['li_lo']] . $this->lastFilledOutCellY,"√");
            $spreadsheet->getActiveSheet()->setCellValue("R" . $this->lastFilledOutCellY, $part2Row['parolees']);
            $spreadsheet->getActiveSheet()->setCellValue("S" . $this->lastFilledOutCellY, $part2Row['probationers']);
            $spreadsheet->getActiveSheet()->setCellValue("T" . $this->lastFilledOutCellY, $part2Row['pardonees']);
            $spreadsheet->getActiveSheet()->setCellValue("U" . $this->lastFilledOutCellY, $part2Row['jicl']);
            $spreadsheet->getActiveSheet()->setCellValue("V" . $this->lastFilledOutCellY, $part2Row['ftmdo']);
            $total = intval($part2Row['parolees']) + intval($part2Row['probationers']) + intval($part2Row['pardonees']) + intval($part2Row['jicl']) + intval($part2Row['ftmdo']);
            $spreadsheet->getActiveSheet()->setCellValue("W" . $this->lastFilledOutCellY, $total);
            $spreadsheet->getActiveSheet()->setCellValue("X" . $this->lastFilledOutCellY, $part2Row['petitioners']);
            $spreadsheet->getActiveSheet()->setCellValue("Y" . $this->lastFilledOutCellY, $part2Row['terminated']);

            $resourcePerson = '';

            foreach ($part2Row['resource_person'] as $resource) {
                if ($resource['name'] != null) {
                    $resourcePerson .=  $resource['name'] . ',';
                }
            }
            $spreadsheet->getActiveSheet()->setCellValue("Z" . $this->lastFilledOutCellY, rtrim($resourcePerson, ','));

            $roles = '';
            foreach ($part2Row['role'] as $role) {
                $roles .=  $role . ',';
            }
            $spreadsheet->getActiveSheet()->setCellValue("AA" . $this->lastFilledOutCellY, rtrim($roles, ','));

            $spreadsheet->getActiveSheet()->getStyle("AA" . $this->lastFilledOutCellY)->getAlignment()->setWrapText(true);
            $spreadsheet->getActiveSheet()->setCellValue("AB" . $this->lastFilledOutCellY, $part2Row['remarks']);

            $spreadsheet->getActiveSheet()->getStyle("A". $this->lastFilledOutCellY .":AB" . $this->lastFilledOutCellY)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        return $spreadsheet;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function footer(): Spreadsheet
    {
        $spreadsheet = $this->body();
        $this->lastFilledOutCellY++;

        $spreadsheet->getActiveSheet()->setCellValue('X' . $this->lastFilledOutCellY + 1, 'Total Number of:   1)  VPAs involved (Headcount)');
        $spreadsheet->getActiveSheet()->setCellValue('Z' . $this->lastFilledOutCellY + 2, '2)  Frequency of VPAs Involvement');
        $spreadsheet->getActiveSheet()->setCellValue('Z' . $this->lastFilledOutCellY + 3, '3)  Trees Planted');
        $spreadsheet->getActiveSheet()->setCellValue('Z' . $this->lastFilledOutCellY + 4, '4)  Clients Involved in Tree Planting');
        $spreadsheet->getActiveSheet()->setCellValue('Z' . $this->lastFilledOutCellY + 5, '5)  Community Services and Other Related Activities');
        $spreadsheet->getActiveSheet()->setCellValue('Z' . $this->lastFilledOutCellY + 6, '6) Coop./ Self-Help Asso.');

        $spreadsheet->getActiveSheet()->setCellValue('AB' . $this->lastFilledOutCellY + 1, $this->data['footer']['vpa_headcount']);
        $spreadsheet->getActiveSheet()->setCellValue('AB' . $this->lastFilledOutCellY + 2, $this->data['footer']['frequency_of_vpa_involvement']);
        $spreadsheet->getActiveSheet()->setCellValue('AB' . $this->lastFilledOutCellY + 3, $this->data['footer']['trees_planted']);
        $spreadsheet->getActiveSheet()->setCellValue('AB' . $this->lastFilledOutCellY + 4, $this->data['footer']['clients_involve_in_tree_planting']);
        $spreadsheet->getActiveSheet()->setCellValue('AB' . $this->lastFilledOutCellY + 5, $this->data['footer']['community_services_and_other']);
        $spreadsheet->getActiveSheet()->setCellValue('AB' . $this->lastFilledOutCellY + 6, $this->data['footer']['coop_or_self_help']);

        $spreadsheet->getActiveSheet()->getStyle('AB' . $this->lastFilledOutCellY + 1)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('AB' . $this->lastFilledOutCellY + 2)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('AB' . $this->lastFilledOutCellY + 3)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('AB' . $this->lastFilledOutCellY + 4)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('AB' . $this->lastFilledOutCellY + 5)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('AB' . $this->lastFilledOutCellY + 6)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('A' . $this->lastFilledOutCellY + 7 . ':AB' . $this->lastFilledOutCellY + 7)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);

        return $spreadsheet;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function prepare(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $fieldOffice = $this->fieldOfficesRepository->find($this->data['part1'][0]['field_office_id']);
        $quarters = [
            'FIRST' => '1st',
            'SECOND' => '2nd',
            'THIRD' => '3rd',
            'FOURTH' => '4th'
        ];

        $textAndCoordinates = [
            'Z1' => 'FIELD OFFICE IQPR FORM  -  PPA- PLD-FR-004',
            'B2' => 'INTEGRATED QUARTERLY PERFORMANCE REPORT',
            'A3' => 'FIELD OFFICE :  ' . $fieldOffice->getName(),
            'AA3' => $quarters[$this->data['part1'][0]['name']] . ' Quarter, CY ' . $this->data['part1'][0]['year'],
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
            "Z1", "B2:AB2", "A3", "A5", "A7", "A8", "D9:N10", "A10", "P10", "B10", "D11:H11", "I11:M11", "N11", "O11", "A12:B12", "C12", "D12:M12"
        ];

        $verticalAlignedCoordinates = [
            "E11:E12"  => "center", "B2:AB2" => "center", "D9:N10"  => "center", "D11:D12"  => "center", "F11:F12"  => "center",
            "G11:G12"  => "center", "H11:H12"  => "center", "I11:I12"  => "center", "J11:J12"  => "center", "K11:K12"  => "center",
            "L11:L12"  => "center", "M11:M12"  => "center", "R11:R13"  => "center", "S11:S13"  => "center", "T11:T13"  => "center",
            "U11:U13"  => "center", "V11:V13"  => "center", "W11:W13"  => "center", "X11:X13"  => "center", "Y11:Y13"  => "center",
        ];

        $horizontalAlignedCoordinates = [
            "B2:AB2"  => "center", "D9:N10"  => "center", "R9:Y9"  => "center", "Z9:AA9"  => "center", "AB9"  => "center",
            "X10:Y10"  => "center", "A10:C10"  => "center", "C11"  => "center", "D11:H11"  => "center", "D11:D12"  => "center",
            "E11:E12"  => "center", "F11:F12"  => "center", "G11:G12"  => "center", "H11:H12"  => "center", "I11:I12"  => "center",
            "J11:J12"  => "center", "K11:K12"  => "center", "L11:L12"  => "center", "M11:M12"  => "center", "I11:M11"  => "center",
            "N11"  => "center", "R11:R13"  => "center", "S11:S13"  => "center", "T11:T13"  => "center", "U11:U13"  => "center",
            "V11:V13"  => "center", "W11:W13"  => "center", "X11:X13"  => "center", "Y11:Y13"  => "center", "A12:C12"  => "center",
        ];

        $fontSizeAndCoordinates = [
            "Z1" => 10, "A3" => 10, "AA3" => 10, "B2:AB2" => 16, "P9:Q9" => 9, "N12:N13" => 9, "R11:R13" => 9, "S11:S13" => 9, "T11:T13" => 9,
            "U11:U13" => 9, "V11:V13" => 9, "W11:W13" => 9, "X11:X13" => 9, "Y11:Y13" => 9,
        ];

        $adjustedColumnWidthCoordinates = [
            'C' => 35, 'N' => 15, 'P' => 3, 'Q' => 3, 'R' => 4, 'S' => 4, 'T' => 4, 'U' => 4,
            'V' => 5, 'W' => 4, 'X' => 4, 'Y' => 4, 'Z' => 20, 'AA' => 15, 'AB' => 25
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

        foreach ($adjustedColumnWidthCoordinates as $coordinate => $width) {
            $spreadsheet->getActiveSheet()->getColumnDimension($coordinate)->setWidth($width);
        }

        return $spreadsheet;
    }
}