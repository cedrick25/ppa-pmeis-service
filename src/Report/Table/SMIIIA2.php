<?php

declare(strict_types=1);

namespace App\Report\Table;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SMIIIA2 implements Form
{
    private const TABLE_NAME = "SMIIIA2";
    
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

        $spreadsheet->getActiveSheet()->getStyle('A4:G11')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('C3:F3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        // $spreadsheet->getActiveSheet()->getStyle('d23')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('80808080');
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
            'a1' => 'Table  III.A.2  -  MEETINGS /PARTICIPATIONS IN PEACE & ORDER COUNCIL (POC)/ ANTI-DRUG ABUSE COUNCIL (CADAC)/ MANAGEMENT SCREENING & EVALUATION COMMITTEE (MSEC), DDB AUTHORIZED REPRESENTATIVE, ETC.',
            'a2' => 'Activity', 
            'a3' => '(1)',
            'a4' => '1.  Peace and Order Councils (POC) / Anti-Drug Abuse Council ( CADAC)/',
            'a5' => '     Government Information Officers  League, MSEC, etc.',
            'a7' => '2.  Others (Attendance/ Participation in significant events ', 
            'a8' => '            as representative of the Agency)',
            'b2' => 'Date.Venue',
            'b3' => '(2)',
            'c2' => 'Participants (3)',
            'c3' => 'No.', 
            'd3' => 'Type',
            'e2' => 'Name of Person/ s Involved  (4)', 
            'e3' => 'Personnel',
            'f3' => 'Role', 
            'g2' => 'Remarks',
            'g3' => '(5)',

        ];
        $mergesCoordinates = [
            'C2:D2', 'E2:F2',
        ];
        $boldCoordinates = ['a1'];
        $verticalAlignedCoordinates = ['B2:G11' => 'center', 'A2:A3' => 'center'];
        $horizontalAlignedCoordinates = ['B2:G11' => 'center', 'A2:A3' => 'center'];
        $adjustedColumnWidthCoordinates = [
            'A' => 63, 'B' => 20, 'C' => 10, 'D' => 10, 'E' => 15, 'F' => 15, 'G' => 15, 'H' => 20,
        ];
        $outlineBorderThinCoordinates = [
            'A2:A3', 'B2:B3', 'C2:D2', 'E2:F2', 'G2:G3', 
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