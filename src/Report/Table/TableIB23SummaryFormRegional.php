<?php

declare(strict_types=1);

namespace App\Report\Table;

use App\Entity\Quarters;
use App\Entity\Regions;
use App\Repository\FieldOfficesRepository;
use App\Repository\QuartersRepository;
use App\Repository\RegionsRepository;
use App\Repository\RJConductProcessesRepository;
use App\Repository\RJRelatedActivitiesRepository;
use App\Repository\RjRelatedRestitutionsRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TableIB23SummaryFormRegional implements Form
{
    private const TABLE_NAME = "TableIB23SummaryFormRegional";

    public function __construct(
        private QuartersRepository  $quartersRepository,
        private RegionsRepository   $regionsRepository,
        private FieldOfficesRepository  $fieldOfficesRepository,
        private RJRelatedActivitiesRepository $relatedActivitiesRepository,
        private RjRelatedRestitutionsRepository $restitutionsRepository,
        private RJConductProcessesRepository $conductProcessesRepository,
        private ?Regions            $region = null,
        private ?Quarters           $quarter = null,
        private int                 $lastFilledOutCellY = 14,
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
        $responses = [];
        $spreadsheet = $this->header();
        $quarterId = intval($this->data['quarter_id']);
        $regionId = intval($this->data['region_id']);

        $fieldOffices = $this->fieldOfficesRepository->findBy(['regionId' => $regionId]);
        $fieldOfficesId = array_map(fn($fieldOffice) => $fieldOffice->getFieldOfficeId(), $fieldOffices);
        $processes = $this->getProcessData($quarterId, $fieldOfficesId);
        $relatedActivities = $this->getActivities($quarterId, $fieldOfficesId);
        $restitutions = $this->getRestitutions($quarterId, $fieldOfficesId);

        foreach ($fieldOffices as $fieldOffice) {
            $relatedActivity = $relatedActivities[$fieldOffice->getFieldOfficeId()] ?? [];
            $restitution = $restitutions[$fieldOffice->getFieldOfficeId()] ?? [];
            $process = $processes[$fieldOffice->getFieldOfficeId()] ?? [];

            if (empty($relatedActivity)) {
                continue;
            }

            $responses[] = [
                'name' => $fieldOffice->getName(),
                'process_client_active' => $process['ACTIVE_SUPERVISION'] ?? 0,
                'process_client_petitioner' => $process['PETITIONERS'] ?? 0,
                'activity_client_active' => $relatedActivity['ACTIVE_SUPERVISION'] ?? 0,
                'activity_client_petitioner' => $relatedActivity['PETITIONERS'] ?? 0,
                'restitution_client_active' => $restitution['ACTIVE_SUPERVISION'] ?? 0,
                'restitution_client_petitioner' => $restitution['PETITIONERS'] ?? 0,
                'original_amount' => $restitution['originalAmount'] ?? 0,
                'start_of_quarter' => $restitution['startOfQuarter'] ?? 0,
                'balance' => $restitution['balance'] ?? 0,
                'paid_client_active' => $restitution['ACTIVE_SUPERVISION'] ?? 0,
                'paid_client_petitioner' => $restitution['PETITIONERS'] ?? 0,
                'amount_paid_active' => $restitution['group']['ACTIVE_SUPERVISION']['paymentAmount'] ?? 0,
                'amount_paid_petitioner' => $restitution['group']['PETITIONERS']['paymentAmount'] ?? 0,
                'amount_remitted_active' => $restitution['group']['ACTIVE_SUPERVISION']['remittedAmount'] ?? 0,
                'amount_remitted_petitioner' => $restitution['group']['PETITIONERS']['remittedAmount'] ?? 0,
            ];
        }

        $cellsX = [
            'name' =>'A',
            'process_client_active' =>'B',
            'process_client_petitioner' => 'C',
            'activity_client_active' => 'D',
            'activity_client_petitioner' => 'E',
            'restitution_client_active' => 'F',
            'restitution_client_petitioner' => 'G',
            'original_amount' => 'H',
            'start_of_quarter' => 'I',
            'balance' => 'J',
            'paid_client_active' => 'K',
            'paid_client_petitioner' => 'L',
            'amount_paid_active' => 'M',
            'amount_paid_petitioner' => 'N',
            'amount_remitted_active' => 'O',
            'amount_remitted_petitioner' => 'P',
        ];

        $totals = [
            'process_client_active' => 0,
            'process_client_petitioner' => 0,
            'activity_client_active' => 0,
            'activity_client_petitioner' => 0,
            'restitution_client_active' => 0,
            'restitution_client_petitioner' => 0,
            'original_amount' => 0,
            'start_of_quarter' => 0,
            'balance' => 0,
            'paid_client_active' => 0,
            'paid_client_petitioner' => 0,
            'amount_paid_active' => 0,
            'amount_paid_petitioner' => 0,
            'amount_remitted_active' => 0,
            'amount_remitted_petitioner' => 0,
        ];

        foreach ($responses as $response) {
            foreach ($response as $key => $item) {
                $spreadsheet->getActiveSheet()->setCellValue($cellsX[$key] . $this->lastFilledOutCellY, $item);

                if ('name' == $key) {
                    continue;
                }

                $totals[$key] += $item;
            }

            $this->lastFilledOutCellY++;
        }

        foreach ($totals as $key => $total) {
            $spreadsheet->getActiveSheet()->setCellValue($cellsX[$key] . $this->lastFilledOutCellY, $total);
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
            'A1' => 'REGION ' . $this->region->getName(),
            'A2' => 'IQPR SUMMARY FORM',
            'A3' => $this->quarter->getName() . ' QTR, ' . $this->quarter->getYear(),
            'A4' => "B.2 and B.3  VPA INVOLVEMENT IN RJ PROCESSES/ RJ ACTIVITIES FOR VICTIMS/ CIVIL LIABILITIES",
            'A5' => 'FIELD OFFICES',
            'B5' => 'NO. OF VPAs INVOLVED IN RJ PROCESS',
            'D5' => 'NO. OF RJ RELATED  ACTS./INTERVENTIONS FOR VICTIMS',
            'F5' => 'C   I   V   I   L   L   I   A   B   I   L   I   T   Y',
            'F6' => 'TOTAL NO. OF CLIENTS W/  CL (SUPERVISION)',
            'G6' => 'TOTAL NO. OF CLIENTS W/ CL (PETITIONERS)',
            'H6' => 'ORIGINAL AMOUNT',
            'I6' => 'START OF QTR.',
            'J6' => 'TOTAL NO. OF CLIENTS WHO PAID',
            'L6' => 'TOTAL AMOUNT PAID',
            'N6' => 'BALANCE END OF QTR.',
            'O6' => 'TOTAL AMT. REMITTED RECEIVED BY VICTIMS/ BENEFICIARIES',
            'B6' => 'FOR CLIENTS UNDER ACTIVE SUPV.',
            'C6' => 'Petitioners',
            'D7' => 'ACTIVE SUPV.',
            'E7' => 'Petitioners',
            'J7' => 'ACTIVE SUPV.',
            'K7' => 'Petitioners',
            'L7' => 'ACTIVE SUPV.',
            'M7' => 'Petitioners',
            'O7' => 'ACTIVE SUPV.',
            'P7' => 'Petitioners',
            'A9' => 'Total',
        ];

        $mergesCoordinates = [
            'A5:A7','B5:C5','D5:E6','F5:P5','B6:B7','C6:C7','F6:F7','G6:G7','H6:H7','I6:I7','J6:K6','L6:M6','N6:N7',
            'O6:P6',
        ];

        $boldCoordinates = ['A1:P7', 'A9'];

        $verticalAlignedCoordinates = ['A5:P9' => 'center'];

        $horizontalAlignedCoordinates = ['A5:P9' => 'center'];

        $adjustedColumnWidthCoordinates = [
            'A' => 25, 'B' => 15, 'C' => 15, 'D' => 10, 'E' => 10, 'F' => 20, 'G' => 20, 'H' => 20, 'I' => 20,
            'J' => 10, 'K' => 10, 'L' => 20, 'M' => 10, 'O' => 10, 'P' => 10
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

        return $spreadsheet;
    }

    private function getProcessData(int $quarterId, array $fieldOfficesId): array
    {
        $results = [];
        $processes = $this->conductProcessesRepository->findByFieldOfficesId($quarterId, $fieldOfficesId);

        foreach ($processes as $fieldOfficeId => $process) {
            foreach ($process as $item) {
                $group = $item['rj_group'];

                if (! isset($results[$fieldOfficeId][$group])) {
                    $results[$fieldOfficeId][$group] = 0;
                }

                $results[$fieldOfficeId][$group]++;
            }
        }

        return $results;
    }

    private function getActivities(int $quarterId, array $fieldOfficesId): array
    {
        $results = [];
        $activities = $this->relatedActivitiesRepository->findByFieldOfficesId($quarterId, $fieldOfficesId);

        foreach ($activities as $fieldOfficeId => $activity) {
            foreach ($activity as $item) {
                $group = $item['rj_group'];

                if (! isset($results[$fieldOfficeId][$group])) {
                    $results[$fieldOfficeId][$group] = 0;
                }

                $results[$fieldOfficeId][$group]++;
            }
        }

        return $results;
    }

    private function getRestitutions(int $quarterId, array $fieldOfficesId): array
    {
        $results = [];
        $restitutions = $this->restitutionsRepository->findByFieldOfficesId($quarterId, $fieldOfficesId);

        foreach ($restitutions as $fieldOfficeId => $restitution) {
            foreach ($restitution as $item) {
                $group = $item['rj_group'];

                if (! isset($results[$fieldOfficeId][$group])) {
                    $results[$fieldOfficeId][$group] = 0;
                }

                $results[$fieldOfficeId][$group]++;

                if (! isset($results[$fieldOfficeId]['originalAmount'])) {
                    $results[$fieldOfficeId]['originalAmount'] = 0;
                }

                $results[$fieldOfficeId]['originalAmount'] += (int) $item['original_amount'];

                if (! isset($results[$fieldOfficeId]['startOfQuarter'])) {
                    $results[$fieldOfficeId]['startOfQuarter'] = 0;
                }

                $results[$fieldOfficeId]['startOfQuarter'] += (int) $item['start_of_quarter'];

                if (! isset($results[$fieldOfficeId]['balance'])) {
                    $results[$fieldOfficeId]['balance'] = 0;
                }

                $results[$fieldOfficeId]['balance'] += (int) $item['balance'];

                if (! isset($results[$fieldOfficeId]['group'][$group]['paymentAmount'])) {
                    $results[$fieldOfficeId]['group'][$group]['paymentAmount'] = 0;
                }

                $results[$fieldOfficeId]['group'][$group]['paymentAmount'] += (int) $item['payment_amount'];

                if (! isset($results[$fieldOfficeId]['group'][$group]['remittedAmount'])) {
                    $results[$fieldOfficeId]['group'][$group]['remittedAmount'] = 0;
                }

                $results[$fieldOfficeId]['group'][$group]['remittedAmount'] += (int) $item['remitted_amount'];
            }
        }

        return $results;
    }
}