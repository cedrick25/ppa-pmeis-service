<?php

declare(strict_types=1);

namespace App\Report\Table;

use App\Common\AppReportHelper;
use App\Enum\SystemSettingNames;
use App\Service\TherapeuticCommunity\Sessions;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TCIA8 implements Form
{
    private const TABLE_NAME = "TCIA8";


    public function __construct(
        private AppReportHelper $appReportHelper,
        private Sessions      $sessionService,
        private int             $lastFilledOutCellY = 9,
        private array           $data = [],
        private array           $summaryData = [],
    ){}

    public function supports(string $tableName): bool
    {
        return self::TABLE_NAME === $tableName;
    }

    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function generate(array $data): BinaryFileResponse
    {
        $this->data = $this->getData($data);
        $this->data[SystemSettingNames::GENERATED_REPORTS_CODE] = $data[SystemSettingNames::GENERATED_REPORTS_CODE];

        $spreadsheet = $this->footer();
        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");

        $filePath = $_ENV['XLSX_PATH_FILE'] . self::TABLE_NAME . "-" . time() . ".xlsx";
        $writer->save($filePath);

        return new BinaryFileResponse($filePath);
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function footer(): Spreadsheet
    {
        $spreadsheet = $this->body();
        $this->lastFilledOutCellY++;

        $texts = [
            'C_1' => 'SUMMARY', 'C_2' => 'PHASES', 'D_2' => 'NO. OF CLIENTS PER PHASE', 'D_3' => '1ST QTR', 'H_3' => '2ND QTR',
            'K_3' => '3RD QTR', 'N_3' => '4TH QTR', 'D_4' => 'PET.', 'F_4' => 'TERM', 'H_4' => 'PET.', 'J_4' => 'TERM.',
            'K_4' => 'PET.', 'M_4' => 'TERM.', 'N_4' => 'PET.', 'O_4' => 'TERM.', 'C_5' => 'Preparatory', 'C_6' => 'I',
            'C_7' => 'II', 'C_8' => 'III', 'C_9' => 'IV- On-going', 'C_10' => 'Completed', 'C_11' => 'TOTAL', 'A_13' => 'INSTRUCTIONS',
            'A_15' => 'TABLE 1.A.8 OTHERS - PETITIONERS AND TERMINATED CLIENTS WHO CONTINUE TO PARTICIPATE IN THE TC PROGRAM',
            'A_17' => 'COLUMN 2', 'A_18' => 'Indicate appropriate docket number per client', 'A_20' => 'COLUMN NO. 7', 'A_21' => 'Mark check (√ ):',
            'A_22' => 'DO- if client is convicted of Drug Offense', 'A_23' => 'NDO- if client is convicted of Non-Drug Offense', 'A_25' => 'NOTE:',
            'A_26' => '1.  Other columns -  same instructions as Table 1.A.2 to 1.A.6', 'A_27' => "2.  Group clients' names if Petitioner or Terminated",
            'A_29' => 'NOTE:  ONLY PETITIONERS WHO WERE RECOMMENDED FOR GRANT CAN JOIN THE  TC LADDERIZED PROGRAM'
        ];

        $boldTexts = [
            'C_1:O_4', 'C_5:C_11', 'A_13:A_17', 'A_20', 'A_25', 'A_29'
        ];

        $merges = [
            'C_1:O_1', 'C_2:C_4', 'D_2:O_2', 'D_3:G_3', 'H_3:J_3', 'K_3:M_3', 'N_3:O_3', 'D_4:E_4', 'F_4:G_4', 'H_4:I_4', 'K_4:L_4', 'A_13:AC_13'
        ];

        $outlineBorders = [
            'C_1:O_1', 'C_2:C_4', 'D_2:O_2'
        ];

        $allBorders = [
            'D_3:O_11', 'C_5:C_11'
        ];

        foreach ($texts as $coordinates => $text) {
            $coordinate = $this->appReportHelper->buildCoordinate($coordinates, $this->lastFilledOutCellY);
            $spreadsheet->getActiveSheet()->setCellValue($coordinate, $text);
        }

        foreach ($boldTexts as $coordinates) {
            $coordinate = $this->appReportHelper->buildCoordinate($coordinates, $this->lastFilledOutCellY);
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getFont()->setBold(true);
        }

        foreach ($merges as $coordinates) {
            $coordinate = $this->appReportHelper->buildCoordinate($coordinates, $this->lastFilledOutCellY);
            $spreadsheet->getActiveSheet()->mergeCells($coordinate);
        }

        foreach ($outlineBorders as $coordinates) {
            $coordinate = $this->appReportHelper->buildCoordinate($coordinates, $this->lastFilledOutCellY);
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        }

        foreach ($allBorders as $coordinates) {
            $coordinate = $this->appReportHelper->buildCoordinate($coordinates, $this->lastFilledOutCellY);
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        $spreadsheet->getActiveSheet()->getStyle('C' . $this->lastFilledOutCellY + 1)->getAlignment()->setHorizontal('center');
        $spreadsheet->getActiveSheet()->getStyle('C' . $this->lastFilledOutCellY + 2)->getAlignment()->setHorizontal('center');
        $spreadsheet->getActiveSheet()->getStyle('D' . $this->lastFilledOutCellY + 2 . ':O' . $this->lastFilledOutCellY + 11)->getAlignment()->setHorizontal('center');

        $spreadsheet->getActiveSheet()->getStyle('C' . $this->lastFilledOutCellY + 2)->getAlignment()->setVertical('center');

        $summaryCoordinates = [
            'FIRST' => [
                'I' => ['Pet' => 'D_6', 'Term' => 'F_6'],
                'II' => ['Pet' => 'D_7', 'Term' => 'F_7'],
                'III' => ['Pet' => 'D_8', 'Term' => 'F_8'],
                'IV' => ['Pet' => 'D_9', 'Term' => 'F_9 '],
                'TOTAL' => ['Pet' => 'D_11', 'Term' => 'F_11'],
            ],
            'SECOND' => [
                'I' => ['Pet' => 'H_6', 'Term' => 'J_6'],
                'II' => ['Pet' => 'H_7', 'Term' => 'J_7'],
                'III' => ['Pet' => 'H_8', 'Term' => 'J_8'],
                'IV' => ['Pet' => 'H_9', 'Term' => 'J_9'],
                'TOTAL' => ['Pet' => 'H_11', 'Term' => 'J_11'],
            ],
            'THIRD' => [
                'I' => ['Pet' => 'K_6', 'Term' => 'M_6'],
                'II' => ['Pet' => 'K_7', 'Term' => 'M_7'],
                'III' => ['Pet' => 'K_8', 'Term' => 'M_8'],
                'IV' => ['Pet' => 'K_9', 'Term' => 'M_9'],
                'TOTAL' => ['Pet' => 'K_11', 'Term' => 'M_11'],
            ],
            'FOURTH' => [
                'I' => ['Pet' => 'N_6', 'Term' => 'O_6'],
                'II' => ['Pet' => 'N_7', 'Term' => 'O_7'],
                'III' => ['Pet' => 'N_8', 'Term' => 'O_8'],
                'IV' => ['Pet' => 'N_9', 'Term' => 'O_9'],
                'TOTAL' => ['Pet' => 'N_11', 'Term' => 'O_11'],
            ]
        ];

        foreach ($this->summaryData as $quarter => $row) {
            $quarterScore[$quarter] = [
                'Pet' => 0,
                'Term' => 0
            ];

            foreach ($row as $phase => $typeData) {
                foreach ($typeData as $type => $score) {
                    $coordinate = $this->appReportHelper->buildCoordinate($summaryCoordinates[$quarter][$phase][$type], $this->lastFilledOutCellY);
                    $spreadsheet->getActiveSheet()->setCellValue($coordinate, $score);
                    $quarterScore[$quarter][$type] += $score;
                }
            }

            $petCoordinate = $this->appReportHelper->buildCoordinate($summaryCoordinates[$quarter]['TOTAL']['Pet'], $this->lastFilledOutCellY);
            $termCoordinate = $this->appReportHelper->buildCoordinate($summaryCoordinates[$quarter]['TOTAL']['Term'], $this->lastFilledOutCellY);
            $spreadsheet->getActiveSheet()->setCellValue($petCoordinate, $quarterScore[$quarter]['Pet']);
            $spreadsheet->getActiveSheet()->setCellValue($termCoordinate, $quarterScore[$quarter]['Term']);
        }

        return $spreadsheet;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Exception
     */
    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();

        $phaseCoordinates = [
            'FIRST' => 'J', 'SECOND' => 'O', 'THIRD' => 'T', 'FOURTH' => 'Y'
        ];

        $quarterMonthCoordinates = [
            'FIRST_J' => 'K', 'FIRST_F' => 'L', 'FIRST_M' => 'M',
            'SECOND_A' => 'P', 'SECOND_M' => 'Q', 'SECOND_J' => 'R',
            'THIRD_J' => 'U', 'THIRD_A' => 'V', 'THIRD_S' => 'W',
            'FOURTH_O' => 'Z', 'FOURTH_N' => 'AA', 'FOURTH_D' => 'AB',
        ];

        $fsiCoordinates = [
            'FIRST' => 'N', 'SECOND' => 'S', 'THIRD' => 'X', 'FOURTH' => 'AC'
        ];

        $totalData = [
            'female' => 0,
            'male' => 0,
            'pwd' => 0,
            'senior_citizen' => 0,
            'do' => 0,
            'ndo' => 0,
        ];
        $monthlyTotal = [];
        $rowNumber = 1;
        $rows = $this->data['rows'];

        ksort($rows);

        $isTermShowed = false;
        foreach ($rows as $row) {
            $this->lastFilledOutCellY++;

            if (!$isTermShowed && $row['client_type'] === 'Term') {
                $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, 'TERMINATED');
                $spreadsheet->getActiveSheet()->getStyle('A' . $this->lastFilledOutCellY)->getFont()->setBold(true);
                $isTermShowed = true;
                $this->lastFilledOutCellY++;
            }

            $middleInitial = $row['middle_name'] != null ? substr($row['middle_name'], 0, 1) . '.' : '';
            $fullName = $row['last_name'] . ', ' . $row['first_name'] . ' ' . $middleInitial;

            $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, $rowNumber);
            $spreadsheet->getActiveSheet()->setCellValue("B" . $this->lastFilledOutCellY, $row['docket_number'] ?? '');
            $spreadsheet->getActiveSheet()->setCellValue("C" . $this->lastFilledOutCellY, $fullName);

            if ($row['gender'] === 'F') {
                $spreadsheet->getActiveSheet()->setCellValue('D' . $this->lastFilledOutCellY, '∕');
                $totalData['female']++;
            } else {
                $spreadsheet->getActiveSheet()->setCellValue('E' . $this->lastFilledOutCellY, '∕');
                $totalData['male']++;
            }

            if ($row['is_pwd'] !== '0') {
                $spreadsheet->getActiveSheet()->setCellValue('F' . $this->lastFilledOutCellY, '∕');
                $totalData['pwd']++;
            }
            if ($row['is_senior_citizen'] !== '0') {
                $spreadsheet->getActiveSheet()->setCellValue('G' . $this->lastFilledOutCellY, '∕');
                $totalData['senior_citizen']++;
            }

            if ($row['offense_category'] === 'DO') {
                $spreadsheet->getActiveSheet()->setCellValue('H' . $this->lastFilledOutCellY, '∕');
                $totalData['do']++;
            } else {
                $spreadsheet->getActiveSheet()->setCellValue('I' . $this->lastFilledOutCellY, '∕');
                $totalData['ndo']++;
            }

            $spreadsheet->getActiveSheet()->setCellValue($phaseCoordinates[$row['quarter']] . $this->lastFilledOutCellY, $row['phase']);

            foreach ($row['month_quarter'] as $monthQuarter) {
                $spreadsheet->getActiveSheet()->setCellValue($quarterMonthCoordinates[$monthQuarter] . $this->lastFilledOutCellY, '1');
                $monthInitial = explode('_', $monthQuarter)[1];

                if (!isset($monthlyTotal[$row['quarter']][$monthInitial])) {
                    $monthlyTotal[$row['quarter']][$monthInitial] = 0;
                }

                $monthlyTotal[$row['quarter']][$monthInitial]++;
            }

            if ($row['fsi']) {
                if (!isset($monthlyTotal[$row['quarter']]['FSI'])) {
                    $monthlyTotal[$row['quarter']]['FSI'] = 0;
                }

                $spreadsheet->getActiveSheet()->setCellValue($fsiCoordinates[$row['quarter']] . $this->lastFilledOutCellY, '√');
                $monthlyTotal[$row['quarter']]['FSI']++;
            }

            $remarks = $row['remarks'] ?? '';
            $spreadsheet->getActiveSheet()->setCellValue("AD" . $this->lastFilledOutCellY, $remarks . ' ' . $row['other_remarks']);
            $spreadsheet->getActiveSheet()
                ->getStyle("A" . $this->lastFilledOutCellY . ":AD" . $this->lastFilledOutCellY)
                ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            if (!isset($this->summaryData[$row['quarter']][$row['phase']][$row['client_type']])) {
                $this->summaryData[$row['quarter']][$row['phase']][$row['client_type']] = 0;
            }

            $this->summaryData[$row['quarter']][$row['phase']][$row['client_type']]++;

            $rowNumber++;
        }


        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('C' . $this->lastFilledOutCellY, 'TOTAL');
        $spreadsheet->getActiveSheet()->setCellValue('D' . $this->lastFilledOutCellY, $totalData['female']);
        $spreadsheet->getActiveSheet()->setCellValue('E' . $this->lastFilledOutCellY, $totalData['male']);
        $spreadsheet->getActiveSheet()->setCellValue('F' . $this->lastFilledOutCellY, $totalData['pwd']);
        $spreadsheet->getActiveSheet()->setCellValue('G' . $this->lastFilledOutCellY, $totalData['senior_citizen']);
        $spreadsheet->getActiveSheet()->setCellValue('H' . $this->lastFilledOutCellY, $totalData['do']);
        $spreadsheet->getActiveSheet()->setCellValue('I' . $this->lastFilledOutCellY, $totalData['ndo']);
        $spreadsheet->getActiveSheet()->getStyle('A' . $this->lastFilledOutCellY . ':' . 'AC' . $this->lastFilledOutCellY)->getFont()->setBold(true);
        $spreadsheet->getActiveSheet()
            ->getStyle("A" . $this->lastFilledOutCellY . ":AD" . $this->lastFilledOutCellY)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()
            ->getStyle("D" . $this->lastFilledOutCellY . ":I" . $this->lastFilledOutCellY)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);


        $this->lastFilledOutCellY++;
        $quarterMonthTotalCoordinates = [
            'FIRST' => [
                'J' => 'K', 'F' => 'L', 'M' => 'M', 'FSI' => 'N'
            ],
            'SECOND' => [
                'A' => 'P', 'M' => 'Q', 'J' => 'R', 'FSI' => 'S'
            ],
            'THIRD' => [
                'J' => 'U', 'A' => 'V', 'S' => 'W', 'FSI' => 'X'
            ],
            'FOURTH' => [
                'O' => 'Z', 'N' => 'AA', 'D' => 'AB', 'FSI' => 'AC'
            ]
        ];

        $spreadsheet->getActiveSheet()->setCellValue('B' . $this->lastFilledOutCellY, 'Total # of clients attending TC');
        $spreadsheet->getActiveSheet()->setCellValue('J' . $this->lastFilledOutCellY, 'TOTAL');
        $spreadsheet->getActiveSheet()->setCellValue('O' . $this->lastFilledOutCellY, 'TOTAL');
        $spreadsheet->getActiveSheet()->setCellValue('T' . $this->lastFilledOutCellY, 'TOTAL');
        $spreadsheet->getActiveSheet()->setCellValue('Y' . $this->lastFilledOutCellY, 'TOTAL');
        $spreadsheet->getActiveSheet()->getStyle('A' . $this->lastFilledOutCellY . ':' . 'AC' . $this->lastFilledOutCellY)->getFont()->setBold(true);
        $spreadsheet->getActiveSheet()
            ->getStyle("A" . $this->lastFilledOutCellY . ":AD" . $this->lastFilledOutCellY)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()
            ->getStyle("K" . $this->lastFilledOutCellY . ":N" . $this->lastFilledOutCellY)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
        $spreadsheet->getActiveSheet()
            ->getStyle("P" . $this->lastFilledOutCellY . ":S" . $this->lastFilledOutCellY)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
        $spreadsheet->getActiveSheet()
            ->getStyle("U" . $this->lastFilledOutCellY . ":X" . $this->lastFilledOutCellY)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
        $spreadsheet->getActiveSheet()
            ->getStyle("Z" . $this->lastFilledOutCellY . ":AC" . $this->lastFilledOutCellY)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
        foreach ($monthlyTotal as $quarter => $row) {
            foreach ($row as $monthFsi => $score) {
                $spreadsheet->getActiveSheet()->setCellValue($quarterMonthTotalCoordinates[$quarter][$monthFsi] . $this->lastFilledOutCellY, $score);
            }
        }

        $spreadsheet->getActiveSheet()->getStyle('D9:AD' . $this->lastFilledOutCellY)->getAlignment()->setHorizontal('center');

        return $spreadsheet;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function header(): Spreadsheet
    {
        $spreadsheet = $this->prepare();

        $thinBorders = [
            "A3:A8", "B3:B8", "D3:E5", "F6:F8", "D6:D8", "E6:E8", "F3:F8", "G3:G8", "G6:G8", "H3:I5", "H6:H8", "I6:I8", "J3:AC3", "J4:N4", "O4:S4",
            "T4:X4", "Y4:AC4", "J5:J8", "L5:L8", "M5:M8", "N5:N8", "O5:O8", "P5:P8", "Q5:Q8", "R5:R8", "S5:S8", "T5:T8", "U5:U8", "V5:J8", "V5:J8",
            "W5:W8", "X5:X8", "Y5:Y8", "Z5:Z8", "AA5:AA8", "AB5:AB8", "AC5:AC8", "AD5:AD8"
        ];

        foreach ($thinBorders as $coordinate) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        }

        $spreadsheet->getActiveSheet()->getStyle("A9:AD9")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle("A3:AD8")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);
        $spreadsheet->getActiveSheet()->getStyle("A1:A2")->getAlignment()->setHorizontal('left');
        $spreadsheet->getActiveSheet()->getStyle("A9")->getAlignment()->setHorizontal('left');

        return $spreadsheet;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function prepare(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $textAndCoordinates = [
            'A1' => 'OTHERS',
            'AD1' => $this->data[SystemSettingNames::GENERATED_REPORTS_CODE],
            'A2' => 'Table I.A.8 – PETITIONERS AND TERMINATED CLIENTS WHO CONTINUE TO PARTICIPATE IN THE TC PROGRAM',
            'D3' => 'Sex',
            'H3' => 'Supervision',
            'J3' => 'PHASE (Preparatory, I, II, III, IV)  (9)',
            'AD3' => 'Remarks (9)',
            'D4' => '(4)',
            'F4' => 'P',
            'G4' => 'S',
            'H4' => 'Offense',
            'J4' => '1st Quarter',
            'O4' => '2nd Quarter',
            'T4' => '3rd Quarter',
            'Y4' => '4th Quarter',
            'AD4' => '(Date of Termination / PSIR',
            'A5' => 'No.',
            'B5' => 'Docket',
            'C5' => 'Name',
            'F5' => 'W',
            'G5' => 'C',
            'H5' => 'Category',
            'N5' => 'FSI',
            'S5' => 'FSI',
            'X5' => 'FSI',
            'AC5' => 'FSI',
            'AD5' => 'Submitted)',
            'A6' => '(1)',
            'B6' => 'Number',
            'C6' => '(3)',
            'D6' => 'F',
            'E6' => 'M',
            'F6' => 'D',
            'J6' => 'Prep./',
            'O6' => 'Prep./',
            'T6' => 'Prep./',
            'Y6' => 'Prep./',
            'AD6' => 'For Petitioners, include',
            'B7' => '(2)',
            'H7' => 'DO',
            'I7' => 'NDO',
            'J7' => 'Phase',
            'K7' => 'J',
            'L7' => 'F',
            'M7' => 'M',
            'N7' => 'Mark',
            'O7' => 'Phase',
            'P7' => 'A',
            'Q7' => 'M',
            'R7' => 'J',
            'S7' => 'Mark',
            'T7' => 'Phase',
            'U7' => 'J',
            'V7' => 'A',
            'W7' => 'S',
            'X7' => 'Mark',
            'Y7' => 'Phase',
            'Z7' => 'O',
            'AA7' => 'N',
            'AB7' => 'D',
            'AC7' => 'Mark',
            'AD7' => 'activities conducted during the',
            'F8' => '(5)',
            'G8' => '(6)',
            'N8' => '√',
            'S8' => '√',
            'X8' => '√',
            'AC8' => '√',
            'AD8' => 'Preparatory stage (TCLP)',
            'A9' => 'PETITIONERS',
        ];

        $mergesCoordinates = [
            "D3:E3", "H3:I3", "J3:AC3", "D4:E4", "H4:I4", "J4:N4", "O4:S4", "T4:X4", "Y4:AC4", "H5:I5"
        ];

        $boldCoordinates = [
            "A1", "A2", "D4", "AD1", "F3:F8", "G3:G8", "A9"
        ];

        $verticalAlignedCoordinates = [
            "A1:AD9" => "center",
        ];

        $horizontalAlignedCoordinates = [
            "A1:AD9" => "center",
        ];

        $adjustedColumnWidthCoordinates = [
            'A' => 3, 'B' => 10, 'C' => 20, 'D' => 3, 'E' => 3, 'F' => 3, 'G' => 3, 'AD' => 25
        ];

        foreach ($textAndCoordinates as $coordinate => $text) {
            $spreadsheet->getActiveSheet()->setCellValue($coordinate, $text);
        }

        foreach ($mergesCoordinates as $coordinate) {
            $spreadsheet->getActiveSheet()->mergeCells($coordinate);
        }

        foreach ($boldCoordinates as $coordinate) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getFont()->setBold(true);
        }

        foreach ($verticalAlignedCoordinates as $coordinate => $alignment) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getAlignment()->setVertical($alignment);
        }

        foreach ($horizontalAlignedCoordinates as $coordinate => $alignment) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getAlignment()->setHorizontal($alignment);
        }

        foreach ($adjustedColumnWidthCoordinates as $coordinate => $width) {
            $spreadsheet->getActiveSheet()->getColumnDimension($coordinate)->setWidth($width);
        }

        $spreadsheet->getActiveSheet()->getStyle("AD4:AD8")->getFont()->setItalic(true);
        $spreadsheet->getActiveSheet()->getStyle("AD4:AD8")->getFont()->setSize(9);

        return $spreadsheet;
    }

    private function getData(array $data): array
    {
        $petitioners = $this->sessionService->getTCIA2(
            $data['quarter_id'],
            $data['field_office_id'],
            'Pet'
        );

        $terminated = $this->sessionService->getTCIA2(
            $data['quarter_id'],
            $data['field_office_id'],
            'Term'
        );

        $result = array_merge(array_values($petitioners['data'] ?? []), array_values($terminated['data'] ?? []));

        return ['rows' => $result];
    }
}