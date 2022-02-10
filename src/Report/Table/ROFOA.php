<?php

declare(strict_types=1);

namespace App\Report\Table;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ROFOA implements Form
{
    private const TABLE_NAME = "ROFOA";
    
    public function __construct(
        private int   $lastFilledOutCellY = 5,
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

    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();

        $quarter = $this->data['quarter'];
        $fieldOffice = $this->data['field_office_id'];
        $data = [
            'FIRST_2022' => [
                '1' => [
                    ['I.  ACTIVITIES BY FIELD OFFICES', '', '', '', '', '', '',],
                    ['  January 7, 2022', 'Pasig City Field Office', 'Meals', '14000', '2500', '16500', '',],
                    ['  January 15, 2022', 'Pasig City Field Office', 'Laptop', '30000', '5000', '35000', '',],
                    ['  February 2, 2022', 'Pasig City Field Office', 'Projector', '10000', '5000', '15000', '',],
                    ['', '', '', '', '', '', '',],
                    ['II.  ACTIVITIES BY CLUSTER/REGIONAL OFFICE', '', '', '', '', '', '',],
                    ['', '', '', '', '', '', '',],
                ],
            ],
        ];
        $rows = $data[$quarter][$fieldOffice];
        foreach ($rows as $row) {
            $this->lastFilledOutCellY++;
            $spreadsheet->getActiveSheet()->setCellValue("a" . $this->lastFilledOutCellY, $row[0]);
            $spreadsheet->getActiveSheet()->setCellValue("b" . $this->lastFilledOutCellY, $row[1]);
            $spreadsheet->getActiveSheet()->setCellValue("c" . $this->lastFilledOutCellY, $row[2]);
            $spreadsheet->getActiveSheet()->setCellValue("d" . $this->lastFilledOutCellY, $row[3]);
            $spreadsheet->getActiveSheet()->setCellValue("e" . $this->lastFilledOutCellY, $row[4]);
            $spreadsheet->getActiveSheet()->setCellValue("F" . $this->lastFilledOutCellY, $row[5]);
            $spreadsheet->getActiveSheet()->setCellValue("G" . $this->lastFilledOutCellY, $row[6]);
        }
        $spreadsheet->getActiveSheet()->getStyle('a6:G' . $this->lastFilledOutCellY)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

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
            'B1' => 'FINANCIAL / MATERIAL SUPPORT OF REGIONAL OFFICE TO FIELD OFFICES (To be prepared by the Regional Office)',
            'b2' => '_______________Quarter 20______________',
            'a4' => 'A. TC ACTIVITIES', 
            'a5' => 'DATE',
            'b5' => 'FIELD OFFICE(S)',
            'c5' => 'PARTICULARS',
            'd5' => 'AMOUNT',
            'e5' => 'ATTRIBUTABLE COST',
            'f5' => 'TOTAL AMOUNT',
            'g5' => 'REMARKS',
        ];
        $mergesCoordinates = [
            'B1:G1','B2:G2',

        ];
        $boldCoordinates = ['B1','B2','a4'];
        $verticalAlignedCoordinates = ['B2:G10' => 'center', 'B1:B1' => 'center', 'A5:A5' => 'center'];
        $horizontalAlignedCoordinates = ['B2:G10' => 'center', 'B1:B1' => 'center', 'A5:A5' => 'center'];
        $adjustedColumnWidthCoordinates = [
            'A' => 40, 'B' => 35, 'C' => 40, 'D' => 25, 'E' => 25, 'F' => 15, 'G' => 15, 'H' => 15, 'I' => 15, 'J' => 15, 'K' => 27, 'L' => 20, 'M' => 30, 'N' => 30, 'O' => 20, 'P' => 20, 'Q' => 5, 'R' => 5, 'S' => 5, 'T' => 20,
        ];
        $outlineBorderThinCoordinates = [
            'A5:A5', 'B5:B5', 'C5:C5', 'D5:D5', 'E5:E5', 'F5:F5', 'G5:G5',
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
}