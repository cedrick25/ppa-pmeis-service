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
        private int   $lastFilledOutCellY = 8,
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
                    ['1.  THERAPEUTIC ', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',],
                    ['     COMMUNITY  (TC)', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',],
                    ['Implementation of PD 968', '1/15/2022 / Pasig City Hall', '', '', '', '', '', 'Snacks', '14,625', 'Pasig City LGU', '/', '', '', '', '', '', '', '', '', 'Gerone Mabagay / Troy Tereno / Melissa Femilliano / Mariza Aguilera',],
                    ['TC Session', '1/22/2022 / Brgy. Dela Paz, Pasig City', '1000', 'Daniel Agbat', '', '', '/', 'Snacks', '14,625', 'Pasig City LGU', '/', '', '', '', '', '', '', '', '', 'Gerone Mabagay / Troy Tereno / Melissa Femilliano / Mariza Aguilera',],
                    ['2. RESTORATIVE JUSTICE', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',],
                    ['RJ', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',],
                    ['None', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',],
                    ['3. VOLUNTEERISM (VPA)', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',],
                    ['None', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',],
                    ['4. GENDER AND DEVELOPMENT (GAD)', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',],
                    ['None', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',],
                    ['5. PERSONS WITH DISABILITY (PWDs) AND SENIOR CITIZENS', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',],
                    ['None', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',],
                    ['6. OTHERS', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',],
                    ['   (To include LGUs - on detail)', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',],
                    ['None', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',],
                    ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',],
                    ['', 'TOTAL', '1000', 'TOTAL', '0', '0', '1', 'TOTAL', '29,250', 'TOTAL', '2', '0', '0', 'TOTAL', '0', 'TOTAL', '0', '0', '0', '',],
                ],
            ],
            'FOURTH_2021' => [
                '1' => [
                    ['1.  THERAPEUTIC ', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',],
                    ['     COMMUNITY  (TC)', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',],
                    ['Fire Safety and Prevention Seminar', '1/22/2022 / Brgy. Dela Paz, Pasig City', '1000', 'Daniel Agbat', '', '', '/', 'Snacks', '14,625', 'Pasig City LGU', '/', '', '', '', '', '', '', '', '', 'Gerone Mabagay / Troy Tereno / Jeffrey Magbantay',],
                    ['2. RESTORATIVE JUSTICE', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',],
                    ['RJ', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',],
                    ['None', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',],
                    ['3. VOLUNTEERISM (VPA)', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',],
                    ['None', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',],
                    ['4. GENDER AND DEVELOPMENT (GAD)', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',],
                    ['None', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',],
                    ['5. PERSONS WITH DISABILITY (PWDs) AND SENIOR CITIZENS', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',],
                    ['None', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',],
                    ['6. OTHERS', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',],
                    ['   (To include LGUs - on detail)', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',],
                    ['None', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',],
                    ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',],
                    ['', 'TOTAL', '1000', 'TOTAL', '0', '0', '1', 'TOTAL', '14,625', 'TOTAL', '1', '0', '0', 'TOTAL', '0', 'TOTAL', '0', '0', '0', '',],
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
            $spreadsheet->getActiveSheet()->setCellValue("f" . $this->lastFilledOutCellY, $row[5]);
            $spreadsheet->getActiveSheet()->setCellValue("g" . $this->lastFilledOutCellY, $row[6]);
            $spreadsheet->getActiveSheet()->setCellValue("h" . $this->lastFilledOutCellY, $row[7]);
            $spreadsheet->getActiveSheet()->setCellValue("i" . $this->lastFilledOutCellY, $row[8]);
            $spreadsheet->getActiveSheet()->setCellValue("j" . $this->lastFilledOutCellY, $row[9]);
            $spreadsheet->getActiveSheet()->setCellValue("k" . $this->lastFilledOutCellY, $row[10]);
            $spreadsheet->getActiveSheet()->setCellValue("l" . $this->lastFilledOutCellY, $row[11]);
            $spreadsheet->getActiveSheet()->setCellValue("m" . $this->lastFilledOutCellY, $row[12]);
            $spreadsheet->getActiveSheet()->setCellValue("n" . $this->lastFilledOutCellY, $row[13]);
            $spreadsheet->getActiveSheet()->setCellValue("o" . $this->lastFilledOutCellY, $row[14]);
            $spreadsheet->getActiveSheet()->setCellValue("p" . $this->lastFilledOutCellY, $row[15]);
            $spreadsheet->getActiveSheet()->setCellValue("q" . $this->lastFilledOutCellY, $row[16]);
            $spreadsheet->getActiveSheet()->setCellValue("r" . $this->lastFilledOutCellY, $row[17]);
            $spreadsheet->getActiveSheet()->setCellValue("s" . $this->lastFilledOutCellY, $row[18]);
            $spreadsheet->getActiveSheet()->setCellValue("t" . $this->lastFilledOutCellY, $row[19]);
        }
        $spreadsheet->getActiveSheet()->getStyle('A6:h' . $this->lastFilledOutCellY)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

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
            'a29' => 'NOTE:  DO NOT INCLUDE RESOURCES FROM THE  REGIONAL OFFICE (THIS IS REPORTED BY RO SEPARATELY)',
            'a31' => 'NOTE:      RESOURCE MOBILIZATION is a  continuing process of developing, generating and managing funds, information, goods, services, people and institutions to provide support to program',

            'b4' => 'DATE/ VENUE',
            'b6' => '(2)',
            
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
            'A' => 57, 'B' => 40, 'C' => 17, 'D' => 15, 'E' => 5, 'F' => 5, 'G' => 5, 'H' => 30, 'I' => 30, 'J' => 30, 'K' => 5, 'L' => 5, 'M' => 5, 'N' => 30, 'O' => 20, 'P' => 20, 'Q' => 5, 'R' => 5, 'S' => 5, 'T' => 70,
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