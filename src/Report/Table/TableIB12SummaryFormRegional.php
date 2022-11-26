<?php

declare(strict_types=1);

namespace App\Report\Table;

use App\Entity\Quarters;
use App\Entity\Regions;
use App\Repository\FieldOfficesRepository;
use App\Repository\QuartersRepository;
use App\Repository\RegionsRepository;
use App\Repository\RJConductProcessesRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TableIB12SummaryFormRegional implements Form
{
    private const TABLE_NAME = "TableIB12SummaryFormRegional";
    
    public function __construct(
        private QuartersRepository  $quartersRepository,
        private RegionsRepository   $regionsRepository,
        private FieldOfficesRepository  $fieldOfficesRepository,
        private RJConductProcessesRepository $conductProcessesRepository,
        private ?Regions            $region = null,
        private ?Quarters           $quarter = null,
        private int                 $lastFilledOutCellY = 8,
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
        $regionId = intval($this->data['region_id']);

        $fieldOffices = $this->fieldOfficesRepository->findBy(['regionId' => $regionId]);
        $fieldOfficesId = array_map(fn($fieldOffice) => $fieldOffice->getFieldOfficeId(), $fieldOffices);
        $processes = $this->getProcesses($quarterId, $fieldOfficesId);
        $cellsX = [
            'ACTIVE_SUPERVISION' => [
                'status' => ['RESOLVED' => 'B', 'UNRESOLVED' => 'C',],
                'outcome' => [
                    'Restitution' => 'D',
                    'Community Work Services' => 'E',
                    'Restored Relationships' => 'F',
                    'Others' => 'G',
                ],
            ],
            'PETITIONERS' => [
                'status' => ['RESOLVED' => 'I', 'UNRESOLVED' => 'J',],
                'outcome' => [
                    'Restitution' => 'K',
                    'Community Work Services' => 'L',
                    'Restored Relationships' => 'M',
                    'Others' => 'N',
                ],
            ],
        ];
        $totals = [
            'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0, 'G' => 0, 'I' => 0, 'J' => 0,
            'K' => 0, 'L' => 0, 'M' => 0, 'N' => 0,
        ];

        foreach ($fieldOffices as $fieldOffice) {
            $process = $processes[$fieldOffice->getFieldOfficeId()] ?? [];

            if (empty($process)) {
                continue;
            }
            $this->lastFilledOutCellY++;

            $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, $fieldOffice->getName());
            foreach ($process as $group => $item) {
                foreach ($item as $type => $value) {
                    foreach ($value as $key => $score) {
                        $cellX = $cellsX[$group][$type][$key];
                        $spreadsheet->getActiveSheet()->setCellValue(
                            $cellX . $this->lastFilledOutCellY,
                            $score
                        );

                        $totals[$cellX] += $score;
                    }
                }
            }
        }

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, 'Total');

        foreach ($totals as $cellX => $score) {
            $spreadsheet->getActiveSheet()->setCellValue(
                $cellX . $this->lastFilledOutCellY,
                $score
            );
        }

        $spreadsheet->getActiveSheet()->getStyle('A5:O' . $this->lastFilledOutCellY)
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
            'A1' => 'REGION ' . $this->region->getName(),
            'A2' => 'IQPR SUMMARY FORM',
            'A3' => $this->quarter->getName() . ' QTR, ' . $this->quarter->getYear(),
            'A4' => "B.1-2 Number of RJ Processes Conducted/ Clients' Involvement",
            'A5' => 'FIELD OFFICES',
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

    private function getProcesses(int $quarterId, array $fieldOfficesId): array
    {
        $results = [];
        $processes = $this->conductProcessesRepository->findByFieldOfficesIdWithDetails($quarterId, $fieldOfficesId);

        foreach ($processes as $process) {
            $fieldOfficeId = (int) $process['field_office_id'];
            $group = $process['rj_group'];
            $status = $process['status'];
            $translatedStatus = ('Agreement Reached' == $status || 'Completed' == $status) ? 'RESOLVED' : 'UNRESOLVED';
            $outcome = $process['outcome'];

            if (! isset($results[$fieldOfficeId][$group]['status'][$translatedStatus])) {
                $results[$fieldOfficeId][$group]['status'][$translatedStatus] = 0;
            }

            $results[$fieldOfficeId][$group]['status'][$translatedStatus]++;

            if (! isset($results[$fieldOfficeId]['outcome'][$group][$outcome])) {
                $results[$fieldOfficeId][$group]['outcome'][$outcome] = 0;
            }

            $results[$fieldOfficeId][$group]['outcome'][$outcome]++;
        }

        return $results;
    }
}