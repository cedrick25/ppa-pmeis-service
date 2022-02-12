<?php

declare(strict_types=1);

namespace App\Report\Table;

use App\Common\AppDateHelper;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TCIA2 implements Form
{
    private const TABLE_NAME = "TCIA2";


    public function __construct(
        private AppDateHelper $appDateHelper,
        private int           $lastFilledOutCellY = 8,
        private array         $data = [],
        private array         $summaryData = [],
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
        $this->data = $data;

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
        $currentRowNumber = $this->lastFilledOutCellY + 2;
        $currentRowNumber2 = $currentRowNumber + 1;
        $currentRowNumber3 = $currentRowNumber + 2;
        $currentRowNumber4 = $currentRowNumber + 3;
        $currentRowNumber5 = $currentRowNumber + 4;
        $currentRowNumber6 = $currentRowNumber + 5;
        $currentRowNumber7 = $currentRowNumber + 6;
        $currentRowNumber8 = $currentRowNumber + 7;
        $currentRowNumber9 = $currentRowNumber + 8;
        $currentRowNumber10 = $currentRowNumber + 9;
        $currentRowNumber11 = $currentRowNumber + 10;
        $currentRowNumber12 = $currentRowNumber + 11;
        $currentRowNumber13 = $currentRowNumber + 12;


        $summaryCoordinates = [
            'FIRST' => [
                'I' => "C$currentRowNumber5", 'II' => "C$currentRowNumber6",
                'III' => "C$currentRowNumber7", 'IV' => "C$currentRowNumber8",
                'TOTAL' => "C$currentRowNumber10",
            ],
            'SECOND' => [
                'I' => "H$currentRowNumber5", 'II' => "H$currentRowNumber6",
                'III' => "H$currentRowNumber7", 'IV' => "H$currentRowNumber8",
                'TOTAL' => "H$currentRowNumber10",
            ],
            'THIRD' => [
                'I' => "L$currentRowNumber5", 'II' => "L$currentRowNumber6",
                'III' => "L$currentRowNumber7", 'IV' => "L$currentRowNumber8",
                'TOTAL' => "L$currentRowNumber10",
            ],
            'FOURTH' => [
                'I' => "Q$currentRowNumber5", 'II' => "Q$currentRowNumber6",
                'III' => "Q$currentRowNumber7", 'IV' => "Q$currentRowNumber8",
                'TOTAL' => "Q$currentRowNumber10",
            ]
        ];

        $textAndCoordinates = [
            "A$currentRowNumber" => 'S     U     M     M     A     R     Y', "X$currentRowNumber" => 'No. of PS under the following circumstances AND HAVE NOT attended',
            "A$currentRowNumber2" => 'PHASES', "C$currentRowNumber2" => 'NO. OF CLIENTS PER PHASE', "X$currentRowNumber2" => 'any TCLP session/ activity for the ENTIRE quarter.',
            "C$currentRowNumber3" => '1ST QTR', "H$currentRowNumber3" => '2ND QTR', "L$currentRowNumber3" => '3RD QTR', "Q$currentRowNumber3" => '4TH QTR',
            "Y$currentRowNumber3" => 'On CS to other Field Offices', "A$currentRowNumber4" => 'Preparatory', "Y$currentRowNumber4" => 'Died w/ no report submitted to Court',
            "A$currentRowNumber5" => 'I', "Y$currentRowNumber5" => 'Absconded  w/ no report submitted to Court',
            "A$currentRowNumber6" => 'II', "Y$currentRowNumber6" => 'In Jail with no report submitted to Court',
            "A$currentRowNumber7" => 'III', "Y$currentRowNumber7" => 'With Serious Ailment',
            "A$currentRowNumber8" => 'IV - On-going', "Y$currentRowNumber8" => 'On Travel Abroad (with permit)',
            "A$currentRowNumber9" => 'Completed', "Y$currentRowNumber9" => 'Supervision cases dropped (Terminated, ',
            "A$currentRowNumber10" => 'TOTAL', "Y$currentRowNumber10" => 'Revoked, Transferred)',
            "Y$currentRowNumber11" => 'Case/ s pending in Court', "Y$currentRowNumber12" => 'Others', "Y$currentRowNumber13" => 'TOTAL',
        ];

        $boldCoordinates = [
            "A$currentRowNumber:T$currentRowNumber", "C$currentRowNumber3:Q$currentRowNumber3", "A$currentRowNumber2:A$currentRowNumber10"
        ];

        $mergesCoordinates = [
            "A$currentRowNumber:T$currentRowNumber", "A$currentRowNumber2:B$currentRowNumber2", "C$currentRowNumber2:T$currentRowNumber2",
            "A$currentRowNumber3:B$currentRowNumber3", "C$currentRowNumber3:G$currentRowNumber3", "H$currentRowNumber3:K$currentRowNumber3",
            "L$currentRowNumber3:P$currentRowNumber3", "Q$currentRowNumber3:T$currentRowNumber3",
            "A$currentRowNumber4:B$currentRowNumber4", "C$currentRowNumber4:G$currentRowNumber4", "H$currentRowNumber4:K$currentRowNumber4",
            "L$currentRowNumber4:P$currentRowNumber4", "Q$currentRowNumber4:T$currentRowNumber4",
            "A$currentRowNumber5:B$currentRowNumber5", "C$currentRowNumber5:G$currentRowNumber5", "H$currentRowNumber5:K$currentRowNumber5",
            "L$currentRowNumber5:P$currentRowNumber5", "Q$currentRowNumber5:T$currentRowNumber5",
            "A$currentRowNumber6:B$currentRowNumber6", "C$currentRowNumber6:G$currentRowNumber6", "H$currentRowNumber6:K$currentRowNumber6",
            "L$currentRowNumber6:P$currentRowNumber6", "Q$currentRowNumber6:T$currentRowNumber6",
            "A$currentRowNumber7:B$currentRowNumber7", "C$currentRowNumber7:G$currentRowNumber7", "H$currentRowNumber7:K$currentRowNumber7",
            "L$currentRowNumber7:P$currentRowNumber7", "Q$currentRowNumber7:T$currentRowNumber7",
            "A$currentRowNumber8:B$currentRowNumber8", "C$currentRowNumber8:G$currentRowNumber8", "H$currentRowNumber8:K$currentRowNumber8",
            "L$currentRowNumber8:P$currentRowNumber8", "Q$currentRowNumber8:T$currentRowNumber8",
            "A$currentRowNumber9:B$currentRowNumber9", "C$currentRowNumber9:G$currentRowNumber9", "H$currentRowNumber9:K$currentRowNumber9",
            "L$currentRowNumber9:P$currentRowNumber9", "Q$currentRowNumber9:T$currentRowNumber9",
            "A$currentRowNumber10:B$currentRowNumber10", "C$currentRowNumber10:G$currentRowNumber10", "H$currentRowNumber10:K$currentRowNumber10",
            "L$currentRowNumber10:P$currentRowNumber10", "Q$currentRowNumber10:T$currentRowNumber10",
        ];

        foreach ($textAndCoordinates as $coordinate => $text) {
            $spreadsheet->getActiveSheet()->setCellValue($coordinate, $text);
        }

        foreach ($boldCoordinates as $coordinate) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getFont()->setBold(true);
        }

        foreach ($mergesCoordinates as $coordinate) {
            $spreadsheet->getActiveSheet()->mergeCells($coordinate);
        }

        foreach ($this->summaryData as $quarter => $row) {
            foreach ($row as $phase => $score) {
                $spreadsheet->getActiveSheet()->setCellValue($summaryCoordinates[$quarter][$phase], $score);
            }

            $spreadsheet->getActiveSheet()->setCellValue($summaryCoordinates[$quarter]['TOTAL'], array_sum($row));
        }

        $supervisionCasesDropped = $this->data['footer']['Terminated'] + $this->data['footer']['Revoked'] + $this->data['footer']['Transferred'];
        $spreadsheet->getActiveSheet()->setCellValue('AF' . $currentRowNumber3, $this->data['footer']['on CS']);
        $spreadsheet->getActiveSheet()->setCellValue('AF' . $currentRowNumber4, $this->data['footer']['Died']);
        $spreadsheet->getActiveSheet()->setCellValue('AF' . $currentRowNumber5, $this->data['footer']['Absconded']);
        $spreadsheet->getActiveSheet()->setCellValue('AF' . $currentRowNumber6, $this->data['footer']['In Jail with no report']);
        $spreadsheet->getActiveSheet()->setCellValue('AF' . $currentRowNumber7, $this->data['footer']['With Serious Ailment']);
        $spreadsheet->getActiveSheet()->setCellValue('AF' . $currentRowNumber8, $this->data['footer']['On Travel Abroad (with permit)']);
        $spreadsheet->getActiveSheet()->setCellValue('AF' . $currentRowNumber9, $supervisionCasesDropped);
        $spreadsheet->getActiveSheet()->setCellValue('AF' . $currentRowNumber11, $this->data['footer']['Case/ s pending in Court']);
        $spreadsheet->getActiveSheet()->setCellValue('AF' . $currentRowNumber12, $this->data['footer']['Others']);
        $spreadsheet->getActiveSheet()->setCellValue('AF' . $currentRowNumber13, array_sum($this->data['footer']));

        $spreadsheet->getActiveSheet()->getStyle("A$currentRowNumber:T$currentRowNumber")->getAlignment()->setHorizontal('center');

        $spreadsheet->getActiveSheet()->getStyle("A" . $currentRowNumber . ":T" . $currentRowNumber10)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $spreadsheet->getActiveSheet()->getStyle("A" . $currentRowNumber13 + 1 . ":AF" . $currentRowNumber13 + 1)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);

        $spreadsheet->getActiveSheet()->getStyle("X$currentRowNumber:AF$currentRowNumber13")->getAlignment()->setHorizontal('left');

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
            'FIRST' => 'L', 'SECOND' => 'Q', 'THIRD' => 'V', 'FOURTH' => 'AA'
        ];

        $quarterMonthCoordinates = [
            'FIRST_J' => 'M', 'FIRST_F' => 'N', 'FIRST_M' => 'O',
            'SECOND_A' => 'R', 'SECOND_M' => 'S', 'SECOND_J' => 'T',
            'THIRD_J' => 'W', 'THIRD_A' => 'X', 'THIRD_S' => 'Y',
            'FOURTH_O' => 'AB', 'FOURTH_N' => 'AC', 'FOURTH_D' => 'AD',
        ];

        $fsiCoordinates = [
            'FIRST' => 'P', 'SECOND' => 'U', 'THIRD' => 'Z', 'FOURTH' => 'AE'
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
        $footer = [
            'Terminated' => 0, 'Revoked' => 0, 'on CS' => 0, 'Transferred' => 0, 'Absconded' => 0, 'Died' => 0, 'In Jail with no report' => 0,
            'With Serious Ailment' => 0, 'On Travel Abroad (with permit)' => 0, 'Case/ s pending in Court' => 0, 'Others' => 0,
        ];

        $rowNumber = 1;
        foreach ($this->data['rows'] as $row) {
            $this->lastFilledOutCellY++;
            $middleInitial = $row['middle_name'] != null ? substr($row['middle_name'], 0, 1) . '.' : '';
            $fullName = $row['last_name'] . ', ' . $row['first_name'] . ' ' . $middleInitial;
            $dateOfBirth = $this->appDateHelper->convertStringToImmutableDate($row['date_of_birth']);
            $supervisionStart = $this->appDateHelper->convertStringToImmutableDate($row['supervision_start']);
            $supervisionEnd = $this->appDateHelper->convertStringToImmutableDate($row['supervision_end']);

            $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, $rowNumber);
            $spreadsheet->getActiveSheet()->setCellValue("B" . $this->lastFilledOutCellY, $fullName);

            if ($row['gender'] === 'F') {
                $spreadsheet->getActiveSheet()->setCellValue('C' . $this->lastFilledOutCellY, '∕');
                $totalData['female']++;
            } else {
                $spreadsheet->getActiveSheet()->setCellValue('D' . $this->lastFilledOutCellY, '∕');
                $totalData['male']++;
            }

            if ($row['is_pwd'] !== '0') {
                $spreadsheet->getActiveSheet()->setCellValue('E' . $this->lastFilledOutCellY, '∕');
            }
            if ($row['is_senior_citizen'] !== '0') {
                $spreadsheet->getActiveSheet()->setCellValue('F' . $this->lastFilledOutCellY, '∕');
            }

            if ($row['offense_category'] === 'DO') {
                $spreadsheet->getActiveSheet()->setCellValue('H' . $this->lastFilledOutCellY, '∕');
                $totalData['do']++;
            } else {
                $spreadsheet->getActiveSheet()->setCellValue('I' . $this->lastFilledOutCellY, '∕');
                $totalData['ndo']++;
            }

            $spreadsheet->getActiveSheet()->setCellValue("G" . $this->lastFilledOutCellY, $dateOfBirth->format('d-M-y'));
            $spreadsheet->getActiveSheet()->setCellValue("J" . $this->lastFilledOutCellY, $supervisionStart->format('d-M-y'));
            $spreadsheet->getActiveSheet()->setCellValue("K" . $this->lastFilledOutCellY, $supervisionEnd->format('d-M-y'));

            $spreadsheet->getActiveSheet()->setCellValue($phaseCoordinates[$row['quarter']] . $this->lastFilledOutCellY, $row['phase']);

            foreach ($row['month_quarter'] as $monthQuarter) {
                $spreadsheet->getActiveSheet()->setCellValue($quarterMonthCoordinates[$monthQuarter] . $this->lastFilledOutCellY, '1');
                $monthInitial = explode('_', $monthQuarter)[1];

                if (!isset($monthlyTotal[$row['quarter']][$monthInitial])) {
                    $monthlyTotal[$row['quarter']][$monthInitial] = 0;
                }

                $monthlyTotal[$row['quarter']][$monthInitial]++;
            }

            foreach ($row['month_quarter_fsi'] as $monthQuarter) {
                $spreadsheet->getActiveSheet()->setCellValue($fsiCoordinates[$monthQuarter] . $this->lastFilledOutCellY, '√');
                $quarter = explode('_', $monthQuarter)[0];

                if (!isset($monthlyTotal[$quarter]['FSI'])) {
                    $monthlyTotal[$quarter]['FSI'] = 0;
                }

                $monthlyTotal[$quarter]['FSI']++;
            }

            $spreadsheet->getActiveSheet()->setCellValue("AF" . $this->lastFilledOutCellY, $row['remarks']);
            $spreadsheet->getActiveSheet()->getStyle("A" . $this->lastFilledOutCellY . ":AF" . $this->lastFilledOutCellY)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            if (!isset($this->summaryData[$row['quarter']][$row['phase']])) {
                $this->summaryData[$row['quarter']][$row['phase']] = 0;
            }

            $this->summaryData[$row['quarter']][$row['phase']]++;
            $footer[$row['remarks']]++;

            $rowNumber++;
        }

        $this->data['footer'] = $footer;

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()
            ->getStyle("A" . $this->lastFilledOutCellY . ":AF" . $this->lastFilledOutCellY)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()
            ->getStyle("A" . $this->lastFilledOutCellY . ":AF" . $this->lastFilledOutCellY)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('B' . $this->lastFilledOutCellY, 'TOTAL');
        $spreadsheet->getActiveSheet()->setCellValue('C' . $this->lastFilledOutCellY, $totalData['female']);
        $spreadsheet->getActiveSheet()->setCellValue('D' . $this->lastFilledOutCellY, $totalData['male']);
        $spreadsheet->getActiveSheet()->setCellValue('E' . $this->lastFilledOutCellY, $totalData['pwd']);
        $spreadsheet->getActiveSheet()->setCellValue('F' . $this->lastFilledOutCellY, $totalData['senior_citizen']);
        $spreadsheet->getActiveSheet()->setCellValue('G' . $this->lastFilledOutCellY, 'TOTAL');
        $spreadsheet->getActiveSheet()->setCellValue('H' . $this->lastFilledOutCellY, $totalData['do']);
        $spreadsheet->getActiveSheet()->setCellValue('I' . $this->lastFilledOutCellY, $totalData['ndo']);
        $spreadsheet->getActiveSheet()->getStyle('A' . $this->lastFilledOutCellY . ':' . 'AE' . $this->lastFilledOutCellY)->getFont()->setBold(true);
        $spreadsheet->getActiveSheet()
            ->getStyle("A" . $this->lastFilledOutCellY . ":AF" . $this->lastFilledOutCellY)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()
            ->getStyle("C" . $this->lastFilledOutCellY . ":F" . $this->lastFilledOutCellY)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
        $spreadsheet->getActiveSheet()
            ->getStyle("H" . $this->lastFilledOutCellY . ":I" . $this->lastFilledOutCellY)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);

        $this->lastFilledOutCellY++;
        $quarterMonthTotalCoordinates = [
            'FIRST' => ['J' => 'M', 'F' => 'N', 'M' => 'O', 'FSI' => 'P'],
            'SECOND' => ['A' => 'R', 'M' => 'S', 'J' => 'T', 'FSI' => 'U'],
            'THIRD' => ['J' => 'W', 'A' => 'X', 'S' => 'Y', 'FSI' => 'Z'],
            'FOURTH' => ['O' => 'AB', 'N' => 'AC', 'D' => 'AD', 'FSI' => 'AE']
        ];

        $spreadsheet->getActiveSheet()->setCellValue('B' . $this->lastFilledOutCellY, 'Total # of clients attending TC');
        $spreadsheet->getActiveSheet()->mergeCells('B' . $this->lastFilledOutCellY . ':K' . $this->lastFilledOutCellY);
        $spreadsheet->getActiveSheet()->getStyle('B' . $this->lastFilledOutCellY . ':K' . $this->lastFilledOutCellY)->getAlignment()->setHorizontal('center');
        $spreadsheet->getActiveSheet()->setCellValue('L' . $this->lastFilledOutCellY, 'TOTAL');
        $spreadsheet->getActiveSheet()->setCellValue('Q' . $this->lastFilledOutCellY, 'TOTAL');
        $spreadsheet->getActiveSheet()->setCellValue('V' . $this->lastFilledOutCellY, 'TOTAL');
        $spreadsheet->getActiveSheet()->setCellValue('AA' . $this->lastFilledOutCellY, 'TOTAL');
        $spreadsheet->getActiveSheet()->getStyle('A' . $this->lastFilledOutCellY . ':' . 'AE' . $this->lastFilledOutCellY)->getFont()->setBold(true);
        $spreadsheet->getActiveSheet()
            ->getStyle("A" . $this->lastFilledOutCellY . ":AF" . $this->lastFilledOutCellY)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()
            ->getStyle("M" . $this->lastFilledOutCellY . ":P" . $this->lastFilledOutCellY)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
        $spreadsheet->getActiveSheet()
            ->getStyle("R" . $this->lastFilledOutCellY . ":U" . $this->lastFilledOutCellY)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
        $spreadsheet->getActiveSheet()
            ->getStyle("W" . $this->lastFilledOutCellY . ":Z" . $this->lastFilledOutCellY)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
        $spreadsheet->getActiveSheet()
            ->getStyle("AB" . $this->lastFilledOutCellY . ":AE" . $this->lastFilledOutCellY)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);

        foreach ($monthlyTotal as $quarter => $row) {
            foreach ($row as $monthFsi => $score) {
                $spreadsheet->getActiveSheet()->setCellValue($quarterMonthTotalCoordinates[$quarter][$monthFsi] . $this->lastFilledOutCellY, $score);
            }
        }

        $spreadsheet->getActiveSheet()->getStyle('C9:AF' . $this->lastFilledOutCellY)->getAlignment()->setHorizontal('center');

        return $spreadsheet;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function header(): Spreadsheet
    {
        $spreadsheet = $this->prepare();

        $thinBorders = [
            "A3:A8", "B3:B8", "C3:D5", "C6:C8", "D6:D8", "E3:E8", "F3:F8", "G3:G5", "G6:G8", "H3:I5", "H6:H8", "I6:I8", "J3:K5", "J6:J8", "K6:K8",
            "L3:AE3", "L4:P4", "Q4:U4", "V4:Z4", "AA4:AE4", "L5:L8", "M5:M8", "N5:N8", "O5:O8", "P5:P8", "Q5:Q8", "R5:T8", "S5:S8", "T5:T8", "U5:U8",
            "V5:V8", "W5:W8", "X5:X8", "Y5:Y8", "Z5:Z8", "AA5:AA8", "AB5:AB8", "AC5:AC8", "AD5:AD8", "AE5:AE8", "AF3:AF8"
        ];

        foreach ($thinBorders as $coordinate) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        }

        $spreadsheet->getActiveSheet()->getStyle("A3:AF8")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);

        return $spreadsheet;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function prepare(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $textAndCoordinates = [
            'A1' => 'ACTIVE SUPERVISION',
            'C1' => '(per Form 5)',
            'AF1' => 'PPA- PLD-FR-004',
            'A2' => 'Table I.A.2 - PROBATIONERS',
            'C3' => 'Sex',
            'G3' => 'Date',
            'H3' => 'Offense',
            'J3' => 'Supervision',
            'L3' => 'PHASE (Preparatory, I, II, III, IV)  (9)',
            'AF3' => '(10)',
            'A4' => 'No.',
            'C4' => '(3)',
            'E4' => 'P',
            'F4' => 'S',
            'G4' => 'of Birth',
            'H4' => 'Category',
            'J4' => 'Period',
            'L4' => '1st Quarter',
            'Q4' => '2nd Quarter',
            'V4' => '3rd Quarter',
            'AA4' => '4th Quarter',
            'AF4' => 'Remarks',
            'A5' => '(1)',
            'B5' => 'Name',
            'E5' => 'W',
            'F5' => 'C',
            'G5' => '(6)',
            'H5' => '(7)',
            'J5' => '(8)',
            'P5' => 'FSI',
            'U5' => 'FSI',
            'Z5' => 'FSI',
            'AE5' => 'FSI',
            'AF5' => 'Ex.  w/ FR/VR, Terminated,',
            'B6' => '(2)',
            'C6' => 'F',
            'D6' => 'M',
            'E6' => 'D',
            'G6' => 'mm /dd /',
            'L6' => 'Prep./',
            'Q6' => 'Prep./',
            'V6' => 'Prep./',
            'AA6' => 'Prep./',
            'AF6' => 'Revoked, on CS, Transferred,',
            'G7' => 'yyyy',
            'H7' => 'DO',
            'I7' => 'NDO',
            'J7' => 'Start',
            'K7' => 'End',
            'L7' => 'Phase',
            'M7' => 'J',
            'N7' => 'F',
            'O7' => 'M',
            'P7' => 'Mark',
            'Q7' => 'Phase',
            'R7' => 'A',
            'S7' => 'M',
            'T7' => 'J',
            'U7' => 'Mark',
            'V7' => 'Phase',
            'W7' => 'J',
            'X7' => 'A',
            'Y7' => 'S',
            'Z7' => 'Mark',
            'AA7' => 'Phase',
            'AB7' => 'O',
            'AC7' => 'N',
            'AD7' => 'D',
            'AE7' => 'Mark',
            'AF7' => 'Absconded, Died, Others',
            'E8' => '(4)',
            'F8' => '(5)',
            'P8' => '√',
            'U8' => '√',
            'Z8' => '√',
            'AE8' => '√',
            'AF8' => '(Indicate dates if  Applicable)',
        ];

        $mergesCoordinates = [
            "L3:AE3", "C3:D3", "H3:I3", "J3:K3", "C4:D4", "H4:I4", "J4:K4", "L4:P4", "Q4:U4", "V4:Z4", "AA4:AE4", "J5:K5", "H5:I5", "C6:C8", "D6:D8"
        ];

        $boldCoordinates = [
            "A1", "C1", "AF1", "A2", "AF3", "C4", "A5", "G5", "H5", "J5", "B6", "E8", "F8"
        ];

        $verticalAlignedCoordinates = [
            "A" => "center",
            "B5:B6" => "center",
            "C:AF" => "center"
        ];

        $horizontalAlignedCoordinates = [
            "B5:B6" => "center",
            "C:AF" => "center"
        ];

        $adjustedColumnWidthCoordinates = [
            'A' => 3, 'B' => 20, 'C' => 3, 'D' => 3, 'E' => 4, 'F' => 4, 'G' => 10, 'J' => 10, 'K' => 10, 'AF' => 25
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
        $spreadsheet->getActiveSheet()->getStyle('C1')->getAlignment()->setHorizontal('left');

        foreach ($adjustedColumnWidthCoordinates as $coordinate => $width) {
            $spreadsheet->getActiveSheet()->getColumnDimension($coordinate)->setWidth($width);
        }

        $spreadsheet->getActiveSheet()->getStyle("AF5:AF8")->getFont()->setSize(9);

        return $spreadsheet;
    }
}