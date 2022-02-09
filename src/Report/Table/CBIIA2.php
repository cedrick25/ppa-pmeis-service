<?php

declare(strict_types=1);

namespace App\Report\Table;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CBIIA2 implements Form
{
    private const TABLE_NAME = "CBIIA2";
    
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

        $spreadsheet->getActiveSheet()->getStyle('A8:J23')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('d23')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('80808080');
        $spreadsheet->getActiveSheet()->getStyle('g23:j23')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('80808080');

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
            'a3' => 'Table II.A.2 –  VPAs',
            'a5' => 'Title', 'a6' => '(1)', 'a8' => 'A.  Training on  Therapeutic Community', 'a10' => 'B.  Training on  Restorative Justice', 'a12' => 'C.  Training on Volunteerism', 'a14' => 'D.  Training on GAD', 'a16' => 'E.  Other Training  Courses/ Seminars/', 'a17' => '     Fora/Symposia', 'a19' => 'F.  Conferences/ Conventions/ Congress', 'a21' => 'G.  VPA Meetings/ Assemblies',
            'b5' => 'Date', 'b6' => '(2)',
            'c5' => 'No. of', 'c6' => 'Participants', 'c7' => '(3)', 
            'd5' => 'Name/s', 'd6' => '(4)',
            'e4' => 'P', 'e5' => 'W', 'e6' => 'D', 'e7' => '(5)',
            'f4' => 'S', 'f5' => 'C', 'f6' => '', 'f7' => '(6)',
            'g4' => 'Trainings Conducted   (7)', 'g5' => 'In-House', 'g6' => '(Indicate if CO/
             RO/ FO)', 'h5' => 'Out-House', 'h6' => '(Indicate Name of conducting 
             Agency/ Organization)',
            'i4' => 'No. of', 'i5' => 'Training', 'i6' => 'Hours', 'i7' => '(8)',
            'j4' => 'REMARKS', 'j7' => '(9)',  'a23' => 'TOTAL (Headcount)',  'a24' => '    NOTE:      IF PAX ARE MORE THAN FIVE (5), ATTACH ATTENDANCE SHEET OR LIST OF PARTICIPANTS'
        ];
        $mergesCoordinates = [
            'G4:h4', 'G6:G7', 'H6:H7', 'J4:J6', 'A23:B23'
        ];
        $boldCoordinates = ['l1','a1','a3','b6', 'c7', 'd6', 'e7', 'f7', 'i7','j7', 'a6', 'a19', 'a21', 'a24'];
        $verticalAlignedCoordinates = ['b4:K47' => 'center', 'a4:a7' => 'center', 'a23:b23' => 'center'];
        $horizontalAlignedCoordinates = ['b4:K47' => 'center', 'a4:a7' => 'center', 'a23:b23' => 'right'];
        $adjustedColumnWidthCoordinates = [
            'A' => 37, 'B' => 17, 'C' => 15, 'D' => 20, 'E' => 5, 'F' => 5, 'G' => 25, 'H' => 30, 'I' => 10, 'J' => 10, 'K' => 10, 'L' => 10,
            'M' => 10, 'N' => 10,
        ];
        $outlineBorderThinCoordinates = [
            'A4:A7', 'B4:B7', 'C4:C7', 'D4:D7', 'E4:E7', 'F4:F7', 'G4:h4', 'G6:G7', 'H6:H7', 'J4:J7', 'I4:I7'
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