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
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TableIDSummaryFormNational implements Form
{
    private const TABLE_NAME = "TableIDSummaryFormNational";
    
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
        $spreadsheet->getActiveSheet()->getStyle('A5:L9')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $spreadsheet;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function prepare(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $textAndCoordinates = [
            'L1' => 'AGENCY IQPR CONSOLIDATION FORM - PPA-PLD-FR-001',
            'A2' => 'DOJ-PPA IQPR CONSOLIDATED REPORT',
            'A3' => $this->quarter->getName() . ' QTR, ' . $this->quarter->getYear(),
            'A4' => "I.C.  VPA MONITORING",
            'A5' => 'REGIONAL OFFICES',
            'B5' => 'TOTAL      NUMBER',
            'B6' => 'TCLP',
            'D6' => 'RJ',
            'F6' => 'VPA',
            'H6' => 'GAD',
            'J6' => 'OTHERS',
            'L6' => 'TOTAL NO. OF FOs',
            'B7' => 'PERSONNEL',
            'C7' => 'VPA',
            'D7' => 'PERSONNEL',
            'E7' => 'VPA',
            'F7' => 'PERSONNEL',
            'G7' => 'VPA',
            'H7' => 'PERSONNEL',
            'I7' => 'VPA',
            'J7' => 'PERSONNEL',
            'K7' => 'VPA',
            'L7' => 'ASSISTED',
            'A9' => 'TOTAL',
        ];

        $mergesCoordinates = [
            'A5:A7','B5:L5','B6:C6','D6:E6','F6:G6','H6:I6','J6:K6',
        ];
        $boldCoordinates = ['A5:L7', 'A9:L9'];
        $verticalAlignedCoordinates = ['A5:L7' => 'center'];
        $horizontalAlignedCoordinates = ['A5:L7' => 'center'];
        $adjustedColumnWidthCoordinates = ['A' => 25];

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

        $spreadsheet->getActiveSheet()->getStyle('A5:L7')->getAlignment()->setWrapText(true);

        return $spreadsheet;
    }
}