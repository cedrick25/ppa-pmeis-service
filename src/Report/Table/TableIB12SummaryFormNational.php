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

class TableIB12SummaryFormNational implements Form
{
    private const TABLE_NAME = "TableIB12SummaryFormNational";
    
    public function __construct(
        private QuartersRepository  $quartersRepository,
        private RegionsRepository   $regionsRepository,
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
        $spreadsheet->getActiveSheet()->getStyle('A5:O10')
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $spreadsheet;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function prepare(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $textAndCoordinates = [
            'O1' => 'AGENCY IQPR CONSOLIDATION FORM - PPA-PLD-FR-001',
            'A2' => 'DOJ-PPA IQPR CONSOLIDATED REPORT',
            'A3' => $this->quarter->getName() . ' QTR, ' . $this->quarter->getYear(),
            'A4' => "B.1-2 Number of RJ Processes Conducted/ Clients' Involvement",
            'A5' => 'REGIONAL OFFICES',
            'B5' => 'ACTIVE SUPERVISION',
            'I5' => 'PETITIONERS',
            'B6' => 'N     U     M     B     E     R          O     F',
            'B7' => 'RJ STATUS',
            'D7' => 'RJ OUTCOME',
            'H7' => "VICTIMS' OFFENDED PARTIES SERVED",
            'I7' => 'RJ STATUS',
            'K7' => 'RJ OUTCOME',
            'O7' => "VICTIMS' OFFENDED PARTIES SERVED",
            'B8' => 'RESOLVED',
            'C8' => 'UNRESOLVED',
            'D8' => 'RESTITUTION',
            'E8' => 'CWS',
            'F8' => 'Restoration of Relationships',
            'G8' => 'OTHERS',
            'I8' => 'RESOLVED',
            'J8' => 'UNRESOLVED',
            'K8' => 'RESTITUTION',
            'L8' => 'CWS',
            'M8' => 'Restoration of Relationships',
            'N8' => 'OTHERS',
            'A10' => 'Total',
        ];

        $mergesCoordinates = [
            'A5:A8', 'B5:H5', 'I5:O5', 'B6:O6', 'B7:C7', 'D7:G7', 'H7:H8', 'I7:J7', 'K7:N7', 'O7:O8',
        ];

        $boldCoordinates = ['A1:O8', 'A10'];

        $verticalAlignedCoordinates = ['A5:O10' => 'center'];

        $horizontalAlignedCoordinates = ['A5:O10' => 'center'];

        $adjustedColumnWidthCoordinates = ['A' => 25, 'H' => 15, 'O' => 15];

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