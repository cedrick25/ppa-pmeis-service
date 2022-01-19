<?php

namespace App\Report\Table;

use App\Common\AppDateHelper;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TCIA8 implements Form
{
    private const TABLE_NAME = "TCIA8";


    public function __construct(
        private AppDateHelper $appDateHelper,
        private int $lastFilledOutCellY = 8,
        private array $data = [],
        private array $summaryData = [],
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
        $clientTypesTable = [
            'PROBATIONERS' => 'TC.I.A.2',
            'PAROLEES' => 'TC.I.A.3',
            'PARDONEES' => 'TC.I.A.4',
            'JICLS' => 'TC.I.A.5',
            'FTMDOS' => 'TC.I.A.6'
        ];

        $spreadsheet = $this->footer();
        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");

        $filePath = $_ENV['XLSX_PATH_FILE'] . $data['client_type'] . "-" . time() .".xlsx";
        $writer->save($filePath);

        return new BinaryFileResponse($filePath);
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
     * @throws \Exception
     */
    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();
//        $this->lastFilledOutCellY++;
//
//        $phaseCoordinates = [
//            'FIRST' => 'L', 'SECOND' => 'Q', 'THIRD' => 'V', 'FOURTH' => 'AA'
//        ];
//
//        $quarterMonthCoordinates = [
//            'FIRST_J' => 'M', 'FIRST_F' => 'N', 'FIRST_M' => 'O',
//            'SECOND_A' => 'R', 'SECOND_M' => 'S', 'SECOND_J' => 'T',
//            'THIRD_J' => 'W', 'THIRD_A' => 'X', 'THIRD_S' => 'Y',
//            'FOURTH_O' => 'AB', 'FOURTH_N' => 'AC', 'FOURTH_D' => 'AD',
//        ];
//
//        $fsiCoordinates = [
//            'FIRST' => 'P', 'SECOND' => 'U', 'THIRD' => 'Z', 'FOURTH' => 'AE'
//        ];
//
//        $rowNumber = 1;
//        foreach ($this->data['rows'] as $row) {
//            $middleInitial = $row['middle_name'] != null ? substr($row['middle_name'], 0, 1)  . '.': '';
//            $fullName = $row['last_name'] . ', ' . $row['first_name'] . ' ' . $middleInitial;
//            $genderCoordinate = ($row['gender'] === 'F') ? 'C' : 'D';
//            $offenseCoordinate = ($row['offense_category'] === 'DO') ? 'H' : 'I';
//            $dateOfBirth = $this->appDateHelper->convertStringToImmutableDate($row['date_of_birth']);
//            $supervisionStart = $this->appDateHelper->convertStringToImmutableDate($row['supervision_start']);
//            $supervisionEnd = $this->appDateHelper->convertStringToImmutableDate($row['supervision_end']);
//
//            $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, $rowNumber);
//            $spreadsheet->getActiveSheet()->setCellValue("B" . $this->lastFilledOutCellY, $fullName);
//            $spreadsheet->getActiveSheet()->setCellValue($genderCoordinate . $this->lastFilledOutCellY, '∕');
//            if ($row['is_pwd'] !== '0') {
//                $spreadsheet->getActiveSheet()->setCellValue('E' . $this->lastFilledOutCellY, '∕');
//            }
//            if ($row['is_senior_citizen'] !== '0') {
//                $spreadsheet->getActiveSheet()->setCellValue('F' . $this->lastFilledOutCellY, '∕');
//            }
//            $spreadsheet->getActiveSheet()->setCellValue("G" . $this->lastFilledOutCellY, $dateOfBirth->format('d-M-y'));
//            $spreadsheet->getActiveSheet()->setCellValue($offenseCoordinate . $this->lastFilledOutCellY, '∕');
//            $spreadsheet->getActiveSheet()->setCellValue("J" . $this->lastFilledOutCellY, $supervisionStart->format('d-M-y'));
//            $spreadsheet->getActiveSheet()->setCellValue("K" . $this->lastFilledOutCellY, $supervisionEnd->format('d-M-y'));
//
//            $spreadsheet->getActiveSheet()->setCellValue($phaseCoordinates[$row['quarter']] . $this->lastFilledOutCellY, $row['phase']);
//
//            foreach ($row['month_quarter'] as $monthQuarter) {
//                $spreadsheet->getActiveSheet()->setCellValue($quarterMonthCoordinates[$monthQuarter] . $this->lastFilledOutCellY, '1');
//            }
//
//            foreach ($row['month_quarter_fsi'] as $monthQuarter) {
//                $spreadsheet->getActiveSheet()->setCellValue($fsiCoordinates[$monthQuarter] . $this->lastFilledOutCellY, '√');
//            }
//
//            $spreadsheet->getActiveSheet()->setCellValue("AF" . $this->lastFilledOutCellY, $row['remarks']);
//
//            $spreadsheet->getActiveSheet()->getStyle("A". $this->lastFilledOutCellY .":AF" . $this->lastFilledOutCellY)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
//
//            if (! isset($this->summaryData[$row['quarter']][$row['phase']])) {
//                $this->summaryData[$row['quarter']][$row['phase']] = 1;
//            } else {
//                $this->summaryData[$row['quarter']][$row['phase']]++;
//            }
//
//            $rowNumber++;
//        }

        return $spreadsheet;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function footer(): Spreadsheet
    {
        $spreadsheet = $this->body();

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
            "L3:AE3", "C3:D3", "C4:D4","H4:I4","J4:K4","L4:P4","Q4:U4","V4:Z4","AA4:AE4","J5:K5","C6:C8","D6:D8"
        ];

        $boldCoordinates = [
            "A1","C1","AF1","A2","AF3","C4","A5","G5","H5","J5","B6","E8","F8"
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
            'A' => 3, 'B' => 20, 'C' => 3, 'D' => 3, 'E' => 4, 'F' => 4, 'G' => 15, 'J' => 25, 'K' => 25, 'AF' => 25
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

        foreach ($adjustedColumnWidthCoordinates as $coordinate => $width) {
            $spreadsheet->getActiveSheet()->getColumnDimension($coordinate)->setWidth($width);
        }

         return $spreadsheet;
    }
}