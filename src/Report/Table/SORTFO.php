<?php

declare(strict_types=1);

namespace App\Report\Table;

use App\Common\AppDateHelper;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SORTFO implements Form
{
    private const TABLE_NAME = "SORTFO";
    
    public function __construct(
        private AppDateHelper $appDateHelper,
        private int   $lastFilledOutCellY = 5,
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

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Exception
     */
    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();

        foreach ($this->data['rows'] as $row) {
            $this->lastFilledOutCellY++;
            $date = $this->appDateHelper->convertStringToImmutableDate($row['date']);

            $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, $date->format('d-M-y'));
            $spreadsheet->getActiveSheet()->setCellValue('B' . $this->lastFilledOutCellY, $row['field_office']);
            $spreadsheet->getActiveSheet()->setCellValue('C' . $this->lastFilledOutCellY, $row['particulars']);
            $spreadsheet->getActiveSheet()->setCellValue('D' . $this->lastFilledOutCellY, number_format(floatval($row['amount'])));
            $spreadsheet->getActiveSheet()->setCellValue('E' . $this->lastFilledOutCellY, number_format(floatval($row['attributable_cost'])));
            $spreadsheet->getActiveSheet()->setCellValue('F' . $this->lastFilledOutCellY, number_format(floatval($row['total_amount'])));
            $spreadsheet->getActiveSheet()->setCellValue('G' . $this->lastFilledOutCellY, $row['remarks']);
        }
        $spreadsheet->getActiveSheet()->getStyle('A6:G' . $this->lastFilledOutCellY)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

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
        $clientTypes = [
            'TC' => 'A. TC ACTIVITIES',
            'RJ' => 'B. RJ ACTIVITIES',
            'VPA' => 'C. VPA ACTIVITIES',
            'GAD' => 'D. GAD ACTIVITIES',
            'PWDSC' => 'E. PERSONS WITH DISABILITY (PWD) / SENIOR CITIZENS (SC)',
            'OTHERS' => 'F. OTHER ACTIVITIES (TRICON, REGIONAL/ NATIONAL COMMITTEE MEETINGS, FIELD AUDIT, EXECON, ETC.)',
        ];
        $clientType = $this->data['rows'][0]['category'];

        $textAndCoordinates = [
            'B1' => 'FINANCIAL / MATERIAL SUPPORT OF REGIONAL OFFICE TO FIELD OFFICES (To be prepared by the Regional Office)',
            'b2' => '_______________Quarter 20______________',
            'a4' => $clientTypes[$clientType],
            'a5' => 'DATE',
            'b5' => 'FIELD OFFICE(S)',
            'c5' => 'PARTICULARS',
            'd5' => 'AMOUNT',
            'e5' => 'ATTRIBUTABLE COST',
            'f5' => 'TOTAL AMOUNT',
            'g5' => 'REMARKS',
        ];
        $mergesCoordinates = [
            'B1:G1','B2:G2',

        ];
        $boldCoordinates = ['B1','B2','a4'];
        $verticalAlignedCoordinates = ['B2:G10' => 'center', 'B1:B1' => 'center', 'A5:A5' => 'center'];
        $horizontalAlignedCoordinates = ['B2:G10' => 'center', 'B1:B1' => 'center', 'A5:A5' => 'center'];
        $adjustedColumnWidthCoordinates = [
            'A' => 40, 'B' => 35, 'C' => 40, 'D' => 25, 'E' => 25, 'F' => 15, 'G' => 15, 'H' => 15, 'I' => 15, 'J' => 15, 'K' => 27, 'L' => 20, 'M' => 30, 'N' => 30, 'O' => 20, 'P' => 20, 'Q' => 5, 'R' => 5, 'S' => 5, 'T' => 20,
        ];
        $outlineBorderThinCoordinates = [
            'A5:A5', 'B5:B5', 'C5:C5', 'D5:D5', 'E5:E5', 'F5:F5', 'G5:G5',
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
}