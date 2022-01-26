<?php

declare(strict_types=1);

namespace App\Report\Table;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RJIB1 implements Form
{
    private const TABLE_NAME = "RJIB1";

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
            'A1' => 'B.  RESTORATIVE JUSTICE',
            'Q1' => 'PPA-PLD-FR-004',
            'A3' => 'Table I.B.1   RESTORATIVE JUSTICE (RJ) PROCESSES CONDUCTED / CLIENTS INVOLVED',
            'A5' => "CLIENT'S NAME",
            'C5' => 'SEX',
            'F5' => 'Sr. Citizen (4)',
            'G5' => 'OFFENSE',
            'H5' => 'Pre-Encounter RJ Activities',
            'K5' => 'RJ PROCESS',
            'N5' => 'RJ PLANNER (8)',
            'O5' => 'PERSONS/ INSTITUTION / STAKEHOLDERS INVOLVED (9)',
            'P5' => 'STATUS OF RJ PROCESS',
            'Q5' => 'RJ OUTCOME of the',
            'E6' => 'P',
            'P6' => '(10)',
            'Q6' => 'RESOLVED PROCESS (11)',
            'C7' => '(2)',
            'E7' => 'W',
            'H7' => '(6)',
            'K7' => '(7)',
            'O7' => '(Include  Offended Party,',
            'P7' => '(Shelved, Deferred,On-Going,',
            'Q7' => '(Restitution(R), Community Work Services (CWS), Restored Relationships (RR) and Others(O))',
            'C8' => 'F',
            'D8' => 'M',
            'E8' => 'D',
            'G8' => '(5)',
            'H8' => 'Date',
            'I8' => 'Venue',
            'J8' => 'Activity',
            'K8' => 'Date',
            'L8' => 'Venue',
            'M8' => 'Type',
            'O8' => 'VPA and other Individuals/ Groups.  Indicate before',
            'P8' => 'Completed, Agreement Reached)',
            'A9' => '(1)',
            'E9' => '(3)',
            'O9' => 'each name if OP/VPA/FM/CM).',
            'A10' => 'I.  ACTIVE SUPERVISION',
        ];

        $wrapTextCoordinates = [
            "F5:F9", "N5:N9", "O7:O9", "P7:P9", "Q7:Q9"
        ];

        $mergesCoordinates = [
            "A5:B8", "C5:D6", "F5:F9", "G5:G7", "H5:J6", "K5:M6", "N5:N9", "O5:O6", "C7:D7", "H7:J7", "K7:M7", "Q7:Q9",
            "C8:C9", "D8:D9", "G8:G9", "H8:H9", "I8:I9", "J8:J9", "K8:K9", "L8:L9", "M8:M9", "A9:B9"
        ];

        $boldCoordinates = [
            "A1:N10","Q1","O5:O6","P5:P6","Q5:Q9"
        ];

        $verticalAlignedCoordinates = ["A5:Q9" => "center"];
        $horizontalAlignedCoordinates = ["A5:Q9" => "center"];

        $adjustedColumnWidthCoordinates = [
            'C' => 3, 'D' => 3, 'E' => 3, 'F' => 7, 'G' => 10, 'H' => 8, 'I' => 10, 'J' => 12, 'K' => 8, 'L' => 10, 'M' => 10, 'N' => 9, 'O' => 35, 'P' => 35, 'Q' => 35
        ];

        $outlineBorderThinCoordinates = [
            "A5:B8","C5:D6","E5:E8","F5:F9","G5:G7","H5:J6","K5:M6","N5:N9","O5:O6","P5:P6","Q5:Q6","C7:D7","H7:J7","K7:M7","C8:C9",
            "D8:D9","G8:G9","H8:H9","I8:I9","J8:J9","K8:K9","L8:L9","M8:M9","O7:O9","P7:P9","Q7:Q9","A9:B9", "A10:C10"
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

        $spreadsheet->getActiveSheet()->getRowDimension(8)->setRowHeight(30);
        $spreadsheet->getActiveSheet()->getRowDimension(9)->setRowHeight(30);

        foreach ($outlineBorderThinCoordinates as $coordinate) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        }

        $spreadsheet->getActiveSheet()->getStyle("D10:Q10")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle("A11:Q11")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $spreadsheet;
    }
}