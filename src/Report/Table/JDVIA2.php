<?php

declare(strict_types=1);

namespace App\Report\Table;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class JDVIA2 implements Form
{
    private const TABLE_NAME = "JDVIA2";
    
    public function __construct(
        private int   $lastFilledOutCellY = 4,
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
        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, 'I.  SPECIAL ASSIGNMENT');
        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, '    (Committee memberships)');
        $spreadsheet = $this->plotSpecialAssignments($this->data['rows']['SPECIAL_ASSIGNMENT'], $spreadsheet);

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, 'II.  MISCELLANEOUS ACTIVITIES');
        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, '    (e.g.  Attendance to Court Hearings,');
        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, '      etc.)');
        $this->lastFilledOutCellY++;

        $spreadsheet = $this->plot($this->data['rows']['MISCELLANEOUS_ACTIVITIES'], $spreadsheet);

        $spreadsheet->getActiveSheet()->getStyle('A5:E' . $this->lastFilledOutCellY)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('A5:E' . $this->lastFilledOutCellY)->getAlignment()->setHorizontal('center');
        $spreadsheet->getActiveSheet()->getStyle('A5:E' . $this->lastFilledOutCellY)->getAlignment()->setVertical('center');

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

            'b3' => 'ACTIVITY',
            'b4' => '(2)',

            'c3' => 'DATE/ VENUE',
            'c4' => '(3)',

            'd3' => 'PERSONNEL  INVOLVED',
            'd4' => '(4)',
            'e3' => 'REMARKS',
            'e4' => '(5)',

        ];
        $boldCoordinates = ['a1','e1','a4','b4','c4','d4','e4',];
        $verticalAlignedCoordinates = ['B6:E27' => 'center', 'A3:A4' => 'center'];
        $horizontalAlignedCoordinates = ['B6:E27' => 'center', 'A3:A4' => 'center'];
        $adjustedColumnWidthCoordinates = [
            'A' => 40, 'B' => 35, 'C' => 40, 'D' => 25, 'E' => 25, 'F' => 15, 'G' => 15, 'H' => 15, 'I' => 15, 'J' => 15, 'K' => 27, 'L' => 20, 'M' => 30, 'N' => 30, 'O' => 20, 'P' => 20, 'Q' => 5, 'R' => 5, 'S' => 5, 'T' => 20,
        ];
        $outlineBorderThinCoordinates = [
            'A3:A4', 'B3:B4', 'c3:c4', 'd3:d4', 'e3:e4',
        ];

        foreach ($textAndCoordinates as $coordinate=>$text) {
            $spreadsheet->getActiveSheet()->setCellValue($coordinate, $text);
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

    private function plotSpecialAssignments(array $rows, Spreadsheet $spreadsheet): Spreadsheet
    {
        $this->lastFilledOutCellY++;
        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, '   a.   National');
        $spreadsheet = $this->plot($rows['national'], $spreadsheet);
        $this->lastFilledOutCellY++;

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, '    b.   Regional');
        $spreadsheet = $this->plot($rows['regional'], $spreadsheet);
        $this->lastFilledOutCellY++;

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, '   c.   Field Office');
        $spreadsheet = $this->plot($rows['field_office'], $spreadsheet);
        $this->lastFilledOutCellY++;

        return $spreadsheet;
    }
    private function plot(array $rows, Spreadsheet $spreadsheet): Spreadsheet
    {
        $total = [
            'activity' => 0,
            'person_involved' => 0
        ];
        foreach ($rows as $row) {
            $this->lastFilledOutCellY++;
            $spreadsheet->getActiveSheet()->setCellValue("a" . $this->lastFilledOutCellY, $row['decsription']);
            $spreadsheet->getActiveSheet()->setCellValue("b" . $this->lastFilledOutCellY, $row['activity']);
            $spreadsheet->getActiveSheet()->setCellValue("c" . $this->lastFilledOutCellY, $row['date'] . ' ' . $row['venue']);
            $spreadsheet->getActiveSheet()->setCellValue("d" . $this->lastFilledOutCellY, $row['person_involved']);
            $spreadsheet->getActiveSheet()->setCellValue("e" . $this->lastFilledOutCellY, $row['remarks']);
            $total['activity']++;
            $total['person_involved']++;
        }
        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, '                                                     Total');
        $spreadsheet->getActiveSheet()->setCellValue('B' . $this->lastFilledOutCellY, $total['activity']);
        $spreadsheet->getActiveSheet()->setCellValue('C' . $this->lastFilledOutCellY, '              TOTAL  (Headcount)');
        $spreadsheet->getActiveSheet()->setCellValue('D' . $this->lastFilledOutCellY, $total['person_involved']);
        $spreadsheet->getActiveSheet()->getStyle('A' . $this->lastFilledOutCellY .':E' . $this->lastFilledOutCellY)->getFont()->setBold(true);

        return $spreadsheet;
    }
}