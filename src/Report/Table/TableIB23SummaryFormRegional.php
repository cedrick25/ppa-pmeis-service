<?php

declare(strict_types=1);

namespace App\Report\Table;

use App\Entity\Quarters;
use App\Entity\Regions;
use App\Repository\QuartersRepository;
use App\Repository\RegionsRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TableIB23SummaryFormRegional implements Form
{
    private const TABLE_NAME = "TableIB23SummaryFormRegional";
    
    public function __construct(
        private QuartersRepository  $quartersRepository,
        private RegionsRepository   $regionsRepository,
        private ?Regions            $region = null,
        private ?Quarters           $quarter = null,
        private int                 $lastFilledOutCellY = 14,
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
        $quarterId = intval($data['quarter_id']);
        $regionId = intval($data['region_id']);

        $this->region = $this->regionsRepository->find($regionId);
        $this->quarter = $this->quartersRepository->find($quarterId);
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
        $spreadsheet->getActiveSheet()->getStyle('A5:P9')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $spreadsheet;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function prepare(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $textAndCoordinates = [
            'A1' => 'REGION ' . $this->region->getName(),
            'A2' => 'IQPR SUMMARY FORM',
            'A3' => $this->quarter->getName() . ' QTR, ' . $this->quarter->getYear(),
            'A4' => "B.2 and B.3  VPA INVOLVEMENT IN RJ PROCESSES/ RJ ACTIVITIES FOR VICTIMS/ CIVIL LIABILITIES",
            'A5' => 'FIELD OFFICES',
            'B5' => 'NO. OF VPAs INVOLVED IN RJ PROCESS',
            'D5' => 'NO. OF RJ RELATED  ACTS./INTERVENTIONS FOR VICTIMS',
            'F5' => 'C   I   V   I   L   L   I   A   B   I   L   I   T   Y',
            'F6' => 'TOTAL NO. OF CLIENTS W/  CL (SUPERVISION)',
            'G6' => 'TOTAL NO. OF CLIENTS W/ CL (PETITIONERS)',
            'H6' => 'ORIGINAL AMOUNT',
            'I6' => 'START OF QTR.',
            'J6' => 'TOTAL NO. OF CLIENTS WHO PAID',
            'L6' => 'TOTAL AMOUNT PAID',
            'N6' => 'BALANCE END OF QTR.',
            'O6' => 'TOTAL AMT. REMITTED RECEIVED BY VICTIMS/ BENEFICIARIES',
            'B6' => 'FOR CLIENTS UNDER ACTIVE SUPV.',
            'C6' => 'Petitioners',
            'D7' => 'ACTIVE SUPV.',
            'E7' => 'Petitioners',
            'J7' => 'ACTIVE SUPV.',
            'K7' => 'Petitioners',
            'L7' => 'ACTIVE SUPV.',
            'M7' => 'Petitioners',
            'O7' => 'ACTIVE SUPV.',
            'P7' => 'Petitioners',
            'A9' => 'Total',
        ];

        $mergesCoordinates = [
            'A5:A7','B5:C5','D5:E6','F5:P5','B6:B7','C6:C7','F6:F7','G6:G7','H6:H7','I6:I7','J6:K6','L6:M6','N6:N7','O6:P6',
        ];

        $boldCoordinates = ['A1:P7', 'A9'];

        $verticalAlignedCoordinates = ['A5:P9' => 'center'];

        $horizontalAlignedCoordinates = ['A5:P9' => 'center'];

        $adjustedColumnWidthCoordinates = [
            'A' => 25, 'B' => 15, 'C' => 15, 'D' => 10, 'E' => 10, 'F' => 20, 'G' => 20, 'H' => 20, 'I' => 20,
            'J' => 10, 'K' => 10, 'L' => 20, 'M' => 10, 'O' => 10, 'P' => 10
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

        return $spreadsheet;
    }
}