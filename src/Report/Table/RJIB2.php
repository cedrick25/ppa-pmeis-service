<?php

declare(strict_types=1);

namespace App\Report\Table;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RJIB2 implements Form
{
    private const TABLE_NAME = "RJIB2";
    
    public function __construct(
        private int   $lastFilledOutCellY = 9,
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
        $rows = ['PETITIONER' => [], 'ACTIVE_SUPERVISION' => []];

        foreach ($this->data['rows'] as $row) {
            $rows[$row['rj_group']][] = $row;
        }


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
            'K1' => 'PPA-PLD-FR-004', 'A2' => 'Table I.B.2  RJ RELATED ACTIVITIES/ INTERVENTIONS FOR VICTIMS',
            'A4' => "CLIENT'S NAME", 'B4' => 'SEX', 'D4' => 'PWD', 'E4' => 'Senior', 'F4' => 'OFFENSE (Specify)',
            'G4' => "VICTIM's NAME", 'H4' => 'Activities/ Interventions', 'I4' => 'DATE/ VENUE', 'J4' => 'PERSONS/',
            'K4' => 'OUTCOME', 'B5' => 'F', 'C5' => 'M', 'E5' => 'Citizen', 'G5' => "(To include victims' ", 'J5' => 'INSTITUTIONS',
            'A6' => '(1)', 'D6' => '(3)', 'E6' => '(4)', 'G6' => 'family members)', 'I6' => '(8)', 'J6' => 'INVOLVED', 'K6' => '(10)',
            'B7' => '(2)', 'F7' => '(5)', 'G7' => '(6)', 'H7' => '(7)', 'J7' => '(9)', 'A8' => 'I.  ACTIVE SUPERVISION'
        ];

        $wrapTextCoordinates = ["A4:K9"];

        $mergesCoordinates = [
            "A4:A5", "A6:A7", "B4:C4", "B5:B6", "C5:C6", "B7:C7", "D4:D5","D6:D7", "E6:E7", "F4:F6","H4:H6","I4:I5","I6:I7","K4:K5","K6:K7"
        ];

        $boldCoordinates = ["A1:K8"];

        $verticalAlignedCoordinates = ["A4:K7" => "center"];
        $horizontalAlignedCoordinates = ["A4:K7" => "center"];

        $adjustedColumnWidthCoordinates = [
            'A' => 30, 'B' => 7, 'C' => 7, 'D' => 9, 'E' => 9, 'F' => 30, 'G' => 40, 'H' => 20, 'I' => 20, 'J' => 25, 'K' => 25
        ];

        $outlineBorderThinCoordinates = [
            'A4:A5','A6:A7','B4:C4','B5:B6','C5:C6','B7:C7','D4:D5','D6:D7','E4:E5','E6:E7','F4:F6','F7','G4','G5:G6','G7','H4:H6','H7',
            'I4:I5','I6:I7','J4:J6','J7','K4:K5','K6:K7'
        ];

        foreach ($textAndCoordinates as $coordinate => $text) {
            $spreadsheet->getActiveSheet()->setCellValue($coordinate, $text);
        }

        foreach ($wrapTextCoordinates as $coordinate) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getAlignment()->setWrapText(true);
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

        $spreadsheet->getActiveSheet()->getStyle("A8:K9")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $spreadsheet;
    }
}