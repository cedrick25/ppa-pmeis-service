<?php

declare(strict_types=1);

namespace App\Report\Table;

use App\Enum\SystemSettingNames;
use App\Service\Volunteerism\IdSupport;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ID implements Form
{
    private const TABLE_NAME = "SupportProgram";
    
    public function __construct(
        private IdSupport   $service,
        private int         $lastFilledOutCellY = 5,
        private array       $data = [],
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
        $this->data = $this->getData($data);
        $this->data[SystemSettingNames::GENERATED_REPORTS_CODE] = $data[SystemSettingNames::GENERATED_REPORTS_CODE];

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

        foreach ($this->data['rows'] as $row) {
            $this->lastFilledOutCellY++;
            $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, $row['name']);
            $spreadsheet->getActiveSheet()->setCellValue("b" . $this->lastFilledOutCellY, $row['program']);
            $spreadsheet->getActiveSheet()->setCellValue("c" . $this->lastFilledOutCellY, $row['assisted_field_office']);
            $spreadsheet->getActiveSheet()->setCellValue("d" . $this->lastFilledOutCellY, $row['activity']);
            $spreadsheet->getActiveSheet()->setCellValue("e" . $this->lastFilledOutCellY, $row['date'] . ' ' . $row['venue']);
            $spreadsheet->getActiveSheet()->setCellValue("f" . $this->lastFilledOutCellY, $row['assistance_rendered']);
        }
        $spreadsheet->getActiveSheet()->getStyle('A6:f' . $this->lastFilledOutCellY)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

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
            'g1' => $this->data[SystemSettingNames::GENERATED_REPORTS_CODE],
            'a2' => 'D.   SUPPORT TO OTHER FIELD OFFICES ON  TC, RJ, VPA, GAD, PWD, SC IMPLEMENTATION',
            'a3' => 'Name', 'b3' => 'Program', 'c3' => 'Field Office Assisted', 'd3' => 'Activity', 'e3' => 'Date/Venue', 'f3' => 'Assistance Rendered',
            'a4' => '(Personnel/ VPAs)', 'b4' => '(2)', 'c4' => '(3)', 'd4' => '(4)', 'e4' => '(5)', 'f4' => '(6)',
            'a5' => '(1)',
        ];
        $mergesCoordinates = [
        ];
        $boldCoordinates = ['g1','a2','a4','a5', 'b4', 'c4', 'd4', 'e4', 'f4',];
        $verticalAlignedCoordinates = ['a3:f15' => 'center'];
        $horizontalAlignedCoordinates = ['a3:f15' => 'center'];
        $adjustedColumnWidthCoordinates = [
            'A' => 23, 'B' => 10, 'C' => 25, 'D' => 30, 'E' => 50, 'F' => 25, 'G' => 25, 'H' => 10, 'I' => 10, 'J' => 10, 'K' => 10, 'L' => 10,
            'M' => 10, 'N' => 10, 'O' => 10, 'P' => 30, 'Q' => 10, 'R' => 10, 'S' => 10, 'T' => 10
        ];
        $outlineBorderThinCoordinates = [
            'A3:A5', 'B3:B5', 'C3:C5', 'D3:D5', 'e3:e5', 'f3:f5',
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

    private function getData(array $data): array
    {
        $result = $this->service->getIdSupportReport(
            $data['quarter_id'],
            $data['field_office_id'],
        );

        return ['rows' => $result['data'] ?? []];
    }
}