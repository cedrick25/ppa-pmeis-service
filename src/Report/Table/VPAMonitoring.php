<?php

declare(strict_types=1);

namespace App\Report\Table;

use App\Service\Volunteerism\Volunteer;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VPAMonitoring implements Form
{
    private const TABLE_NAME = "VPAMonitoring";
    
    public function __construct(
        private Volunteer   $service,
        private int         $lastFilledOutCellY = 7,
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
        $this->lastFilledOutCellY++;

        foreach ($this->data['rows'] as $row) {
            $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, $row['start_of_quarter_vpa']);
            $spreadsheet->getActiveSheet()->setCellValue('B' . $this->lastFilledOutCellY, $row['new_appointed']);
            $spreadsheet->getActiveSheet()->setCellValue('C' . $this->lastFilledOutCellY, $row['reappointed']);
            $spreadsheet->getActiveSheet()->setCellValue('D' . $this->lastFilledOutCellY, $row['dropped']);
            $spreadsheet->getActiveSheet()->setCellValue('E' . $this->lastFilledOutCellY, $row['total_number_of_vpa_during_quarter']);
            $spreadsheet->getActiveSheet()->setCellValue('F' . $this->lastFilledOutCellY, $row['inactive']);
            $spreadsheet->getActiveSheet()->setCellValue('G' . $this->lastFilledOutCellY, $row['total_active_vpa']);
            $spreadsheet->getActiveSheet()->setCellValue('H' . $this->lastFilledOutCellY, $row['percentage_of_vpa_mobilized'] . '%');
            $spreadsheet->getActiveSheet()->setCellValue('I' . $this->lastFilledOutCellY, $row['no_of_vpa_supervising_clients']);
            $spreadsheet->getActiveSheet()->setCellValue('J' . $this->lastFilledOutCellY, $row['no_of_vpa_supervising_clients_percentage'] . '%');
            $spreadsheet->getActiveSheet()->setCellValue('K' . $this->lastFilledOutCellY, $row['no_of_vpa_acting_as_resource_individuals']);
            $spreadsheet->getActiveSheet()->setCellValue('L' . $this->lastFilledOutCellY, $row['no_of_vpa_acting_as_resource_individuals_percentage'] . '%');
            $spreadsheet->getActiveSheet()->setCellValue('M' . $this->lastFilledOutCellY, $row['vpa_acting_both_supervising_and_resource_individual']);
            $spreadsheet->getActiveSheet()->setCellValue('N' . $this->lastFilledOutCellY, $row['percentage_of_vpa_acting_both_supervising_and_resource_individual'] . '%');
            $spreadsheet->getActiveSheet()->setCellValue('O' . $this->lastFilledOutCellY, $row['total_number_of_clients_supervised']);
            $spreadsheet->getActiveSheet()->setCellValue('P' . $this->lastFilledOutCellY, $row['no_of_services_rendered_by_vpa']);
            $spreadsheet->getActiveSheet()->setCellValue('Q' . $this->lastFilledOutCellY, $row['no_of_services_rendered_by_vpa_percentage'] . '%');
        }

        $spreadsheet->getActiveSheet()->getStyle('A'. $this->lastFilledOutCellY .':Q' . $this->lastFilledOutCellY)->getAlignment()->setHorizontal('center');
        $spreadsheet->getActiveSheet()->getStyle('A'. $this->lastFilledOutCellY .':Q' . $this->lastFilledOutCellY)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
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
            'P1' => 'PPA-PLD-FR-004', 'A2' => 'VPA MONITORING', 'A3' => 'No. of VPAs (start of the quarter)',
            'B3' => 'Appointed', 'D3' => 'Dropped (expired appointment or any other cause)', 'E3' => 'TOTAL NUMBER OF VPAs  DURING THE QUARTER',
            'F3' => 'No. of INACTIVE VPAs during the QTR', 'G3' => 'TOTAL  ACTIVE VPAs DURING THE QUARTER', 'H3' => '% of VPAs mobilized',
            'I3' => 'No. of VPAs supervising clients during the quarter (Head count)', 'J3' => '%', 'K3' => 'No. of VPAs acting as resource individuals during the quarter (Head count)',
            'L3' => '%', 'M3' => 'Acting as Both (Supervising VPAs and Resource Individual/ Head count)', 'N3' => '%', 'O3' => 'Total number of clients supervised (Headcount)',
            'P3' => 'No. of services rendered by VPAs during the quarter (service count or frequency)', 'Q3' => 'Percent of services rendered by VPAs',
            'B4' => 'New', 'C4' => 'Re-appointed', 'A5' => '(1)', 'B5' => '(2)', 'D5' => '(3)', 'E5' => '(4)', 'F5' => '(5)', 'G5' => '(6)', 'H5' => '(7)',
            'I5' => '(8)', 'J5' => '(9)', 'K5' => '(10)', 'L5' => '(11)', 'M5' => '(12)', 'N5' => '(13)', 'O5' => '(14)', 'P5' => '(15)', 'Q5' => '(16)',
            'E6' => '(1+2)-3', 'G6' => '4-5', 'H6' => '6÷4', 'J6' => '8÷6', 'L6' => '10÷6', 'N6' => '12÷6', 'Q6' => '15÷6'
        ];
        $mergesCoordinates = ['B3:C3', 'B5:C5'];
        $boldCoordinates = ['P1', 'A2', 'E3', 'G3', 'A5:Q6'];
        $verticalAlignedCoordinates = ['A3:Q6' => 'center'];
        $horizontalAlignedCoordinates = ['A3:Q6' => 'center'];
        $adjustedColumnWidthCoordinates = [
            'B' => 12, 'C' => 12
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
        $spreadsheet->getActiveSheet()->getStyle('A3:Q7')->getAlignment()->setWrapText(true);
        $spreadsheet->getActiveSheet()->getStyle('A6:Q6')->getFont()->getColor()->setARGB(Color::COLOR_RED);
        $spreadsheet->getActiveSheet()->getRowDimension(3)->setRowHeight(170);
        $spreadsheet->getActiveSheet()->getStyle('A3:Q7')->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('A6:Q6')
            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(Color::COLOR_YELLOW);
        $spreadsheet->getActiveSheet()->getStyle('B4:C4')
            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(Color::COLOR_YELLOW);

        return $spreadsheet;
    }

    private function getData(array $data): array
    {
        $result = $this->service->getVpaMonitoring(
            $data['quarter_id'],
            $data['field_office_id']
        );

        return ['rows' => [$result ?? []]];
    }
}