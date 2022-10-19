<?php

declare(strict_types=1);

namespace App\Report\Table;

use App\Enum\OccupationType;
use App\Service\Volunteerism\Volunteer;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SocioDemographicOccupation implements Form
{
    private const TABLE_NAME = "SocioDemographicOccupation";
    
    public function __construct(
        private Volunteer   $service,
        private int   $lastFilledOutCellY = 10,
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
        $this->data = $this->getData($data);

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

        $coordinates = [
            'occupation' => [
                OccupationType::ARMED_FORCES_OCCUPATION => 'B',
                OccupationType::MANAGERS => 'C',
                OccupationType::PROFESSIONALS => 'D',
                OccupationType::TECHNICAL_ASSOCIATE_PROFESSIONALS => 'E',
                OccupationType::CLERICAL_SUPPORT_WORKERS => 'F',
                OccupationType::SERVICE_AND_SALES_WORKERS => 'G',
                OccupationType::SKILLED_AGRICULTURAL_FORESTRY_AND_FISHERY_WORKERS => 'H',
                OccupationType::CRAFT_AND_RELATED_TRADES_WORKERS => 'I',
                OccupationType::PLANT_AND_MACHINES_OPERATORS_AND_ASSEMBLERS => 'J',
                OccupationType::ELEMENTARY_OCCUPATION => 'K',
                OccupationType::UNEMPLOYED => 'L'
            ]
        ];

        $total = [
            OccupationType::ARMED_FORCES_OCCUPATION => 0,
            OccupationType::MANAGERS => 0,
            OccupationType::PROFESSIONALS => 0,
            OccupationType::TECHNICAL_ASSOCIATE_PROFESSIONALS => 0,
            OccupationType::CLERICAL_SUPPORT_WORKERS => 0,
            OccupationType::SERVICE_AND_SALES_WORKERS => 0,
            OccupationType::SKILLED_AGRICULTURAL_FORESTRY_AND_FISHERY_WORKERS => 0,
            OccupationType::CRAFT_AND_RELATED_TRADES_WORKERS => 0,
            OccupationType::PLANT_AND_MACHINES_OPERATORS_AND_ASSEMBLERS => 0,
            OccupationType::ELEMENTARY_OCCUPATION => 0,
            OccupationType::UNEMPLOYED => 0,
            'Total' => 0
        ];

        foreach ($this->data['rows'] as $region => $row) {
            $this->lastFilledOutCellY++;
            $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, $region);

            $totalScore = 0;
            foreach ($row['occupation'] as $occupation => $value) {
                $cellColumn = $coordinates['occupation'][$occupation];
                $total[$occupation] += $value;

                $spreadsheet->getActiveSheet()->setCellValue($cellColumn . $this->lastFilledOutCellY, $value);
                $totalScore += $value;
            }

            $total['Total'] += $totalScore;
            $spreadsheet->getActiveSheet()->setCellValue('M' . $this->lastFilledOutCellY, $totalScore);
        }

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, 'GRAND TOTALS');
        $spreadsheet->getActiveSheet()->getStyle('A' . $this->lastFilledOutCellY)->getFont()->setBold(true);
        $spreadsheet->getActiveSheet()
            ->setCellValue('B' . $this->lastFilledOutCellY, $total[OccupationType::ARMED_FORCES_OCCUPATION]);
        $spreadsheet->getActiveSheet()
            ->setCellValue('C' . $this->lastFilledOutCellY, $total[OccupationType::MANAGERS]);
        $spreadsheet->getActiveSheet()
            ->setCellValue('D' . $this->lastFilledOutCellY, $total[OccupationType::PROFESSIONALS]);
        $spreadsheet->getActiveSheet()
            ->setCellValue('E' . $this->lastFilledOutCellY, $total[OccupationType::TECHNICAL_ASSOCIATE_PROFESSIONALS]);
        $spreadsheet->getActiveSheet()
            ->setCellValue('F' . $this->lastFilledOutCellY, $total[OccupationType::CLERICAL_SUPPORT_WORKERS]);
        $spreadsheet->getActiveSheet()
            ->setCellValue('G' . $this->lastFilledOutCellY, $total[OccupationType::SERVICE_AND_SALES_WORKERS]);
        $spreadsheet->getActiveSheet()->setCellValue(
            'H' . $this->lastFilledOutCellY,
            $total[OccupationType::SKILLED_AGRICULTURAL_FORESTRY_AND_FISHERY_WORKERS]
        );
        $spreadsheet->getActiveSheet()
            ->setCellValue('I' . $this->lastFilledOutCellY, $total[OccupationType::CRAFT_AND_RELATED_TRADES_WORKERS]);
        $spreadsheet->getActiveSheet()->setCellValue(
            'J' . $this->lastFilledOutCellY,
            $total[OccupationType::PLANT_AND_MACHINES_OPERATORS_AND_ASSEMBLERS]
        );
        $spreadsheet->getActiveSheet()
            ->setCellValue('K' . $this->lastFilledOutCellY, $total[OccupationType::ELEMENTARY_OCCUPATION]);
        $spreadsheet->getActiveSheet()
            ->setCellValue('L' . $this->lastFilledOutCellY, $total[OccupationType::UNEMPLOYED]);
        $spreadsheet->getActiveSheet()->setCellValue('M' . $this->lastFilledOutCellY, $total['Total']);

        $spreadsheet->getActiveSheet()
            ->getStyle('A10:M' . $this->lastFilledOutCellY)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()
            ->getStyle('A10:M' . $this->lastFilledOutCellY)
            ->getAlignment()->setHorizontal('center');
        $spreadsheet->getActiveSheet()
            ->getStyle('A10:M' . $this->lastFilledOutCellY)
            ->getAlignment()->setVertical('center');

        return $spreadsheet;
    }

    public function header(): Spreadsheet
    {
        return $this->prepare();
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function prepare(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $textAndCoordinates = [
            'M1' => 'PPA-CSD-FR-011-00',
            'A2' => 'VPA SOCIO-DEMOGRAPHIC REPORT',
            'A3' => 'As of________20__',
            'A5' => 'Field Office',
            'B5' => 'OCCUPATION',
            'B6' => OccupationType::ARMED_FORCES_OCCUPATION,
            'C6' => OccupationType::MANAGERS,
            'D6' => OccupationType::PROFESSIONALS,
            'E6' => OccupationType::TECHNICAL_ASSOCIATE_PROFESSIONALS,
            'F6' => OccupationType::CLERICAL_SUPPORT_WORKERS,
            'G6' => OccupationType::SERVICE_AND_SALES_WORKERS,
            'H6' => OccupationType::SKILLED_AGRICULTURAL_FORESTRY_AND_FISHERY_WORKERS,
            'I6' => OccupationType::CRAFT_AND_RELATED_TRADES_WORKERS,
            'J6' => OccupationType::PLANT_AND_MACHINES_OPERATORS_AND_ASSEMBLERS,
            'K6' => OccupationType::ELEMENTARY_OCCUPATION,
            'L6' => OccupationType::UNEMPLOYED,
            'M6' => 'Total',
        ];
        $mergesCoordinates = [
            'A2:M2', 'A3:M3', 'A5:A10', 'B5:M5', 'B6:B10', 'C6:C10', 'D6:D10', 'E6:E10',
            'F6:F10', 'G6:G10', 'H6:H10', 'I6:I10', 'J6:J10', 'K6:K10', 'L6:L10', 'M6:M10'
        ];
        $boldCoordinates = ['A1:M10'];
        $verticalAlignedCoordinates = ['A1:M10' => 'center'];
        $horizontalAlignedCoordinates = ['A1:M10' => 'center'];
        $adjustedColumnWidthCoordinates = ['A' => 40];
        $outlineBorderThinCoordinates = [
            'A5:A10', 'B5:M5', 'B6:D6', 'D6:F6', 'G6:G10', 'H6:H10', 'I6:I10', 'J6:J10',
            'K6:K10', 'L6:L10', 'M6:M10', 'B7:B10', 'C7:C10', 'D7:D10', 'E7:E10', 'F7:F10', 'G7:G10'
        ];
        $rotateTextCoordinates = [
            'B6:B10', 'C6:C10', 'D6:D10', 'E6:E10', 'F6:F10', 'G6:G10',
            'H6:H10', 'I6:I10', 'J6:J10', 'K6:K10', 'L6:L10', 'M6:M10'
        ];

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
            $spreadsheet->getActiveSheet()
                ->getStyle($coordinate)
                ->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        }

        foreach ($rotateTextCoordinates as $coordinate) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getAlignment()->setTextRotation(90);
        }

        $spreadsheet->getActiveSheet()->getRowDimension(5)->setRowHeight(20);
        $spreadsheet->getActiveSheet()->getRowDimension(10)->setRowHeight(60);
        $spreadsheet->getActiveSheet()->getStyle('B5:P10')->getFont()->setSize(8);

        return $spreadsheet;
    }

    private function getData(array $data): array
    {
        $result = $this->service->getConsolidatedSocioDemographic($data['region_id']);

        return ['rows' => $result['data'] ?? []];
    }
}
