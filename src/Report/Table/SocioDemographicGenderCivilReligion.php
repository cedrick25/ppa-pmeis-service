<?php

declare(strict_types=1);

namespace App\Report\Table;

use App\Service\Volunteerism\Volunteer;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SocioDemographicGenderCivilReligion implements Form
{
    private const TABLE_NAME = "SocioDemographicGenderCivilReligion";
    
    public function __construct(
        private Volunteer   $service,
        private int         $lastFilledOutCellY = 10,
        private array       $data = [],
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
        unset($this->data['rows']['Regional Office - Region I']);
        unset($this->data['rows']['Regional Office - Region II']);
        unset($this->data['rows']['Regional Office - Region III']);
        unset($this->data['rows']['Regional Office - Region IV-A']);
        unset($this->data['rows']['Regional Office - Region IV-B']);
        unset($this->data['rows']['Regional Office - Region V']);
        unset($this->data['rows']['Regional Office - Region VI']);
        unset($this->data['rows']['Regional Office - Region VII']);
        unset($this->data['rows']['Regional Office - Region VIII']);
        unset($this->data['rows']['Regional Office - Region IX']);
        unset($this->data['rows']['Regional Office - Region X']);
        unset($this->data['rows']['Regional Office - Region XI']);
        unset($this->data['rows']['Regional Office - Region XII']);
        unset($this->data['rows']['Regional Office - Region XIII']);
        unset($this->data['rows']['Regional Office - CAR']);
        unset($this->data['rows']['Regional Office - NCR']);

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

        return $spreadsheet;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();

        $coordinates = [
            'gender' => [
                'M' => 'B',
                'F' => 'C'
            ],
            'civil_status' => [
                'Single' => 'E',
                'Married' => 'F',
                'Widowed' => 'G',
                'Seperated' => 'H',
                'Not Indicated' => 'I'
            ],
            'religion' => [
                'Roman Catholic' => 'K',
                'INC' => 'L',
                'Islam' => 'M',
                'Others' => 'N',
                'Not Indicate' => 'O'
            ]
        ];

        $total = [
            'M' => 0,
            'F' => 0,
            'genderTotal' => 0,
            'Single' => 0,
            'Married' => 0,
            'Widowed' => 0,
            'Seperated' => 0,
            'civil_Not Indicated' => 0,
            'civilStatusTotal' => 0,
            'Roman Catholic' => 0,
            'INC' => 0,
            'Islam' => 0,
            'Others' => 0,
            'religion_Not Indicated' => 0,
            'religionTotal' => 0
        ];

        foreach ($this->data['rows'] as $region=>$row) {
            $this->lastFilledOutCellY++;
            $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, $region);

            $genderTotal = 0;
            foreach ($row['gender'] as $gender=>$value) {
                $cellColumn = $coordinates['gender'][$gender];
                $total[$gender] += $value;
                $spreadsheet->getActiveSheet()->setCellValue($cellColumn . $this->lastFilledOutCellY, $value);
                $genderTotal += $value;
            }

            $civilStatusTotal = 0;
            foreach ($row['civil_status'] as $civilStatus=>$value) {
                $cellColumn = $coordinates['civil_status'][$civilStatus];
                if ($civilStatus === 'Not Indicated') {
                    $total['civil_' . $civilStatus] += $value;
                } else {
                    $total[$civilStatus] += $value;
                }
                $spreadsheet->getActiveSheet()->setCellValue($cellColumn . $this->lastFilledOutCellY, $value);
                $civilStatusTotal += $value;
            }

            $religionTotal = 0;
            foreach ($row['religion'] as $religion=>$value) {
                $cellColumn = $coordinates['religion'][$religion];

                if ($religion === 'Not Indicated') {
                    $total['religion_' . $religion] += $value;
                } else {
                    $total[$religion] += $value;
                }
                $spreadsheet->getActiveSheet()->setCellValue($cellColumn . $this->lastFilledOutCellY, $value);
                $religionTotal += $value;
            }

            $total['genderTotal'] += $genderTotal;
            $total['civilStatusTotal'] += $civilStatusTotal;
            $total['religionTotal'] += $religionTotal;
            $spreadsheet->getActiveSheet()->setCellValue('P' . $this->lastFilledOutCellY, $religionTotal);
        }

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, 'GRAND TOTAL');
        $spreadsheet->getActiveSheet()->getStyle('A' . $this->lastFilledOutCellY)->getFont()->setBold(true);
        $spreadsheet->getActiveSheet()->setCellValue('B' . $this->lastFilledOutCellY, $total['M']);
        $spreadsheet->getActiveSheet()->setCellValue('C' . $this->lastFilledOutCellY, $total['F']);
        $spreadsheet->getActiveSheet()->setCellValue('D' . $this->lastFilledOutCellY, $total['genderTotal']);
        $spreadsheet->getActiveSheet()->setCellValue('E' . $this->lastFilledOutCellY, $total['Single']);
        $spreadsheet->getActiveSheet()->setCellValue('F' . $this->lastFilledOutCellY, $total['Married']);
        $spreadsheet->getActiveSheet()->setCellValue('G' . $this->lastFilledOutCellY, $total['Widowed']);
        $spreadsheet->getActiveSheet()->setCellValue('H' . $this->lastFilledOutCellY, $total['Seperated']);
        $spreadsheet->getActiveSheet()->setCellValue('I' . $this->lastFilledOutCellY, $total['civil_Not Indicated']);
        $spreadsheet->getActiveSheet()->setCellValue('J' . $this->lastFilledOutCellY, $total['civilStatusTotal']);
        $spreadsheet->getActiveSheet()->setCellValue('K' . $this->lastFilledOutCellY, $total['Roman Catholic']);
        $spreadsheet->getActiveSheet()->setCellValue('L' . $this->lastFilledOutCellY, $total['INC']);
        $spreadsheet->getActiveSheet()->setCellValue('M' . $this->lastFilledOutCellY, $total['Islam']);
        $spreadsheet->getActiveSheet()->setCellValue('N' . $this->lastFilledOutCellY, $total['Others']);
        $spreadsheet->getActiveSheet()->setCellValue('O' . $this->lastFilledOutCellY, $total['religion_Not Indicated']);
        $spreadsheet->getActiveSheet()->setCellValue('P' . $this->lastFilledOutCellY, $total['religionTotal']);

        $spreadsheet->getActiveSheet()->getStyle('A10:P' . $this->lastFilledOutCellY)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('A10:P' . $this->lastFilledOutCellY)->getAlignment()->setHorizontal('center');
        $spreadsheet->getActiveSheet()->getStyle('A10:P' . $this->lastFilledOutCellY)->getAlignment()->setVertical('center');

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
            'N1' => 'CSD-FR-011-00',
            'A2' => 'VPA SOCIO-DEMOGRAPHIC REPORT',
            'A3' => 'As of________20__',
            'A5' => 'Regional Office',
            'B5' => 'GENDER',
            'E5' => 'CIVIL STATUS',
            'K5' => 'RELIGION',
            'B6' => 'Male',
            'C6' => 'Female',
            'D6' => 'Total',
            'E6' => 'Single',
            'F6' => 'Married',
            'G6' => 'Widowed',
            'H6' => 'Seperated',
            'I6' => 'Not Indicated',
            'J6' => 'Total',
            'K6' => 'Roman Catholic',
            'L6' => 'INC',
            'M6' => 'Islam',
            'N6' => 'Others',
            'O6' => 'Not Indicated',
            'P6' => 'Total'
        ];
        $mergesCoordinates = [
            'A2:P2', 'A3:P3', 'A5:A10', 'B5:D5', 'E5:J5', 'K5:P5', 'B6:B10', 'C6:C10', 'D6:D10', 'E6:E10', 'F6:F10', 'G6:G10',
            'H6:H10', 'I6:I10', 'J6:J10', 'K6:K10', 'L6:L10', 'M6:M10', 'N6:N10', 'O6:O10', 'P6:P10'
        ];
        $boldCoordinates = ['A1:P10'];
        $verticalAlignedCoordinates = ['A1:P10' => 'center'];
        $horizontalAlignedCoordinates = ['A1:P10' => 'center'];
        $adjustedColumnWidthCoordinates = ['A' => 40];
        $outlineBorderThinCoordinates = [
            'A5:A10', 'B5:D5', 'E5:J5', 'K5:P5', 'B6:B10', 'C6:C10', 'D6:D10', 'E6:E10', 'F6:F10', 'G6:G10',
            'H6:H10', 'I6:I10', 'J6:J10', 'K6:K10', 'L6:L10', 'M6:M10', 'N6:N10', 'O6:O10', 'P6:P10'
        ];
        $rotateTextCoordinates = [
            'B6:B10', 'C6:C10', 'D6:D10', 'E6:E10', 'F6:F10', 'G6:G10', 'H6:H10', 'I6:I10',
            'J6:J10', 'K6:K10', 'L6:L10', 'M6:M10', 'N6:N10', 'O6:O10', 'P6:P10'
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

        foreach ($outlineBorderThinCoordinates as $coordinate) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        }

        foreach ($rotateTextCoordinates as $coordinate) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getAlignment()->setTextRotation(90);
        }

        $spreadsheet->getActiveSheet()->getRowDimension(5)->setRowHeight(20);
        $spreadsheet->getActiveSheet()->getRowDimension(10)->setRowHeight(60);
        $spreadsheet->getActiveSheet()->getStyle('B5:P10')->getFont()->setSize(8);

        return $spreadsheet;
    }

    private function getData(array $data): array
    {
        $result = $this->service->getConsolidatedSocioDemographic($data['region_id']);

        return ['rows' => $result['data'] ?? []];
    }
}