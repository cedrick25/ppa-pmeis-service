<?php

declare(strict_types=1);

namespace App\Report\Table;

use App\Entity\Quarters;
use App\Repository\FieldOfficesRepository;
use App\Repository\QuartersRepository;
use App\Repository\RegionsRepository;
use App\Service\Volunteerism\IdSupportInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TableIDSummaryFormNational implements Form
{
    private const TABLE_NAME = "TableIDSummaryFormNational";
    
    public function __construct(
        private QuartersRepository  $quartersRepository,
        private RegionsRepository   $regionsRepository,
        private FieldOfficesRepository  $fieldOfficesRepository,
        private IdSupportInterface  $idSupport,
        private ?Quarters           $quarter = null,
        private int                 $lastFilledOutCellY = 7,
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
            'TCLP' => ['Personnel' => 'B', 'VPA' => 'C'],
            'RJ' => ['Personnel' => 'D', 'VPA' => 'E'],
            'VPA' => ['Personnel' => 'F', 'VPA' => 'G'],
            'GAD' => ['Personnel' => 'H', 'VPA' => 'I'],
            'OTHERS' => ['Personnel' => 'J', 'VPA' => 'K'],
        ];

        $totals = ['total' => 0];

        foreach ($regions as $region) {
            $this->lastFilledOutCellY++;
            $fieldOffices = $this->fieldOfficesRepository->findBy(['regionId' => $region->getRegionId()]);
            $scores = [];

            foreach ($fieldOffices as $fieldOffice) {
                $report = $this->idSupport->getIdSupportReport($quarterId, $fieldOffice->getFieldOfficeId());

                if (! isset($report['data'])) {
                    continue;
                }

                $results = [];
                $total = 0;
                $reports = $report['data'];

                foreach ($reports as $report) {
                    $program = $report['program'];
                    $type = $report['type'];

                    if (! isset($results[$program][$type])) {
                        $results[$program][$type] = 0;
                    }

                    $results[$program][$type]++;
                    $total++;
                }

                foreach ($results as $program => $items) {
                    foreach ($items as $type => $score) {
                        if (! isset($scores[$program][$type])) {
                            $scores[$program][$type] = 0;
                        }

                        $scores[$program][$type] += $score;

                        if (! isset($totals[$program][$type])) {
                            $totals[$program][$type] = 0;
                        }

                        $totals[$program][$type] += $score;
                    }
                }

                $spreadsheet->getActiveSheet()->setCellValue('L' . $this->lastFilledOutCellY, $total);
                $totals['total'] += $total;
            }

            $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, $region->getName());
            foreach ($scores as $program => $items) {
                foreach ($items as $type => $score) {
                    $spreadsheet->getActiveSheet()->setCellValue(
                        $cellsX[$program][$type] . $this->lastFilledOutCellY,
                        $score
                    );
                }
            }
        }

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, 'Total');
        foreach ($totals as $program => $items) {
            if ('total' == $program) {
                $spreadsheet->getActiveSheet()->setCellValue('L' . $this->lastFilledOutCellY, $items);

                continue;
            }

            foreach ($items as $type => $score) {
                $spreadsheet->getActiveSheet()->setCellValue(
                    $cellsX[$program][$type] . $this->lastFilledOutCellY,
                    $score
                );
            }
        }

        $spreadsheet->getActiveSheet()->getStyle('A5:L' . $this->lastFilledOutCellY)
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
        ];

        $mergesCoordinates = [
            'A5:A7','B5:L5','B6:C6','D6:E6','F6:G6','H6:I6','J6:K6',
        ];
        $boldCoordinates = ['A5:L7'];
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