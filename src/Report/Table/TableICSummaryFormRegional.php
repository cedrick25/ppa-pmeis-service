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

class TableICSummaryFormRegional implements Form
{
    private const TABLE_NAME = "TableICSummaryFormRegional";
    
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
            'A4' => "I.C.  VPA MONITORING",
            'A5' => 'FIELD OFFICES',
            'B5' => 'No. of VPAs (start of the quarter)',
            'C5' => 'Appointed',
            'E5' => 'Dropped (expired appointment or any other cause)',
            'F5' => 'TOTAL NUMBER OF VPAs  DURING THE QUARTER',
            'G5' => 'No. of INACTIVE VPAs during the QTR',
            'H5' => 'TOTAL  ACTIVE VPAs DURING THE QUARTER',
            'I5' => '% of VPAs mobilized',
            'J5' => 'No. of VPAs supervising clients during the quarter (Head count)',
            'K5' => '%',
            'L5' => 'No. of VPAs acting as resource individuals during the quarter (Head count)',
            'M5' => '%',
            'N5' => 'Acting as Both (Supervising VPAs and Resource Individual/ Head count)',
            'O5' => '%',
            'P5' => 'Total number of clients supervised (Headcount)',
            'Q5' => 'No. of services rendered by VPAs during the quarter (service count or frequency)',
            'R5' => 'Percent of services rendered by VPAs',
            'C6' => 'New',
            'D6' => 'Re-appointed',
            'B7' => '(1)',
            'C7' => '(2)',
            'E7' => '(3)',
            'F7' => '(4)',
            'G7' => '(5)',
            'H7' => '(6)',
            'I7' => '(7)',
            'J7' => '(8)',
            'K7' => '(9)',
            'L7' => '(10)',
            'M7' => '(11)',
            'N7' => '(12)',
            'O7' => '(13)',
            'P7' => '(14)',
            'Q7' => '(15)',
            'R7' => '(16)',
            'A8' => 'Formula',
            'F8' => '(1+2)-3',
            'H8' => '4-5',
            'I8' => '6÷4',
            'K8' => '8÷6',
            'M8' => '10÷6',
            'O8' => '12÷6',
            'R8' => '15÷6',
            'A10' => 'Total',
        ];

        $mergesCoordinates = ['A5:A6', 'C5:D5'];
        $boldCoordinates = ['A5:R8'];
        $verticalAlignedCoordinates = ['A5:R8' => 'center'];
        $horizontalAlignedCoordinates = ['A5:R8' => 'center'];
        $adjustedColumnWidthCoordinates = [
            'A' => 25, 'B' => 12, 'C' => 12
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
        $spreadsheet->getActiveSheet()->getStyle('A5:R8')->getAlignment()->setWrapText(true);
        $spreadsheet->getActiveSheet()->getStyle('B8:R8')->getFont()->getColor()->setARGB(Color::COLOR_RED);
        $spreadsheet->getActiveSheet()->getRowDimension(5)->setRowHeight(10);
        $spreadsheet->getActiveSheet()->getStyle('A5:R10')->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('A8:R8')
            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(Color::COLOR_YELLOW);

        return $spreadsheet;
    }
}