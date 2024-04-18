<?php

declare(strict_types=1);

namespace App\Report\Table;

use App\Service\Volunteerism\Volunteer;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Repository\RegionsRepository;

class SocioDemographicEducationalAge implements Form
{
    private const TABLE_NAME = "SocioDemographicEducationalAge";
    
    public function __construct(
        private Volunteer   $service,
        private int         $lastFilledOutCellY = 10,
        private array       $data = [],
        private RegionsRepository  $regionsRepository,
    ) {}

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
            'education_attainment' => [
                'Post Graduate' => 'B',
                'College Graduate' => 'C',
                'College Level' => 'D',
                'Vocational' => 'E',
                'HS Graduate' => 'F',
                'HS Level' => 'G',
                'Elementary' => 'H',
                'Not Indicated' => 'I'
            ]
        ];

        $total = [
            'Post Graduate' => 0,
            'College Graduate' => 0,
            'College Level' => 0,
            'Vocational' => 0,
            'HS Graduate' => 0,
            'HS Level' => 0,
            'Elementary' => 0,
            'Not Indicated' => 0,
            '15-24' => 0,
            '25-34' => 0,
            '35-44' => 0,
            '45-54' => 0,
            '55-64' => 0,
            65 => 0,
            0 => 0,
            'Total' => 0,
            'AgeTotal' => 0,
        ];
        $ageCellsX = [
            '15-24' => 'K',
            '25-34' => 'L',
            '35-44' => 'M',
            '45-54' => 'N',
            '55-64' => 'O',
            65 => 'P',
            0 => 'Q',
        ];

        foreach ($this->data['rows'] as $region => $row) {
            $this->lastFilledOutCellY++;
            $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, $region);

            $totalScore = 0;
            $agesTotal = 0;
            foreach ($row['education_attainment'] as $educationAttainment => $value) {
                $cellColumn = $coordinates['education_attainment'][$educationAttainment];
                $total[$educationAttainment] += $value;
                $spreadsheet->getActiveSheet()->setCellValue($cellColumn . $this->lastFilledOutCellY, $value);
                $totalScore += $value;
            }

            foreach ($row['age'] as $key => $score) {
                $spreadsheet->getActiveSheet()->setCellValue(
                    $ageCellsX[$key] . $this->lastFilledOutCellY,
                    $score
                );

                $agesTotal += $score;
                $total[$key] += $value;
            }


            $total['Total'] += $totalScore;
            $total['AgeTotal'] += $totalScore;
            $spreadsheet->getActiveSheet()->setCellValue('J' . $this->lastFilledOutCellY, $totalScore);
            $spreadsheet->getActiveSheet()->setCellValue('R' . $this->lastFilledOutCellY, $agesTotal);
        }
        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, 'GRAND TOTAL');
        $spreadsheet->getActiveSheet()->getStyle('A' . $this->lastFilledOutCellY)->getFont()->setBold(true);
        $spreadsheet->getActiveSheet()->setCellValue('B' . $this->lastFilledOutCellY, $total['Post Graduate']);
        $spreadsheet->getActiveSheet()->setCellValue('C' . $this->lastFilledOutCellY, $total['College Graduate']);
        $spreadsheet->getActiveSheet()->setCellValue('D' . $this->lastFilledOutCellY, $total['College Level']);
        $spreadsheet->getActiveSheet()->setCellValue('E' . $this->lastFilledOutCellY, $total['Vocational']);
        $spreadsheet->getActiveSheet()->setCellValue('F' . $this->lastFilledOutCellY, $total['HS Graduate']);
        $spreadsheet->getActiveSheet()->setCellValue('G' . $this->lastFilledOutCellY, $total['HS Level']);
        $spreadsheet->getActiveSheet()->setCellValue('H' . $this->lastFilledOutCellY, $total['Elementary']);
        $spreadsheet->getActiveSheet()->setCellValue('I' . $this->lastFilledOutCellY, $total['Not Indicated']);
        $spreadsheet->getActiveSheet()->setCellValue('J' . $this->lastFilledOutCellY, $total['Total']);
        $spreadsheet->getActiveSheet()->setCellValue('K' . $this->lastFilledOutCellY, $total['15-24']);
        $spreadsheet->getActiveSheet()->setCellValue('L' . $this->lastFilledOutCellY, $total['25-34']);
        $spreadsheet->getActiveSheet()->setCellValue('M' . $this->lastFilledOutCellY, $total['35-44']);
        $spreadsheet->getActiveSheet()->setCellValue('N' . $this->lastFilledOutCellY, $total['45-54']);
        $spreadsheet->getActiveSheet()->setCellValue('O' . $this->lastFilledOutCellY, $total['55-64']);
        $spreadsheet->getActiveSheet()->setCellValue('P' . $this->lastFilledOutCellY, $total[65]);
        $spreadsheet->getActiveSheet()->setCellValue('Q' . $this->lastFilledOutCellY, $total[0]);
        $spreadsheet->getActiveSheet()->setCellValue('R' . $this->lastFilledOutCellY, $total['AgeTotal']);

        $spreadsheet->getActiveSheet()->getStyle('A10:R' . $this->lastFilledOutCellY)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('A10:R' . $this->lastFilledOutCellY)
            ->getAlignment()->setHorizontal('center');
        $spreadsheet->getActiveSheet()->getStyle('A10:R' . $this->lastFilledOutCellY)
            ->getAlignment()->setVertical('center');

        return $spreadsheet;
    }

    public function header(): Spreadsheet
    {
        return $this->prepare();
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
            'B5' => 'EDUCATIONAL BACKGROUND',
            'K5' => 'AGE',
            'B6' => 'Post Graduate',
            'C6' => 'College Graduate',
            'D6' => 'College Level',
            'E6' => 'Vocational',
            'F6' => 'HS Graduate',
            'G6' => 'HS Level',
            'H6' => 'Elementary',
            'I6' => 'Not Indicated',
            'J6' => 'Total',
            'K6' => '15-24 years old',
            'L6' => '25-34 years old',
            'M6' => '35-44 years old',
            'N6' => '45-54 years old',
            'O6' => '55-6 years old',
            'P6' => '65 years old and Above',
            'Q6' => 'Not Indicated',
            'R6' => 'Total'
        ];
        $mergesCoordinates = [
            'A2:R2', 'A3:R3', 'A5:A10', 'B5:J5', 'K5:R5', 'B6:B10', 'C6:C10', 'D6:D10', 'E6:E10', 'F6:F10', 'G6:G10',
            'H6:H10', 'I6:I10', 'J6:J10', 'K6:K10', 'L6:L10', 'M6:M10', 'N6:N10', 'O6:O10', 'P6:P10', 'Q6:Q10', 'R6:R10'
        ];
        $boldCoordinates = ['A1:R10'];
        $verticalAlignedCoordinates = ['A1:R10' => 'center'];
        $horizontalAlignedCoordinates = ['A1:R10' => 'center'];
        $adjustedColumnWidthCoordinates = ['A' => 40, 'K' => 15, 'R' => 15];
        $outlineBorderThinCoordinates = [
            'A5:A10', 'B5:K5', 'J5:R5', 'B6:B10', 'C6:C10', 'D6:D10', 'E6:E10', 'F6:F10', 'G6:G10', 'H6:H10',
            'I6:I10', 'J6:J10', 'K6:K10', 'L6:L10', 'M6:M10', 'N6:N10', 'O6:O10', 'P6:P10', 'Q6:Q10', 'R6:R10'
        ];
        $rotateTextCoordinates = [
            'B6:B10', 'C6:C10', 'D6:D10', 'E6:E10', 'F6:F10', 'G6:G10', 'H6:H10', 'I6:I10', 'J6:J10',
            'K6:K10', 'L6:L10', 'M6:M10', 'N6:N10', 'O6:O10', 'P6:P10', 'Q6:Q10', 'R6:R10'
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
            $spreadsheet->getActiveSheet()->getStyle($coordinate)
                ->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        }

        foreach ($rotateTextCoordinates as $coordinate) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getAlignment()->setTextRotation(90);
        }

        $spreadsheet->getActiveSheet()->getRowDimension(5)->setRowHeight(20);
        $spreadsheet->getActiveSheet()->getRowDimension(10)->setRowHeight(60);
        $spreadsheet->getActiveSheet()->getStyle('B5:R10')->getFont()->setSize(8);

        return $spreadsheet;
    }

    private function getData(array $data): array
    {
        $result = $this->service->getConsolidatedSocioDemographic($data['region_id']);

        return ['rows' => $result['data'] ?? []];
    }
}