<?php

declare(strict_types=1);

namespace App\Report\Table;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SMIIIA1 implements Form
{
    private const TABLE_NAME = "SMIIIA1";
    
    public function __construct(
        private int   $lastFilledOutCellY = 6,
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
                    ['1.  Fora, symposia  (Planned, coordinated, organized with', '', '', '', '', '', ''],
                    ['     program and topics, personnel/ VPAs with specific roles, letter', '', '', '', '', '', ''],
                    ['     request (if applicable), with target number of participants and', '', '', '', '', '', ''],
                    ['     content limited to programs and services of the Agency.)', '', '', '', '', '', ''],
                    ['The Work of the PPA', 'January 22, 2022 / ', '', '', 'Gerone Mabagay / Facilitator ', '', ''],
                    ['', 'Brgy. Dela Paz Pasig City', '2', '', 'Daniel Agbat / Facilitator', '', ''],
                ],
            ],
            'FOURTH_2021' => [
                '1' => [
                    ['1.  Fora, symposia  (Planned, coordinated, organized with', '', '', '', '', '', ''],
                    ['     program and topics, personnel/ VPAs with specific roles, letter', '', '', '', '', '', ''],
                    ['     request (if applicable), with target number of participants and', '', '', '', '', '', ''],
                    ['     content limited to programs and services of the Agency.)', '', '', '', '', '', ''],
                    ['', '', '', '', '', '', ''],
                    ['', '', '', '', '', '', ''],
                    ['', '', '', '', '', '', ''],
                    ['', '', '', '', '', '', ''],
                    ['Activity', 'Date / Venue', 'Particulars   (3)', '', 'Activity (4)', '', 'REMARKS  (5)'],
                    ['(1)', '(2)', '', '', 'Personnel/ Role', 'VPA/ Role', '(Include  number of primers distributed)'],
                    ['2.  Publication/ Press Releases/ TV / Radio Interviews/ Guestings', '', '', '', '', '', ''],
                    ['GMA News 24/7', 'December 7, 2021 / Pasig City Hall', '', '', 'Mellisa Femiliano / Interviewee', '', ''],
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
        }

        $this->lastFilledOutCellY++;
        $this->lastFilledOutCellY++;

        $spreadsheet->getActiveSheet()->getStyle('A7:g13')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('A17:g20')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
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
            'a1' => 'III.   SOCIAL MARKETING',
            'a3' => 'Table  III.A.1  -  INFORMATION DISSEMINATION',
            'a4' => 'Activity', 
            'a5' => '(1)',
            'a7' => '1.  Fora, symposia  (Planned, coordinated, organized with',
            'a8' => '     program and topics, personnel/ VPAs with specific roles, letter',
            'a9' => '     request (if applicable), with target number of participants and', 
            'a10' => '     content limited to programs and services of the Agency.)',
            'a15' => 'Activity',
            'a16' => '(1)',
            'a17' => '2.  Publication/ Press Releases/ TV / Radio Interviews/ Guestings',
            'b4' => 'Date / Venue', 
            'b5' => '(2)',
            'b15' => 'Date / Venue', 
            'b16' => '(2)',
            'c4' => 'Participants   (3)', 
            'c5' => 'No.',
            'c15' => 'Particulars',
            'c16' => '(3)',
            'd5' => 'Type',
            'e4' => 'Activity (4)', 
            'e5' => 'Personnel/ Role',
            'e15' => 'Activity (4)',
            'e16' => 'Personnel/ Role',
            'f5' => 'VPA/ Role',
            'f16' => 'VPA/ Role',
            'g4' => 'REMARKS  (5)',
            'g5' => '(Include  number of primers',
            'g8' => 'distributed)',
            'g15' => 'REMARKS  (5)',
            'g16' => '(Include  number of primers distributed)',
            'h1' => 'PPA-PLD-FR-004',
        ];
        $mergesCoordinates = [
            'C4:D4', 'C5:C6', 'D5:D6', 'E4:F4', 'E5:E6', 'F5:F6',
            'C15:D16', 'E15:F15',
        ];
        $boldCoordinates = ['a1','a3', 'h1'];
        $verticalAlignedCoordinates = ['B4:G20' => 'center', 'A4:A6' => 'center', 'A15:A16' => 'center'];
        $horizontalAlignedCoordinates = ['B4:G20' => 'center', 'A4:A6' => 'center', 'A15:A16' => 'center'];
        $adjustedColumnWidthCoordinates = [
            'A' => 58, 'B' => 35, 'C' => 10, 'D' => 10, 'E' => 27, 'F' => 20, 'G' => 37, 'H' => 20,
        ];
        $outlineBorderThinCoordinates = [
            'A4:A6', 'B4:B6', 'C4:D4', 'C5:C6', 'D5:D6', 'E4:F4', 'E5:E6', 'F5:F6', 'G4:G6',
            'A15:A16', 'B15:B16', 'C15:D16', 'E15:F15', 'G15:G16', 'E16:E16', 'F16:F16',
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