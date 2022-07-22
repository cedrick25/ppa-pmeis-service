<?php

declare(strict_types=1);

namespace App\Report\Table;

use App\Common\AppDateHelper;
use App\Entity\Volunteer;
use App\Service\Volunteerism\Operations;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VPAIC2 implements Form
{
    private const TABLE_NAME = "VPAIC2";
    
    public function __construct(
        private AppDateHelper   $appDateHelper,
        private Operations      $service,
        private int             $lastFilledOutCellY = 7,
        private array           $data = [],
    ){}

    public function supports(string $tableName): bool
    {
        return self::TABLE_NAME === $tableName;
    }

    /**
     * @throws Exception
     */
    public function generate(array $data): BinaryFileResponse
    {
        $this->data = $this->getData($data);

        $spreadsheet = $this->footer();
        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");

        $filePath = $_ENV['XLSX_PATH_FILE'] . self::TABLE_NAME . "-" . time() . ".xlsx";
        $writer->save($filePath);

        return new BinaryFileResponse($filePath);
    }

    public function footer(): Spreadsheet
    {
        $spreadsheet = $this->body();
        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->getStyle('B' . $this->lastFilledOutCellY)->getFont()->setBold(true);
        $spreadsheet->getActiveSheet()->setCellValue(
            'B' . $this->lastFilledOutCellY,
            'INACTIVE');

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->getStyle('B' . $this->lastFilledOutCellY)->getFont()->setBold(true);
        $spreadsheet->getActiveSheet()->setCellValue(
            'B' . $this->lastFilledOutCellY,
            'For purposes of IQPR only:   Inactive VPAs are those with valid appointments  but not rendering service  during the quarter.');

        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setVisible(false);
        $spreadsheet->getActiveSheet()->getColumnDimension('K')->setVisible(false);
        $spreadsheet->getActiveSheet()->getColumnDimension('S')->setVisible(false);
        $spreadsheet->getActiveSheet()->getColumnDimension('T')->setVisible(false);

        return $spreadsheet;
    }

    /**
     * @throws \Exception
     */
    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();
        $total = [
            'APPOINTED' => ['F' => 0, 'M' => 0],
            'REAPPOINTED' => ['F' => 0, 'M' => 0],
            'INACTIVE' => ['F' => 0, 'M' => 0],
            'DROPPED' => ['F' => 0, 'M' => 0],
        ];

        foreach ($this->data['rows']['APPOINTED'] as $index=>$row) {
            $rowNumber = $this->lastFilledOutCellY + $index + 1;
            /** @var Volunteer $volunteer */
            $volunteer = $row['volunteer'];

            $middleInitial = $volunteer->getMiddleName() != null ? substr($volunteer->getMiddleName(), 0, 1) . '.' : '';
            $fullName = $volunteer->getLastName() . ', ' . $volunteer->getFirstName() . ' ' . $middleInitial;
            $date = $this->appDateHelper->convertStringToImmutableDate($row['date']);

            $spreadsheet->getActiveSheet()->setCellValue("A" . $rowNumber, $fullName);
            $spreadsheet->getActiveSheet()->setCellValue("D" . $rowNumber, $date->format('d-M-y'));
            if ($volunteer->getGender() === 'F') {
                $spreadsheet->getActiveSheet()->setCellValue('E' . $rowNumber, '∕');
                $total['APPOINTED']['F']++;
            } else {
                $spreadsheet->getActiveSheet()->setCellValue('F' . $rowNumber, '∕');
                $total['APPOINTED']['M']++;
            }
            if ($volunteer->getIsPwd()) {
                $spreadsheet->getActiveSheet()->setCellValue('G' . $rowNumber, '∕');
            }
            if ($volunteer->getIsSeniorCitizen()) {
                $spreadsheet->getActiveSheet()->setCellValue('H' . $rowNumber, '∕');
            }

            $spreadsheet->getActiveSheet()->mergeCells('A' . $rowNumber . ':B' . $rowNumber);
        }

        foreach ($this->data['rows']['REAPPOINTED'] as $index=>$row) {
            $rowNumber = $this->lastFilledOutCellY + $index + 1;
            /** @var Volunteer $volunteer */
            $volunteer = $row['volunteer'];

            $middleInitial = $volunteer->getMiddleName() != null ? substr($volunteer->getMiddleName(), 0, 1) . '.' : '';
            $fullName = $volunteer->getLastName() . ', ' . $volunteer->getFirstName() . ' ' . $middleInitial;
            $date = $this->appDateHelper->convertStringToImmutableDate($row['date']);

            $spreadsheet->getActiveSheet()->setCellValue("I" . $rowNumber, $fullName);
            $spreadsheet->getActiveSheet()->setCellValue("L" . $rowNumber, $date->format('d-M-y'));
            if ($volunteer->getGender() === 'F') {
                $spreadsheet->getActiveSheet()->setCellValue('M' . $rowNumber, '∕');
                $total['REAPPOINTED']['F']++;
            } else {
                $spreadsheet->getActiveSheet()->setCellValue('N' . $rowNumber, '∕');
                $total['REAPPOINTED']['M']++;
            }
            if ($volunteer->getIsPwd()) {
                $spreadsheet->getActiveSheet()->setCellValue('O' . $rowNumber, '∕');
            }
            if ($volunteer->getIsSeniorCitizen()) {
                $spreadsheet->getActiveSheet()->setCellValue('P' . $rowNumber, '∕');
            }

            $spreadsheet->getActiveSheet()->mergeCells('I' . $rowNumber . ':J' . $rowNumber);
        }

        /**
         * @var Volunteer  $row
         */
        foreach ($this->data['rows']['INACTIVE'] as $index=>$row) {
            $rowNumber = $this->lastFilledOutCellY + $index + 1;

            $middleInitial = $row->getMiddleName() != null ? substr($row->getMiddleName(), 0, 1) . '.' : '';
            $fullName = $row->getLastName() . ', ' . $row->getFirstName() . ' ' . $middleInitial;

            $spreadsheet->getActiveSheet()->setCellValue("Q" . $rowNumber, $fullName);
            if ($row->getGender() === 'F') {
                $spreadsheet->getActiveSheet()->setCellValue('U' . $rowNumber, '∕');
                $total['INACTIVE']['F']++;
            } else {
                $spreadsheet->getActiveSheet()->setCellValue('V' . $rowNumber, '∕');
                $total['INACTIVE']['M']++;
            }

            $spreadsheet->getActiveSheet()->setCellValue('W' . $rowNumber, 'Non-reporting');
            $spreadsheet->getActiveSheet()->getStyle('W' . $rowNumber)->getFont()->setSize(8);

            $spreadsheet->getActiveSheet()->mergeCells('Q' . $rowNumber . ':R' . $rowNumber);
        }

        foreach ($this->data['rows']['DROPPED'] as $index=>$row) {
            $rowNumber = $this->lastFilledOutCellY + $index + 1;
            /** @var Volunteer $volunteer */
            $volunteer = $row['volunteer'];

            $middleInitial = $volunteer->getMiddleName() != null ? substr($volunteer->getMiddleName(), 0, 1) . '.' : '';
            $fullName = $volunteer->getLastName() . ', ' . $volunteer->getFirstName() . ' ' . $middleInitial;
            $date = $this->appDateHelper->convertStringToImmutableDate($row['date']);
            $dateEndorsed = $this->appDateHelper->convertStringToImmutableDate($row['date_endorsed']);

            $spreadsheet->getActiveSheet()->setCellValue("X" . $rowNumber, $fullName);
            $spreadsheet->getActiveSheet()->setCellValue("Y" . $rowNumber, $date->format('d-M-y'));
            if ($volunteer->getGender() === 'F') {
                $spreadsheet->getActiveSheet()->setCellValue('Z' . $rowNumber, '∕');
                $total['DROPPED']['F']++;
            } else {
                $spreadsheet->getActiveSheet()->setCellValue('AA' . $rowNumber, '∕');
                $total['DROPPED']['M']++;
            }
            $spreadsheet->getActiveSheet()->setCellValue('AB' . $rowNumber, $row['reason']);
            $spreadsheet->getActiveSheet()->setCellValue('AC' . $rowNumber, $dateEndorsed->format('d-M-y'));
        }

        $lastFilledY = max(
            count($this->data['rows']['APPOINTED']),
            count($this->data['rows']['REAPPOINTED']),
            count($this->data['rows']['INACTIVE']),
            count($this->data['rows']['DROPPED'])
        );
        $this->lastFilledOutCellY += $lastFilledY;

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, 'TOTAL');
        $spreadsheet->getActiveSheet()->mergeCells('A' . $this->lastFilledOutCellY . ':D' . $this->lastFilledOutCellY);
        $spreadsheet->getActiveSheet()->setCellValue('E' . $this->lastFilledOutCellY, $total['APPOINTED']['F']);
        $spreadsheet->getActiveSheet()->setCellValue('F' . $this->lastFilledOutCellY, $total['APPOINTED']['M']);
        $spreadsheet->getActiveSheet()->setCellValue('I' . $this->lastFilledOutCellY, 'TOTAL');
        $spreadsheet->getActiveSheet()->mergeCells('I' . $this->lastFilledOutCellY . ':L' . $this->lastFilledOutCellY);
        $spreadsheet->getActiveSheet()->setCellValue('M' . $this->lastFilledOutCellY, $total['REAPPOINTED']['F']);
        $spreadsheet->getActiveSheet()->setCellValue('N' . $this->lastFilledOutCellY, $total['REAPPOINTED']['M']);
        $spreadsheet->getActiveSheet()->setCellValue('Q' . $this->lastFilledOutCellY, 'TOTAL');
        $spreadsheet->getActiveSheet()->mergeCells('Q' . $this->lastFilledOutCellY . ':T' . $this->lastFilledOutCellY);
        $spreadsheet->getActiveSheet()->setCellValue('U' . $this->lastFilledOutCellY, $total['INACTIVE']['F']);
        $spreadsheet->getActiveSheet()->setCellValue('V' . $this->lastFilledOutCellY, $total['INACTIVE']['M']);
        $spreadsheet->getActiveSheet()->setCellValue('X' . $this->lastFilledOutCellY, 'TOTAL');
        $spreadsheet->getActiveSheet()->mergeCells('X' . $this->lastFilledOutCellY . ':Y' . $this->lastFilledOutCellY);
        $spreadsheet->getActiveSheet()->setCellValue('Z' . $this->lastFilledOutCellY, $total['DROPPED']['F']);
        $spreadsheet->getActiveSheet()->setCellValue('AA' . $this->lastFilledOutCellY, $total['DROPPED']['M']);
        $spreadsheet->getActiveSheet()
            ->getStyle("W" . $this->lastFilledOutCellY)
            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('80808080');
        $spreadsheet->getActiveSheet()
            ->getStyle("AB" . $this->lastFilledOutCellY . ":AC" . $this->lastFilledOutCellY)
            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('80808080');

        $spreadsheet->getActiveSheet()->getStyle('A8:AC' . $this->lastFilledOutCellY)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('A8:AC' . $this->lastFilledOutCellY)->getAlignment()->setHorizontal('center');
        $spreadsheet->getActiveSheet()->getStyle('A8:AC' . $this->lastFilledOutCellY)->getAlignment()->setVertical('center');

        return $spreadsheet;
    }

    public function header(): Spreadsheet
    {
        $spreadsheet = $this->prepare();

        return $spreadsheet;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function prepare(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $textAndCoordinates = [
            'A1' => 'Table I.C.2  - APPOINTED/ RENEWED / INACTIVE/ DROPPED', 'AC1' => 'PPA-PLD-FR-004',
            'A3' => 'Name of VPAs', 'E4' => 'Sex', 'M4' => 'Sex', 'U4' => 'Sex', 'Z4' => 'Sex', 'AC4' => 'If reason for Dropping is',
            'A5' => 'APPOINTED', 'D5' => 'Date', 'G5' => 'PWD', 'H5' => 'SC', 'I5' => 'RENEWED', 'L5' => 'Date', 'O5' => 'Date', 'P5' => 'SC',
            'Q5' => 'INACTIVE', 'W5' => 'Reason/s', 'X5' => 'DROPPED', 'Y5' => 'Date', 'AB5' => 'Reason/s', 'AC5' => 'Renewal, indicate date',
            'A6' => '(1)', 'E6' => 'F', 'F6' => 'M', 'I6' => '(2)', 'M6' => 'F', 'N6' => 'M', 'Q6' => '(3)', 'U6' => 'F', 'V6' => 'M', 'X6' => '(4)',
            'Z6' => 'F', 'AA6' => 'M', 'AC6' => 'Indorsed'
        ];
        $mergesCoordinates = ['A3:AC3', 'E6:E7', 'F6:F7', 'M6:M7', 'N6:N7', 'U6:U7', 'V6:V7', 'Z6:Z7', 'AA6:AA7'];
        $boldCoordinates = ['A1:AC1', 'A6:B6', 'Q6:R6', 'X6'];
        $verticalAlignedCoordinates = ['A3:AC7' => 'center'];
        $horizontalAlignedCoordinates = ['A3:AC7' => 'center'];
        $adjustedColumnWidthCoordinates = [
            'A' => 20, 'B' => 20, 'D' => 12, 'E' => 4, 'F' => 4, 'G' => 5, 'H' => 4, 'I' => 20, 'J' => 20, 'L' => 12, 'M' => 4,
            'N' => 4, 'O' => 6, 'P' => 4, 'Q' => 20, 'R' => 20, 'U' => 4, 'V' => 4, 'W' => 15, 'X' => 17, 'Y' => 12, 'Z' => 4,
            'AA' => 4, 'AB' => 20, 'AC' => 30
        ];
        $outlineBorderThinCoordinates = [
            'A3:AC3', 'A4:C7', 'D4:D7', 'E4:F5', 'E6:E7', 'F6:F7', 'G4:G7', 'H4:H7', 'I4:K7', 'L4:L7', 'M4:N5', 'M6:M7',
            'N6:N7', 'O4:O7', 'P4:P7', 'Q4:S7', 'U4:V5', 'U6:U7', 'V6:V7', 'W4:W7', 'X4:X7', 'Y4:Y7', 'Z4:AA5', 'Z6:Z7', 'AA6:AA7',
            'AB4:AB7', 'AC4:AC7'];

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

        foreach ($outlineBorderThinCoordinates as $coordinate) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        }

        return $spreadsheet;
    }

    private function getData(array $data): array
    {
        $result = $this->service->getVPA2(
            $data['quarter_id'],
            $data['field_office_id']
        );

        return ['rows' => $result['data'] ?? []];
    }
}