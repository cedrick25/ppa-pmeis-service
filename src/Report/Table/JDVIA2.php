<?php

declare(strict_types=1);

namespace App\Report\Table;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class JDVIA2 implements Form
{
    private const TABLE_NAME = "JDVIA2";
    
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

        $quarter = $this->data['quarter'];
        $fieldOffice = $this->data['field_office_id'];
        $data = [
            'FIRST_2022' => [
                '1' => [
                    ['', '', '', '', '',],
                    ['', '', '', '', '',],
                ],
            ],
            'FOURTH_2021' => [
                '1' => [
                    ['', '', '', '', '',],
                    ['', '', '', '', '',],
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
        }
        $spreadsheet->getActiveSheet()->getStyle('a5:e' . $this->lastFilledOutCellY)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

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
            'a1' => 'Table VI.A.2  SPECIAL ASSIGNMENTS/ MISCELLANEOUS ACTIVITIES',
            'e1' => 'PPA-PLD-FR-004',
            'a3' => 'DESCRIPTION', 
            'a4' => '(1)',

            'a5' => 'I.  SPECIAL ASSIGNMENT ',
            'a6' => '    (Committee memberships)',
            'a8' => 'II.  MISCELLANEOUS ACTIVITIES',
            'a9' => '    (e.g.  Attendance to Court Hearings, etc)',

            'b3' => 'ACTIVITY',
            'b4' => '(2)',

            'c3' => 'DATE/ VENUE',
            'c4' => '(3)',

            'd3' => 'PERSONNEL  INVOLVED',
            'd4' => '(4)',
            'e3' => 'REMARKS',
            'e4' => '(5)',

        ];
        $mergesCoordinates = [
            // 'C4:D5', 'D7:D8', 'E4:K4', 'E5:F5', 'G5:I5', 'L4:L8', 'J5:J8', 'K5:K8', 'B4:B6', 'C6:C8', 'D6:D8', 'E6:E8', 'G6:G8', 'H6:H8', 'I6:I8', 'J5:J8',

        ];
        $boldCoordinates = ['a1','e1','a4','b4','c4','d4','e4',];
        $verticalAlignedCoordinates = ['B3:E11' => 'center', 'A3:A4' => 'center'];
        $horizontalAlignedCoordinates = ['B3:E11' => 'center', 'A3:A4' => 'center'];
        $adjustedColumnWidthCoordinates = [
            'A' => 40, 'B' => 25, 'C' => 25, 'D' => 25, 'E' => 25, 'F' => 15, 'G' => 15, 'H' => 15, 'I' => 15, 'J' => 15, 'K' => 27, 'L' => 20, 'M' => 30, 'N' => 30, 'O' => 20, 'P' => 20, 'Q' => 5, 'R' => 5, 'S' => 5, 'T' => 20,
        ];
        $outlineBorderThinCoordinates = [
            'A3:A4', 'B3:B4', 'c3:c4', 'd3:d4', 'e3:e4',
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