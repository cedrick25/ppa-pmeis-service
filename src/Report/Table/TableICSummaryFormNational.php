<?php

declare(strict_types=1);

namespace App\Report\Table;

use App\Entity\Quarters;
use App\Repository\FieldOfficesRepository;
use App\Repository\QuartersRepository;
use App\Repository\RegionsRepository;
use App\Service\Volunteerism\VolunteerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TableICSummaryFormNational implements Form
{
    private const TABLE_NAME = "TableICSummaryFormNational";
    
    public function __construct(
        private QuartersRepository  $quartersRepository,
        private RegionsRepository   $regionsRepository,
        private FieldOfficesRepository $fieldOfficesRepository,
        private VolunteerInterface  $volunteer,
        private ?Quarters           $quarter = null,
        private int                 $lastFilledOutCellY = 14,
        private array               $data = [],
    ) {}

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
        $quarterId = intval($this->data['quarter_id']);
        $regions = $this->regionsRepository->findAll();

        $cellsX = [
            'start_of_quarter_vpa' => 'B',
            'new_appointed' => 'C',
            'dropped' => 'D',
            'total_number_of_vpa_during_quarter' => 'E',
            'inactive' => 'F',
            'total_active_vpa' => 'G',
            'no_of_vpa_supervising_clients' => 'H',
            'total_number_of_clients_supervised' => 'I',
            'no_of_vpa_acting_as_resource_individuals' => 'J',
            'vpa_acting_both_supervising_and_resource_individual' => 'K',
            'total_number_of_vpa_mobilize' => 'L',
            'percent_of_vpa_mobilized' => 'M',
            'no_of_services_rendered_during_quarter' => 'N',
            'no_of_services_rendered_by_vpa' => 'O',
        ];
        $template = [
            'start_of_quarter_vpa' => 0,
            'new_appointed' => 0,
            'dropped' => 0,
            'total_number_of_vpa_during_quarter' => 0,
            'inactive' => 0,
            'total_active_vpa' => 0,
            'no_of_vpa_supervising_clients' => 0,
            'total_number_of_clients_supervised' => 0,
            'no_of_vpa_acting_as_resource_individuals' => 0,
            'vpa_acting_both_supervising_and_resource_individual' => 0,
            'total_number_of_vpa_mobilize' => 0,
            'percent_of_vpa_mobilized' => 0,
            'no_of_services_rendered_during_quarter' => 0,
            'no_of_services_rendered_by_vpa' => 0,
        ];
        $totals = $template;

        foreach ($regions as $region) {
            $this->lastFilledOutCellY++;
            $fieldOffices = $this->fieldOfficesRepository->findBy(['regionId' => $region->getRegionId()]);


            $scores = $template;

            foreach ($fieldOffices as $fieldOffice) {
                $report = $this->volunteer->getVpaMonitoring($quarterId, $fieldOffice->getFieldOfficeId());

                foreach ($report as $key => $item) {
                    $scores[$key] += $item;
                    $totals[$key] += $item;
                }
            }

            $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, $region->getName());

            foreach ($scores as $key => $score) {
                $spreadsheet->getActiveSheet()->setCellValue(
                    $cellsX[$key] . $this->lastFilledOutCellY,
                    $score
                );
            }
        }

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, 'Total');

        foreach ($totals as $key => $score) {
            $spreadsheet->getActiveSheet()->setCellValue(
                $cellsX[$key] . $this->lastFilledOutCellY,
                $score
            );
        }

        return $spreadsheet;
    }

    public function header(): Spreadsheet
    {
        $spreadsheet = $this->prepare();
        $spreadsheet->getActiveSheet()->getStyle('A5:P9')
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
            'R1' => 'AGENCY IQPR CONSOLIDATION FORM - PPA-PLD-FR-001',
            'A2' => 'DOJ-PPA IQPR CONSOLIDATED REPORT',
            'A3' => $this->quarter->getName() . ' QTR, ' . $this->quarter->getYear(),
            'A4' => "I.C.  VPA MONITORING",
            'A5' => 'REGIONAL OFFICES',
            'B5' => 'No. of VPAs (start of the quarter)',
            'C5' => 'Appointed',
            'D5' => 'Dropped (expired appointment or any other cause)',
            'E5' => 'TOTAL NUMBER OF VPAs  DURING THE QUARTER',
            'F5' => 'No. of INACTIVE VPAs during the QTR',
            'G5' => 'TOTAL  ACTIVE VPAs DURING THE QUARTER',
            'H5' => 'No. of VPAs supervising clients during the quarter (Head count)',
            'I5' => 'Total Number of clients Supervised',
            'J5' => 'No. of VPAs acting as resource individuals during the quarter (Head count)',
            'K5' => 'Acting as Both (Supervising VPAs and Resource Individual/ Head count)',
            'L5' => 'Total Number of VPA mobilized (per head count)',
            'M5' => 'Percent of VPA mobilized (per head count)',
            'N5' => 'No. of services rendered by VPAs during the quarter',
            'O5' => 'No. of services rendered By a VPA',
            'B7' => '(1)',
            'C7' => '(2)',
            'D7' => '(3)',
            'E7' => '(4)',
            'F7' => '(5)',
            'G7' => '(6)',
            'H7' => '(7)',
            'I7' => '(8)',
            'J7' => '(9)',
            'K7' => '(10)',
            'L7' => '(11)',
            'M7' => '(12)',
            'N7' => '(13)',
            'O7' => '(14)',
            'E8' => '(1+2)-3',
            'G9' => '4-5',
            'H9' => '6÷4',
            'L9' => '7+9+10=11',
            'M9' => '11/6=12',
            'O9' => '13/6=14'
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