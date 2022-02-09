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

        $spreadsheet->getActiveSheet()->getStyle('A7:G11')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('A15:G18')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
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
            'a1' => 'III.   SOCIAL MARKETING',
            'a3' => 'Table  III.A.1  -  INFORMATION DISSEMINATION',
            'a4' => 'Activity', 
            'a5' => '(1)',
            'a7' => '1.  Fora, symposia  (Planned, coordinated, organized with',
            'a8' => '     program and topics, personnel/ VPAs with specific roles, letter',
            'a9' => '     request (if applicable), with target number of participants and', 
            'a10' => '     content limited to programs and services of the Agency.)',
            'a13' => 'Activity',
            'a14' => '(1)',
            'a15' => '2.  Publication/ Press Releases/ TV / Radio Interviews/ Guestings',
            'b4' => 'Date / Venue', 
            'b5' => '(2)',
            'b13' => 'Date / Venue', 
            'b14' => '(2)',
            'c4' => 'Participants   (3)', 
            'c5' => 'No.',
            'c13' => 'Participants',
            'c14' => '(3)',
            'd5' => 'Type',
            'e4' => 'Activity (4)', 
            'e5' => 'Personnel/ Role',
            'e13' => 'Activity (4)',
            'e14' => 'Personnel/ Role',
            'f5' => 'VPA/ Role',
            'f14' => 'VPA/ Role',
            'g4' => 'REMARKS  (5)',
            'g5' => '(Include  number of primers',
            'g6' => 'distributed)',
            'g13' => 'REMARKS  (5)',
            'g14' => '(Include  number of primers distributed)',
            'h1' => 'PPA-PLD-FR-004',

        ];
        $mergesCoordinates = [
            'C4:D4', 'C5:C6', 'D5:D6', 'E4:F4', 'E5:E6', 'F5:F6',
            'C13:D14', 'E13:F13',
        ];
        $boldCoordinates = ['a1','a3', 'h1'];
        $verticalAlignedCoordinates = ['B4:G17' => 'center', 'A4:A6' => 'center', 'A13:A14' => 'center'];
        $horizontalAlignedCoordinates = ['B4:G17' => 'center', 'A4:A6' => 'center', 'A13:A14' => 'center'];
        $adjustedColumnWidthCoordinates = [
            'A' => 58, 'B' => 20, 'C' => 10, 'D' => 10, 'E' => 15, 'F' => 15, 'G' => 37, 'H' => 20,
        ];
        $outlineBorderThinCoordinates = [
            'A4:A6', 'B4:B6', 'C4:D4', 'C5:C6', 'D5:D6', 'E4:F4', 'E5:E6', 'F5:F6', 'G4:G6',
            'A13:A14', 'B13:B14', 'C13:D14', 'E13:F13', 'G13:G14',
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