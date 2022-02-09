<?php

declare(strict_types=1);

namespace App\Report\Table;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ID implements Form
{
    private const TABLE_NAME = "ID";
    
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
        $type = $this->data['type'];
        $data = [
            'VPA' => [
                'FIRST_2022' => [
                    '1' => [
                        ['Luke Skylar', 'TC', 'NCR Pasig Field Office', 'Pre-morning Meeting', 'January 7, 2022 / Brgy. Dela Paz Hall, Pasig City', 'Facilitator'],
                        ['Luke Skylar', 'VPA', 'NCR Pasig Field Office', 'Seminar',  'January 5, 2022 / Pasig City', 'First Speaker'],
                        ['Pedro Pandacan', 'RJ', 'NCR Pasig Field Office', 'Pre-morning Meeting',  'January 8, 2022 / Pasig City', 'Monitoring',],
                        ['Pedro Pandacan', 'VPA', 'NCR Pasig Field Office', 'Seminar',  'January 5, 2022 / Pasig City', 'Second Speaker',],
                        ['Marie Sumapay', 'VPA', 'NCR Pasig Field Office', 'Seminar', 'January 5, 2022 / Brgy. Dela Paz Hall, Pasig City', 'Third Speaker'],
                        ['Marie Sumapay', 'TC', 'NCR Pasig Field Office', 'Morning Meeting',  'January 11, 2022 / Pasig City', 'Facilitator'],
                        ['Renzo Melodez', 'VPA', 'NCR Pasig Field Office', 'Seminar',  'January 5, 2022 / Pasig City', 'Assistant',],
                        ['Renzo Melodez', 'TC', 'NCR Pasig Field Office', 'Individual Counseling',  'January 11, 2022 / Pasig City', 'Facilitator',],
                    ],
                ],
                'FOURTH_2021' => [
                    '1' => [
                        ['Danzo Malaypay', 'VPA', 'NCR Pasig Field Office', 'Livelihood Training', 'December 10, 2021 / Brgy. Dela Paz Hall, Pasig City', 'Master Speaker'],
                        ['Danzo Malaypay', 'RJ', 'NCR Pasig Field Office', 'Meeting the petitioners family',  'November 7, 2021 / Pasig City', 'Monitoring'],
                        ['Marichu Balonzo', 'VPA', 'NCR Pasig Field Office', 'Livelihood Training',  'December 10, 2021 / Pasig City', 'Trainer',],
                        ['Marichu Balonzo', 'RJ', 'NCR Pasig Field Office', 'Conferencing',  'November 20, 2021 / Pasig City', 'Monitoring',],
                        ['Benjo Relaza', 'VPA', 'NCR Pasig Field Office', 'Livelihood Training', 'December 10, 2021 / Brgy. Dela Paz Hall, Pasig City', 'Trainer'],
                        ['Aliya Gumara', 'VPA', 'NCR Pasig Field Office', 'Livelihood Training', 'December 10, 2021 / Pasig City', 'Trainer'],
                    ],
                ],
            ],
            'Personnel' => [
                'FIRST_2022' => [
                    '1' => [
                        ['Gerone Mabagay', 'TC', 'NCR Pasig Field Office', 'Morning Meeting', 'January 28, 2022 / Brgy. Sta. Lucia, Pasig City', 'Facilitator'],
                        ['Daniel Agbat', 'TC', 'NCR Pasig Field Office', 'Job Functions', 'January 17, 2022 / Brgy. Rosario, Pasig City', 'Facilitator'],
                        ['Troy Terono', 'RJ', 'NCR Pasig Field Office', 'Conferencing', 'February 2, 2022 /Brgy. Santolan, Pasig City', 'Monitoring',],
                    ],
                ],
                'FOURTH_2021' => [
                    '1' => [
                        ['Jeffrey Magbantay', 'RJ', 'NCR Pasig Field Office', 'Meeting the victims family', 'October 24, 2021 / Brgy. Dela Paz, Pasig City', 'Monitoring'],
                        ['Melissa Femiliano', 'TC', 'NCR Pasig Field Office', 'Pre-morning meeting', 'October 7, 2021 / Brgy. Rosario, Pasig City', 'Facilitator'],
                        ['Mariz Aguilera', 'TC', 'NCR Pasig Field Office', 'Job Functions',  'November 8, 2021 / Brgy. Dela Paz, Pasig City', 'Facilitator',],
                    ],
                ],
            ],
        ];

        $rows = $data[$type][$quarter][$fieldOffice];
        foreach ($rows as $row) {
            $this->lastFilledOutCellY++;
            $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, $row[0]);
            $spreadsheet->getActiveSheet()->setCellValue("b" . $this->lastFilledOutCellY, $row[1]);
            $spreadsheet->getActiveSheet()->setCellValue("c" . $this->lastFilledOutCellY, $row[2]);
            $spreadsheet->getActiveSheet()->setCellValue("d" . $this->lastFilledOutCellY, $row[3]);
            $spreadsheet->getActiveSheet()->setCellValue("e" . $this->lastFilledOutCellY, $row[4]);
            $spreadsheet->getActiveSheet()->setCellValue("f" . $this->lastFilledOutCellY, $row[5]);
        }
        $spreadsheet->getActiveSheet()->getStyle('A6:f' . $this->lastFilledOutCellY)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

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
            'g1' => 'PPA-PLD-FR-004', 
            'a2' => 'D.   SUPPORT TO OTHER FIELD OFFICES ON  TC, RJ, VPA, GAD, PWD, SC IMPLEMENTATION',
            'a3' => 'Name', 'b3' => 'Program', 'c3' => 'Field Office Assisted', 'd3' => 'Activity', 'e3' => 'Date/Venue', 'f3' => 'Assistance Rendered',
            'a4' => '(Personnel/ VPAs)', 'b4' => '(2)', 'c4' => '(3)', 'd4' => '(4)', 'e4' => '(5)', 'f4' => '(6)',
            'a5' => '(1)',
        ];
        $mergesCoordinates = [
        ];
        $boldCoordinates = ['g1','a2','a4','a5', 'b4', 'c4', 'd4', 'e4', 'f4',];
        $verticalAlignedCoordinates = ['a3:f15' => 'center'];
        $horizontalAlignedCoordinates = ['a3:f15' => 'center'];
        $adjustedColumnWidthCoordinates = [
            'A' => 23, 'B' => 10, 'C' => 25, 'D' => 30, 'E' => 50, 'F' => 25, 'G' => 25, 'H' => 10, 'I' => 10, 'J' => 10, 'K' => 10, 'L' => 10,
            'M' => 10, 'N' => 10, 'O' => 10, 'P' => 30, 'Q' => 10, 'R' => 10, 'S' => 10, 'T' => 10
        ];
        $outlineBorderThinCoordinates = [
            'A3:A5', 'B3:B5', 'C3:C5', 'D3:D5', 'e3:e5', 'f3:f5',
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