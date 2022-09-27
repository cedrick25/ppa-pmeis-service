<?php

declare(strict_types=1);

namespace App\Report\Table;

use App\Enum\SystemSettingNames;
use App\Service\Volunteerism\TechnicalAssistance;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SMIIIA3 implements Form
{
    private const TABLE_NAME = "SMIIIA3";
    
    public function __construct(
        private TechnicalAssistance $service,
        private int                 $lastFilledOutCellY = 5,
        private array               $data = [],
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
            $spreadsheet->getActiveSheet()->setCellValue("a" . $this->lastFilledOutCellY, $row['activity_name']);
            $spreadsheet->getActiveSheet()->setCellValue("b" . $this->lastFilledOutCellY, $row['agency_name']);
            $spreadsheet->getActiveSheet()->setCellValue("c" . $this->lastFilledOutCellY, $row['date'] . ' ' . $row['venue']);
            $spreadsheet->getActiveSheet()->setCellValue("d" . $this->lastFilledOutCellY, $row['participants_no']);
            $spreadsheet->getActiveSheet()->setCellValue("e" . $this->lastFilledOutCellY, $row['participants_type']);
            $spreadsheet->getActiveSheet()->setCellValue("f" . $this->lastFilledOutCellY, $row['name']);
            $spreadsheet->getActiveSheet()->setCellValue("g" . $this->lastFilledOutCellY, $row['role']);
            $spreadsheet->getActiveSheet()->setCellValue("h" . $this->lastFilledOutCellY, $row['remarks']);
        }
        $spreadsheet->getActiveSheet()->getStyle('A6:h' . $this->lastFilledOutCellY)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

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
            'h1' => $this->data[SystemSettingNames::GENERATED_REPORTS_CODE],
            'a3' => 'Table III.A.3  - TECHNICAL ASSISTANCE/ OUTREACH ACTIVITIES TO OTHER AGENCIES/ OTHER RELATED COMMUNITY PARTICIPATION /PUBLIC ASSISTANCE',
            'a4' => 'Activity',
            'a5' => '(1)',
            'b4' => 'Agency Assisted', 
            'b5' => '(2)',
            'c4' => 'Date / Venue',
            'c5' => '(3)',
            'd4' => 'Participants   (4)',
            'd5' => 'No.',
            'e5' => 'Type',
            'f4' => 'Name of Person/s  Involved   (5)',
            'f5' => 'Personnel/VPA',
            'g5' => ' Role',
            'h4' => 'REMARKS',
            'h5' => '(6)',

        ];
        $mergesCoordinates = [
            'D4:E4', 'F4:G4',
        ];
        $boldCoordinates = ['h1','a3'];
        $verticalAlignedCoordinates = ['A4:H14' => 'center',];
        $horizontalAlignedCoordinates = ['A4:H14' => 'center',];
        $adjustedColumnWidthCoordinates = [
            'A' => 33, 'B' => 20, 'C' => 50, 'D' => 15, 'E' => 15, 'F' => 80, 'G' => 15, 'H' => 20,
        ];
        $outlineBorderThinCoordinates = [
            'A4:A5', 'B4:B5', 'C4:C5', 'D4:E4', 'F4:G4', 'H4:H5', 'F5:F5', 'D5:D5'
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
        $result = $this->service->getReport(
            $data['quarter_id'],
            $data['field_office_id']
        );

        return ['rows' => array_values($result['data'] ?? [])];
    }
}