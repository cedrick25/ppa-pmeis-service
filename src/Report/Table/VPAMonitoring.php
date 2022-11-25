<?php

declare(strict_types=1);

namespace App\Report\Table;

use App\Enum\SystemSettingNames;
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

    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();
        $this->lastFilledOutCellY++;

        foreach ($this->data['rows'] as $row) {
            $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, $row['start_of_quarter_vpa']);
            $spreadsheet->getActiveSheet()->setCellValue('B' . $this->lastFilledOutCellY, $row['new_appointed']);
            $spreadsheet->getActiveSheet()->setCellValue('C' . $this->lastFilledOutCellY, $row['dropped']);
            $spreadsheet->getActiveSheet()->setCellValue(
                'D' . $this->lastFilledOutCellY,
                $row['total_number_of_vpa_during_quarter']
            );
            $spreadsheet->getActiveSheet()->setCellValue('E' . $this->lastFilledOutCellY, $row['inactive']);
            $spreadsheet->getActiveSheet()->setCellValue('F' . $this->lastFilledOutCellY, $row['total_active_vpa']);
            $spreadsheet->getActiveSheet()->setCellValue(
                'G' . $this->lastFilledOutCellY,
                $row['no_of_vpa_supervising_clients']
            );
            $spreadsheet->getActiveSheet()->setCellValue(
                'H' . $this->lastFilledOutCellY,
                $row['total_number_of_clients_supervised']
            );
            $spreadsheet->getActiveSheet()->setCellValue(
                'I' . $this->lastFilledOutCellY,
                $row['no_of_vpa_acting_as_resource_individuals']
            );
            $spreadsheet->getActiveSheet()->setCellValue(
                'J' . $this->lastFilledOutCellY,
                $row['vpa_acting_both_supervising_and_resource_individual']
            );
            $spreadsheet->getActiveSheet()->setCellValue(
                'K' . $this->lastFilledOutCellY,
                $row['total_number_of_vpa_mobilize']
            );
            $spreadsheet->getActiveSheet()->setCellValue(
                'L' . $this->lastFilledOutCellY,
                $row['percent_of_vpa_mobilized'] . '%'
            );
            $spreadsheet->getActiveSheet()->setCellValue(
                'M' . $this->lastFilledOutCellY,
                $row['no_of_services_rendered_during_quarter']
            );
            $spreadsheet->getActiveSheet()->setCellValue(
                'N' . $this->lastFilledOutCellY,
                $row['no_of_services_rendered_by_vpa']
            );
        }

        $spreadsheet->getActiveSheet()->getStyle('A'. $this->lastFilledOutCellY .':L' . $this->lastFilledOutCellY)
            ->getAlignment()->setHorizontal('center');
        $spreadsheet->getActiveSheet()->getStyle('A'. $this->lastFilledOutCellY .':L' . $this->lastFilledOutCellY)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
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
            'N1' => $this->data[SystemSettingNames::GENERATED_REPORTS_CODE],
            'A2' => 'VPA MONITORING',
            'A3' => 'No. of VPAs (start of the quarter)',
            'B3' => 'Appointed',
            'C3' => 'Dropped (expired appointment or any other cause)',
            'D3' => 'TOTAL NUMBER OF VPAs  DURING THE QUARTER',
            'E3' => 'No. of INACTIVE VPAs during the QTR',
            'F3' => 'TOTAL  ACTIVE VPAs DURING THE QUARTER',
            'G3' => 'No. of VPAs supervising clients during the quarter (Head count)',
            'H3' => 'Total Number of clients Supervised',
            'I3' => 'No. of VPAs acting as resource individuals during the quarter (Head count)',
            'J3' => 'Acting as Both (Supervising VPAs and Resource Individual/ Head count)',
            'K3' => 'Total Number of VPA mobilized (per head count)',
            'L3' => 'Percent of VPA mobilized (per head count)',
            'M3' => 'No. of services rendered by VPAs during the quarter',
            'N3' => 'No. of services rendered By a VPA',
            'A5' => '(1)',
            'B5' => '(2)',
            'C5' => '(3)',
            'D5' =>'(4)',
            'E5' =>'(5)',
            'F5' =>'(6)',
            'G5' =>'(7)',
            'H5' => '(8)',
            'I5' => '(9)',
            'J5' => '(10)',
            'K5' => '(11)',
            'L5' => '(12)',
            'M5' => '(13)',
            'N5' => '(14)',
            'E6' => '(1+2)-3',
            'G6' => '4-5',
            'H6' => '6÷4',
            'L6' => '7+9+10=11',
            'M6' => '11/6=12',
            'N6' => '13/6=14'
        ];
        $boldCoordinates = ['P1', 'A2', 'E3', 'G3', 'A5:N6'];
        $verticalAlignedCoordinates = ['A3:N6' => 'center'];
        $horizontalAlignedCoordinates = ['A3:N6' => 'center'];
        $adjustedColumnWidthCoordinates = [
            'B' => 12, 'C' => 12
        ];
        foreach ($textAndCoordinates as $coordinate => $text) {
            $spreadsheet->getActiveSheet()->setCellValue($coordinate, $text);
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
        $spreadsheet->getActiveSheet()->getStyle('A3:N7')->getAlignment()->setWrapText(true);
        $spreadsheet->getActiveSheet()->getStyle('A6:N6')->getFont()->getColor()->setARGB(Color::COLOR_RED);
        $spreadsheet->getActiveSheet()->getRowDimension(3)->setRowHeight(170);
        $spreadsheet->getActiveSheet()->getStyle('A3:N7')->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('A6:N6')
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