<?php

declare(strict_types=1);

namespace App\Report\Table;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RMIV implements Form
{
    private const TABLE_NAME = "RMIV";
    
    public function __construct(
        private int   $lastFilledOutCellY = 20,
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

        $spreadsheet->getActiveSheet()->getStyle('A9:T27')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        // $spreadsheet->getActiveSheet()->getStyle('D5:G5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('t27')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('80808080');
        // $spreadsheet->getActiveSheet()->getStyle('g23:j23')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('80808080');

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
            'a1' => 'TABLE IV.  RESOURCE MOBILIZATION', 't1' => 'PPA-PLD-FR-004',
            'a4' => 'ACTIVITIES UNDERTAKEN/ ',
            'a5' => 'RENDERED FOR WHICH',
            'a6' => 'RESOURCES/ ASSISTANCE', 
            'a7' => 'WERE UTILIZED',
            'a8' => '(1)',
            'a9' => '1.  THERAPEUTIC ',
            'a10' => '     COMMUNITY  (TC)',
            'a12' => '2.  RESTORATIVE JUSTICE',
            'a13' => '      (RJ)',
            'a15' => '3.  VOLUNTEERISM  (VPA)',
            'a17' => '4.   GENDER AND ',
            'a18' => '       DEVELOPMENT  (GAD)',
            'a20' => '5.  PERSONS WITH ',
            'a21' => '     DISABILITY (PWDs) and ',
            'a22' => '     SENIOR CITIZENS',
            'a24' => '6.  OTHERS ',
            'a25' => '     (To include LGUs - on detail)',
            'a29' => 'NOTE:  DO NOT INCLUDE RESOURCES FROM THE  REGIONAL OFFICE (THIS IS REPORTED BY RO SEPARATELY)',
            'a31' => 'NOTE:      RESOURCE MOBILIZATION is a  continuing process of developing, generating and managing funds, information, goods, services, people and institutions to provide support to program',

            'b4' => 'DATE/ VENUE',
            'b6' => '(2)',
            'b27' => 'TOTAL',
            
            'c3' => 'RESOURCES SECURED/ UTILIZED (3)',
            'c4' => 'CASH',
            'c6' => 'AMOUNT',

            'd5' => 'SOURCE',
            'd6' => 'NAME',

            'e6' => 'GO',
            'f6' => 'NGO',
            'g6' => 'IND',

            'h4' => 'SUPPLIES/ MATERIALS/ GOODS/ OTHERS',
            'h5' => 'PARTICULARS  (Qty. and type)',
            'i5' => 'ESTIMATED AMOUNT',

            'j5' => 'SOURCE',
            'j6' => 'NAME',

            'k6' => 'GO',
            'l6' => 'NGO',
            'm6' => 'IND',

            'n4' => 'TECHNICAL ASSISTANCE',
            'n5' => 'PARTICULARS (Qty. and Type)',

            'o5' => 'ESTIMATED AMOUNT',
            'p5' => 'SOURCE',
            'p6' => 'NAME',

            'q6' => 'GO',
            'r6' => 'NGO',
            's6' => 'IND',

            't3' => '',
            't4' => '',
            't5' => 'RESOURCES ',
            't6' => 'SECURED OR',
            't7' => 'FACILITATED BY',
            't8' => '(4)',

        ];
        $mergesCoordinates = [
            'C3:S3', 'C4:G4', 'D5:G5', 'H4:M4', 'J5:M5', 'N4:S4', 'P5:S5', 'C3:S3', 'H4:M4', 
        ];
        $boldCoordinates = ['t1','a1','A8','B6','C6','C4','C3','H4','H5','I5','N4','N5','O5','T8', 'a29', 'a31'];
        $verticalAlignedCoordinates = ['B3:T30' => 'center', 'A3:A8' => 'center'];
        $horizontalAlignedCoordinates = ['B3:T30' => 'center', 'A3:A8' => 'center'];
        $adjustedColumnWidthCoordinates = [
            'A' => 40, 'B' => 20, 'C' => 20, 'D' => 15, 'E' => 5, 'F' => 5, 'G' => 5, 'H' => 30, 'I' => 30, 'J' => 30, 'K' => 5, 'L' => 5, 'M' => 5, 'N' => 30, 'O' => 20, 'P' => 20, 'Q' => 5, 'R' => 5, 'S' => 5, 'T' => 20,
        ];
        $outlineBorderThinCoordinates = [
            'A3:A8', 'B3:B8', 'C5:C8', 'D6:D8', 'E6:E8', 'F6:F8', 'G6:G8', 'H5:H8', 'I5:I8', 'J6:J8', 'K6:K8', 'L6:L8', 'm6:m8',
            'N5:N8', 'O5:O8', 'P6:P8', 'Q6:Q8', 'R6:R8', 's6:s8', 'P5:S5', 'C3:S3', 'H4:M4', 'D5:G5', 'T3:T8'
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