<?php

declare(strict_types=1);

namespace App\Report\Table;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VPAIC1 implements Form
{
    private const TABLE_NAME = "VPAIC1";
    
    public function __construct(
        private int   $lastFilledOutCellY = 14,
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
            'A1' => 'C.  VOLUNTEERISM', 'G1' => 'PPA-PLD-FR-004', 'A2' => 'Table I.C.1 – RECRUITMENT', 'C3' => 'Sex', 'E3' => 'Date of', 'A4' => 'No.',
            'B4' => 'Name of Recruit', 'C4' => '(3)', 'E4' => 'Birth', 'F4' => 'Date Recruited', 'G4' => 'Recruiting Officer', 'A5' => '(1)', 'B5' => '(2)',
            'C5' => 'F', 'D5' => 'M', 'E5' => 'mm/dd/yyyy', 'F5' => '(5)', 'G5' => '(6)', 'E6' => '(4)'
        ];
        $mergesCoordinates = ['C4:D4', 'C5:C6', 'D5:D6'];
        $boldCoordinates = ['A1:G2', 'C4', 'A5', 'B5', 'E6', 'F5', 'G5'];
        $verticalAlignedCoordinates = ['A3:G6' => 'center'];
        $horizontalAlignedCoordinates = ['A3:G6' => 'center'];
        $adjustedColumnWidthCoordinates = ['A' => 4, 'B' => 40, 'C' => 4, 'D' => 4, 'E' => 25, 'F' => 25, 'G' => 25];
        $outlineBorderThinCoordinates = ['A3:A6', 'B3:B6', 'C3:D4', 'C5:C6', 'D5:D6', 'E3:E6', 'F3:F6', 'G3:G6'];


        foreach ($textAndCoordinates as $coordinate => $text) {
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