<?php

declare(strict_types=1);

namespace App\Report\Table;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RMIV implements Form
{
    private const TABLE_NAME = "RMIV";
    
    public function __construct(
        private int   $lastFilledOutCellY = 8,
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

        $this->lastFilledOutCellY++;
        $lastFilledOutCellY = $this->lastFilledOutCellY;
        $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, 'NOTE:  DO NOT INCLUDE RESOURCES FROM THE  REGIONAL OFFICE (THIS IS REPORTED BY RO SEPARATELY) ');
        $this->lastFilledOutCellY++;
        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, 'NOTE:      RESOURCE MOBILIZATION is a  continuing process of developing, generating and managing funds, information, goods, services, people and institutions to provide support to program');
        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, '                implementation  and sustainability.  The process includes scanning, analyzing, processing, planning, matching, advocating, monitoring, linkaging, allocating, utilizing, controlling');
        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, '                evaluating  and maintaining internal and external resources.');
        $spreadsheet->getActiveSheet()->getStyle("A$lastFilledOutCellY" . ':T' . $this->lastFilledOutCellY)->getFont()->setBold(true);

        return $spreadsheet;
    }

    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();
        $this->data['total'] = [
            'amount' => 0,
            'cash_source_type' => ['GO' => 0, 'NGO' => 0, 'IND' => 0],
            'materials_amount' => 0,
            'material_source_type' => ['GO' => 0, 'NGO' => 0, 'IND' => 0],
            'technical_assistance_amount' => 0,
            'technical_assistance_type' => ['GO' => 0, 'NGO' => 0, 'IND' => 0]
        ];

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, '1.  THERAPEUTIC ');
        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, '     COMMUNITY  (TC)');
        $spreadsheet = $this->plot($this->data['rows']['TC'], $spreadsheet);
        $this->lastFilledOutCellY++;

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, '2.  RESTORATIVE JUSTICE');
        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, '      (RJ)');
        $spreadsheet = $this->plot($this->data['rows']['RJ'], $spreadsheet);
        $this->lastFilledOutCellY++;

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, '3.  VOLUNTEERISM  (VPA)');
        $spreadsheet = $this->plot($this->data['rows']['VPA'], $spreadsheet);
        $this->lastFilledOutCellY++;

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, '4.   GENDER AND ');
        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, '       DEVELOPMENT  (GAD)');
        $spreadsheet = $this->plot($this->data['rows']['GAD'], $spreadsheet);
        $this->lastFilledOutCellY++;

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, '5.  PERSONS WITH');
        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, '     DISABILITY (PWDs) and');
        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, '     SENIOR CITIZENS');
        // TODO: To be discussed
        $spreadsheet = $this->plot([], $spreadsheet);
        $this->lastFilledOutCellY++;

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, '6.  OTHERS');
        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, '     (To include LGUs - on detail)');
        $spreadsheet = $this->plot($this->data['rows']['OTHERS'], $spreadsheet);
        $this->lastFilledOutCellY++;

        $spreadsheet = $this->plotTotal($spreadsheet);

        $spreadsheet->getActiveSheet()->getStyle('A6:H' . $this->lastFilledOutCellY)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $spreadsheet->getActiveSheet()->getStyle('A9:T' . $this->lastFilledOutCellY)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('t27')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('80808080');

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
            'a1' => 'TABLE IV.  RESOURCE MOBILIZATION', 't1' => 'PPA-PLD-FR-004',
            'a4' => 'ACTIVITIES UNDERTAKEN/ ',
            'a5' => 'RENDERED FOR WHICH',
            'a6' => 'RESOURCES/ ASSISTANCE', 
            'a7' => 'WERE UTILIZED',
            'a8' => '(1)',

            'b4' => 'DATE/ VENUE',
            'b6' => '(2)',
            
            'c3' => 'RESOURCES SECURED/ UTILIZED (3)',
            'c4' => 'CASH',
            'c6' => 'AMOUNT',

            'd5' => 'SOURCE',
            'd6' => 'NAME',

            'e6' => 'GO',
            'f6' => 'NGO',
            'g6' => 'IND',

            'h4' => 'SUPPLIES/ MATERIALS/ GOODS/ OTHERS',
            'h5' => 'PARTICULARS  (Qty. and type)',
            'i5' => 'ESTIMATED AMOUNT',

            'j5' => 'SOURCE',
            'j6' => 'NAME',

            'k6' => 'GO',
            'l6' => 'NGO',
            'm6' => 'IND',

            'n4' => 'TECHNICAL ASSISTANCE',
            'n5' => 'PARTICULARS (Qty. and Type)',

            'o5' => 'ESTIMATED AMOUNT',
            'p5' => 'SOURCE',
            'p6' => 'NAME',

            'q6' => 'GO',
            'r6' => 'NGO',
            's6' => 'IND',

            't3' => '',
            't4' => '',
            't5' => 'RESOURCES ',
            't6' => 'SECURED OR',
            't7' => 'FACILITATED BY',
            't8' => '(4)',

        ];
        $mergesCoordinates = [
            'C3:S3', 'C4:G4', 'D5:G5', 'H4:M4', 'J5:M5', 'N4:S4', 'P5:S5', 'C3:S3', 'H4:M4', 
        ];
        $boldCoordinates = ['t1','a1','A8','B6','C6','C4','C3','H4','H5','I5','N4','N5','O5','T8', 'a29', 'a31'];
        $verticalAlignedCoordinates = ['B3:T30' => 'center', 'A3:A8' => 'center'];
        $horizontalAlignedCoordinates = ['B3:T30' => 'center', 'A3:A8' => 'center'];
        $adjustedColumnWidthCoordinates = [
            'A' => 57, 'B' => 40, 'C' => 17, 'D' => 15, 'E' => 5, 'F' => 5, 'G' => 5, 'H' => 30, 'I' => 30, 'J' => 30, 'K' => 5, 'L' => 5, 'M' => 5, 'N' => 30, 'O' => 20, 'P' => 20, 'Q' => 5, 'R' => 5, 'S' => 5, 'T' => 70,
        ];
        $outlineBorderThinCoordinates = [
            'A3:A8', 'B3:B8', 'C5:C8', 'D6:D8', 'E6:E8', 'F6:F8', 'G6:G8', 'H5:H8', 'I5:I8', 'J6:J8', 'K6:K8', 'L6:L8', 'm6:m8',
            'N5:N8', 'O5:O8', 'P6:P8', 'Q6:Q8', 'R6:R8', 's6:s8', 'P5:S5', 'C3:S3', 'H4:M4', 'D5:G5', 'T3:T8'
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

    private function plot(array $rows, Spreadsheet $spreadsheet): Spreadsheet
    {
        $sourceTypeCoordinate = [
            'cash' => ['GO' => 'E', 'NGO' => 'F', 'IND' => 'G'],
            'materials' => ['GO' => 'K', 'NGO' => 'L', 'IND' => 'M'],
            'technical' => ['GO' => 'Q', 'NGO' => 'R', 'IND' => 'S'],
        ];

        foreach ($rows as $row) {
            $this->lastFilledOutCellY++;
            $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, $row['activity_name']);
            $spreadsheet->getActiveSheet()->setCellValue('B' . $this->lastFilledOutCellY, $row['date'] . ' ' . $row['venue']);
            $spreadsheet->getActiveSheet()->setCellValue('C' . $this->lastFilledOutCellY, $row['amount']);
            $spreadsheet->getActiveSheet()->setCellValue('D' . $this->lastFilledOutCellY, $row['cash_source_name']);
            $spreadsheet->getActiveSheet()->setCellValue(
                $sourceTypeCoordinate['cash'][$row['cash_source_type']] . $this->lastFilledOutCellY,
                $row['cash_source_type']);
            $spreadsheet->getActiveSheet()->setCellValue('H' . $this->lastFilledOutCellY, $row['materials_particulars'] . ' ' . $row['materials_qty']);
            $spreadsheet->getActiveSheet()->setCellValue('I' . $this->lastFilledOutCellY, $row['materials_amount']);
            $spreadsheet->getActiveSheet()->setCellValue('J' . $this->lastFilledOutCellY, $row['material_source_name']);
            $spreadsheet->getActiveSheet()->setCellValue(
                $sourceTypeCoordinate['materials'][$row['material_source_type']] . $this->lastFilledOutCellY,
                $row['material_source_type']);
            $spreadsheet->getActiveSheet()->setCellValue('N' . $this->lastFilledOutCellY, $row['technical_assistance_particulars']);
            $spreadsheet->getActiveSheet()->setCellValue('O' . $this->lastFilledOutCellY, $row['technical_assistance_amount']);
            $spreadsheet->getActiveSheet()->setCellValue('P' . $this->lastFilledOutCellY, $row['technical_assistance_name']);
            $spreadsheet->getActiveSheet()->setCellValue(
                $sourceTypeCoordinate['technical'][$row['technical_assistance_type']] . $this->lastFilledOutCellY,
                $row['technical_assistance_type']);
            $spreadsheet->getActiveSheet()->setCellValue('T' . $this->lastFilledOutCellY, $row['resources_secured_by']);

            $this->data['total']['amount'] += intval($row['amount']);
            $this->data['total']['cash_source_type'][$row['cash_source_type']]++;
            $this->data['total']['materials_amount'] += intval($row['materials_amount']);
            $this->data['total']['material_source_type'][$row['material_source_type']]++;
            $this->data['total']['technical_assistance_amount'] += intval($row['technical_assistance_amount']);
            $this->data['total']['technical_assistance_type'][$row['material_source_type']]++;
        }

        return $spreadsheet;
    }

    private function plotTotal(Spreadsheet $spreadsheet): Spreadsheet
    {
        $this->lastFilledOutCellY++;
        $total = $this->data['total'];
        $sourceTypeCoordinate = [
            'cash' => ['GO' => 'E', 'NGO' => 'F', 'IND' => 'G'],
            'materials' => ['GO' => 'K', 'NGO' => 'L', 'IND' => 'M'],
            'technical' => ['GO' => 'Q', 'NGO' => 'R', 'IND' => 'S'],
        ];

        $spreadsheet->getActiveSheet()->setCellValue('B' . $this->lastFilledOutCellY, 'TOTAL');
        $spreadsheet->getActiveSheet()->setCellValue('C' . $this->lastFilledOutCellY, number_format($total['amount'], 2));
        $spreadsheet->getActiveSheet()->setCellValue('D' . $this->lastFilledOutCellY, 'TOTAL');
        foreach ($total['cash_source_type'] as $type=>$score) {
            $spreadsheet->getActiveSheet()->setCellValue($sourceTypeCoordinate['cash'][$type] . $this->lastFilledOutCellY, $score);
        }

        $spreadsheet->getActiveSheet()->setCellValue('H' . $this->lastFilledOutCellY, 'TOTAL');
        $spreadsheet->getActiveSheet()->setCellValue('I' . $this->lastFilledOutCellY, number_format($total['materials_amount'], 2));
        $spreadsheet->getActiveSheet()->setCellValue('J' . $this->lastFilledOutCellY, 'TOTAL');
        foreach ($total['material_source_type'] as $type=>$score) {
            $spreadsheet->getActiveSheet()->setCellValue($sourceTypeCoordinate['materials'][$type] . $this->lastFilledOutCellY, $score);
        }

        $spreadsheet->getActiveSheet()->setCellValue('N' . $this->lastFilledOutCellY, 'TOTAL');
        $spreadsheet->getActiveSheet()->setCellValue('O' . $this->lastFilledOutCellY, number_format($total['technical_assistance_amount'], 2));
        $spreadsheet->getActiveSheet()->setCellValue('P' . $this->lastFilledOutCellY, 'TOTAL');
        foreach ($total['technical_assistance_type'] as $type=>$score) {
            $spreadsheet->getActiveSheet()->setCellValue($sourceTypeCoordinate['technical'][$type] . $this->lastFilledOutCellY, $score);
        }

        return $spreadsheet;
    }
}