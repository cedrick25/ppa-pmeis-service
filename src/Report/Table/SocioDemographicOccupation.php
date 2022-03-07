<?php

declare(strict_types=1);

namespace App\Report\Table;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SocioDemographicOccupation implements Form
{
    private const TABLE_NAME = "SocioDemographicOccupation";
    
    public function __construct(
        private int   $lastFilledOutCellY = 10,
        private array $data = [],
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
        $this->data = $data;

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
            'occupation' => [
                'Brgy. Official' => 'B',
                'PNP' => 'C',
                'Government Employee' => 'D',
                'White Collar' => 'E',
                'Blue Collar' => 'F',
                'Self-Employed' => 'G',
                'Retirees' => 'H',
                'Unemployed' => 'I',
                'Students' => 'J',
                'Others' => 'K',
                'Not Indicated' => 'L'
            ]
        ];

        $total = [
            'Brgy. Official' => 0,
            'PNP' => 0,
            'Government Employee' => 0,
            'White Collar' => 0,
            'Blue Collar' => 0,
            'Self-Employed' => 0,
            'Retirees' => 0,
            'Unemployed' => 0,
            'Students' => 0,
            'Others' => 0,
            'Not Indicated' => 0,
            'Total' => 0
        ];

        foreach ($this->data['rows'] as $region=>$row) {
            $this->lastFilledOutCellY++;
            $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, $region);

            $totalScore = 0;
            foreach ($row['occupation'] as $occupation=>$value) {
                $cellColumn = $coordinates['occupation'][$occupation];
                $total[$occupation] += $value;

                $spreadsheet->getActiveSheet()->setCellValue($cellColumn . $this->lastFilledOutCellY, $value);
                $totalScore += $value;
            }

            $total['Total'] += $totalScore;
            $spreadsheet->getActiveSheet()->setCellValue('M' . $this->lastFilledOutCellY, $totalScore);
        }

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, 'GRAND TOTALS');
        $spreadsheet->getActiveSheet()->getStyle('A' . $this->lastFilledOutCellY)->getFont()->setBold(true);
        $spreadsheet->getActiveSheet()->setCellValue('B' . $this->lastFilledOutCellY, $total['Brgy. Official']);
        $spreadsheet->getActiveSheet()->setCellValue('C' . $this->lastFilledOutCellY, $total['PNP']);
        $spreadsheet->getActiveSheet()->setCellValue('D' . $this->lastFilledOutCellY, $total['Government Employee']);
        $spreadsheet->getActiveSheet()->setCellValue('E' . $this->lastFilledOutCellY, $total['White Collar']);
        $spreadsheet->getActiveSheet()->setCellValue('F' . $this->lastFilledOutCellY, $total['Blue Collar']);
        $spreadsheet->getActiveSheet()->setCellValue('G' . $this->lastFilledOutCellY, $total['Self-Employed']);
        $spreadsheet->getActiveSheet()->setCellValue('H' . $this->lastFilledOutCellY, $total['Retirees']);
        $spreadsheet->getActiveSheet()->setCellValue('I' . $this->lastFilledOutCellY, $total['Unemployed']);
        $spreadsheet->getActiveSheet()->setCellValue('J' . $this->lastFilledOutCellY, $total['Students']);
        $spreadsheet->getActiveSheet()->setCellValue('K' . $this->lastFilledOutCellY, $total['Others']);
        $spreadsheet->getActiveSheet()->setCellValue('L' . $this->lastFilledOutCellY, $total['Not Indicated']);
        $spreadsheet->getActiveSheet()->setCellValue('M' . $this->lastFilledOutCellY, $total['Total']);

        $spreadsheet->getActiveSheet()->getStyle('A10:M' . $this->lastFilledOutCellY)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('A10:M' . $this->lastFilledOutCellY)->getAlignment()->setHorizontal('center');
        $spreadsheet->getActiveSheet()->getStyle('A10:M' . $this->lastFilledOutCellY)->getAlignment()->setVertical('center');

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
            'N1' => 'PPA-CSD-FR-011-00',
            'A2' => 'VPA SOCIO-DEMOGRAPHIC REPORT',
            'A3' => 'As of________20__',
            'A5' => 'Field Office',
            'B5' => 'OCCUPATION',
            'B6' => 'Goverment',
            'E6' => 'Private',
            'H6' => 'Retirees',
            'I6' => 'Unemployed',
            'J6' => 'Students',
            'K6' => 'Others',
            'L6' => 'Not Indicated',
            'M6' => 'Total',
            'B7' => 'Brgy. Official',
            'C7' => 'PNP',
            'D7' => 'Government Employee',
            'E7' => 'White Collar',
            'F7' => 'Blue Collar',
            'G7' => 'Self-Employed'
        ];
        $mergesCoordinates = [
            'A2:M2', 'A3:M3', 'A5:A10', 'B5:M5', 'B6:D6', 'D6:F6', 'G6:G10', 'H6:H10', 'I6:I10', 'J6:J10',
            'K6:K10', 'L6:L10', 'M6:M10', 'B7:B10', 'C7:C10', 'D7:D10', 'E7:E10', 'F7:F10', 'G7:G10'
        ];
        $boldCoordinates = ['A1:M10'];
        $verticalAlignedCoordinates = ['A1:M10' => 'center'];
        $horizontalAlignedCoordinates = ['A1:M10' => 'center'];
        $adjustedColumnWidthCoordinates = ['A' => 40];
        $outlineBorderThinCoordinates = [
            'A5:A10', 'B5:M5', 'B6:D6', 'D6:F6', 'G6:G10', 'H6:H10', 'I6:I10', 'J6:J10',
            'K6:K10', 'L6:L10', 'M6:M10', 'B7:B10', 'C7:C10', 'D7:D10', 'E7:E10', 'F7:F10', 'G7:G10'
        ];
        $rotateTextCoordinates = [
            'H6:H10', 'I6:I10', 'J6:J10', 'K6:K10', 'L6:L10', 'M6:M10',
            'B7:B10', 'C7:C10', 'D7:D10', 'E7:E10', 'F7:F10', 'G7:G10'
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
}