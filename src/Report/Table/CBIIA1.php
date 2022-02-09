<?php

declare(strict_types=1);

namespace App\Report\Table;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CBIIA1 implements Form
{
    private const TABLE_NAME = "CBIIA1";
    
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

        $spreadsheet->getActiveSheet()->getStyle('A8:K23')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('d23')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('80808080');
        $spreadsheet->getActiveSheet()->getStyle('j23')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('80808080');
        $spreadsheet->getActiveSheet()->getStyle('k23')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('80808080');

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
            'a1' => 'II.  CAPABILITY BUILDING', 'l1' => 'PPA-PLD-FR-004', 
            'a3' => 'Table II.A.1 –  PERSONNEL',
            'a5' => 'Title', 'a6' => '(1)', 'a8' => 'A.  Training on  Therapeutic Community', 'a10' => 'B.  Training on  Restorative Justice', 'a12' => 'C.  Training on Volunteerism', 'a14' => 'D.  Training on GAD', 'a16' => 'E.  Other Training  Courses/ Seminars/', 'a18' => 'F.  Conferences/Conventions', 'a20' => 'G.  Staff/ Committee  Meetings with', 'a21' => 'Professional Dev"t.',
            'b5' => 'Date', 'b6' => '(2)',
            'c5' => 'No. of', 'c6' => 'Participants', 'c7' => '(3)', 
            'd5' => 'Name/s', 'd6' => '(4)',
            'e4' => 'P', 'e5' => 'W', 'e6' => 'D', 'e7' => '(5)',
            'f4' => 'S', 'f5' => 'C', 'f6' => '', 'f7' => '(6)',
            'g4' => 'Nature of Training   (7)', 'g5' => 'Managerial/ 
            Supervisory', 'h6' => 'Technical', 'I5' => 'Foundation',
            'j4' => 'No. of', 'j5' => 'Training', 'j6' => 'Hours', 'j7' => '(8)',
            'K4' => 'REMARKS', 'K7' => '(9)',  'a23' => 'TOTAL (Headcount)', 'g23' => 'TOTAL:', 
        ];
        $mergesCoordinates = [
            'G4:I4', 'G5:G7', 'H5:H7', 'I5:I7', 'K4:K6', 'A23:B23'
        ];
        $boldCoordinates = ['l1','a1','a3','b6', 'c7', 'd6', 'e7', 'f7', 'j7', 'k7', 'a6', 'a18', 'a20', 'a21',];
        $verticalAlignedCoordinates = ['b4:K47' => 'center', 'a4:a7' => 'center', 'a21' => 'center', 'a23:b23' => 'center'];
        $horizontalAlignedCoordinates = ['b4:K47' => 'center', 'a4:a7' => 'center', 'a21' => 'center', 'a23:b23' => 'right'];
        $adjustedColumnWidthCoordinates = [
            'A' => 37, 'B' => 17, 'C' => 15, 'D' => 20, 'E' => 5, 'F' => 5, 'G' => 20, 'H' => 10, 'I' => 10, 'J' => 10, 'K' => 10, 'L' => 10,
            'M' => 10, 'N' => 10,
        ];
        $outlineBorderThinCoordinates = [
            'A4:A7', 'B4:B7', 'C4:C7', 'D4:D7', 'E4:E7', 'F4:F7', 'G4:I4', 'G5:G7', 'H5:H7', 'I5:I7', 'J4:J7', 'K4:K7',
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