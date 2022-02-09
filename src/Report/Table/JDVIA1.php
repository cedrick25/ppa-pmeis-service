<?php

declare(strict_types=1);

namespace App\Report\Table;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class JDVIA1 implements Form
{
    private const TABLE_NAME = "JDVIA1";
    
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
                    ['01/07/2022', 'BJMP Pasig City', '/', '', '2', '', '', '', '', '', '', 'Troy Terono', ''],
                    ['01/15/2022', 'BJMP Pasig City', '/', '', '1', '', '', '', '', '', '', 'Troy Terono', ''],
                    ['', '', '', '', '', '', '', '', '', '', '', '', ''],
                    ['', '', '', '', '', '', '', '', '', '', '', '', ''],
                    ['', 'Total', '', '', '3', '', '', '', '', '', '', '', ''],
                ],
            ],
            'FOURTH_2021' => [
                '1' => [
                    ['11/28/2021', 'BJMP Pasig City', '/', '', '2', '', '', '', '', '', '', 'Jeffrey Mgbantay', ''],
                    ['12/05/2021', 'BJMP Pasig City', '/', '', '2', '', '', '', '', '', '', 'Troy Terono', ''],
                    ['', '', '', '', '', '', '', '', '', '', '', '', ''],
                    ['', '', '', '', '', '', '', '', '', '', '', '', ''],
                    ['', 'Total', '', '', '4', '', '', '', '', '', '', '', ''],
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
        }
        $spreadsheet->getActiveSheet()->getStyle('A9:m' . $this->lastFilledOutCellY)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

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
            'a1' => 'VI.  SUPPORT FUNCTION', 'm1' => 'PPA-PLD-FR-004',
            'a3' => 'Table VI.A.1  JAIL DECONGESTION SERVICES/ ACTIVITIES ',
            'a5' => 'Date', 
            'a6' => '(1)',

            'b4' => 'Name and Address of Jail/Office Assisted',
            'b7' => '(2)',
            
            'c4' => 'Venue (3)',
            'c6' => 'Jail',

            'd6' => 'Office',

            'e4' => 'Activities/Number of Inmates or Detainees Assisted (4)',
            'e5' => 'No. of Intake Interview',
            'e6' => 'Probation',
            'f6' => 'Pre-Parole',
            'f7' => 'Executive',
            'f8' => 'Clemency',

            'g5' => 'No. of Referrals',
            'g6' => 'PAO',
            'h6' => 'Prosecution',
            'i6' => 'Others',
            'j5' => 'MSEC / GCTA',
            'k5' => 'Release on Recognizance',
            'l4' => 'Person Responsible (5)',
            'm4' => '(6)',
            'm5' => 'REMARKS',
            'm6' => 'To include issues and problems ',
            'm7' => 'encountered and other ',
            'm8' => 'relevant information',

        ];
        $mergesCoordinates = [
            'C4:D5', 'D7:D8', 'E4:K4', 'E5:F5', 'G5:I5', 'L4:L8', 'J5:J8', 'K5:K8', 'B4:B6', 'C6:C8', 'D6:D8', 'E6:E8', 'G6:G8', 'H6:H8', 'I6:I8', 'J5:J8',

        ];
        $boldCoordinates = ['a1','a3','m1',];
        $verticalAlignedCoordinates = ['B3:T30' => 'center', 'A3:A8' => 'center'];
        $horizontalAlignedCoordinates = ['B3:T30' => 'center', 'A3:A8' => 'center'];
        $adjustedColumnWidthCoordinates = [
            'A' => 20, 'B' => 40, 'C' => 20, 'D' => 20, 'E' => 15, 'F' => 15, 'G' => 15, 'H' => 15, 'I' => 15, 'J' => 15, 'K' => 27, 'L' => 20, 'M' => 30, 'N' => 30, 'O' => 20, 'P' => 20, 'Q' => 5, 'R' => 5, 'S' => 5, 'T' => 20,
        ];
        $outlineBorderThinCoordinates = [
            'A4:A8', 'B4:B8', 'C6:C8', 'D6:D8', 'D7:D8', 'E6:E8', 'F6:F8', 'G6:G8', 'H6:H8', 'I6:I8', 'J5:J8', 'K5:K8', 'M4:M8', 'E4:K4', 'E5:F5', 'G5:I5', 'c4:d5', 'L4:L8'
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