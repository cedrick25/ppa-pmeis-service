<?php

namespace App\Report\Table;

use App\Entity\Quarters;
use App\Repository\ClientSessionsRepository;
use App\Repository\FieldOfficesRepository;
use App\Repository\QuartersRepository;
use App\Repository\RegionsRepository;
use App\Repository\SessionsRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TableIA1SummaryFormNational implements Form
{
    private const TABLE_NAME = "TableIA1SummaryFormNational";


    public function __construct(
        private RegionsRepository           $regionsRepository,
        private QuartersRepository          $quartersRepository,
        private SessionsRepository          $sessionsRepository,
        private ClientSessionsRepository    $clientSessionsRepository,
        private FieldOfficesRepository       $fieldOfficesRepository,
        private array                       $data = [],
        private array                       $sessionIds = [],
        private ?Quarters                   $quarter = null,
        private int                         $lastFilledOutCellY = 12,
    ) {}

    public function supports(string $tableName): bool
    {
        return self::TABLE_NAME === $tableName;
    }

    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function generate(array $data): BinaryFileResponse
    {
        // TODO: Return per region instead of field office
        $this->data = $this->getData($data);

        $spreadsheet = $this->footer();
        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");

        $filePath = $_ENV['XLSX_PATH_FILE'] . self::TABLE_NAME . "-" . time() . ".xlsx";
        $writer->save($filePath);

        return new BinaryFileResponse($filePath);
    }

    public function header(): Spreadsheet
    {
        $spreadsheet = $this->prepare();

        $spreadsheet->getActiveSheet()->getStyle('A9:O12')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $spreadsheet;
    }

    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();

        $treatmentCategoryCells = [
            'MTCS' => ['RBM' => 'B', 'AEP' => 'C', 'S' => 'D', 'CI' => 'E', 'PVS' => 'F', 'Total' => 'G'],
            'RA' => ['RBM' => 'H', 'AEP' => 'I', 'S' => 'J', 'CI' => 'K', 'PVS' => 'L', 'Total' => 'M']
        ];

        foreach ($this->data as $region => $sessions) {
            $this->lastFilledOutCellY++;

            $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, $region);

            foreach ($sessions['treatment_categories'] as $treatmentCategories) {
                foreach ($treatmentCategories as $category => $treatmentCategory) {
                    foreach ($treatmentCategory as $subCategory => $score) {
                        $spreadsheet->getActiveSheet()->setCellValue(
                            $treatmentCategoryCells[$category][$subCategory] . $this->lastFilledOutCellY,
                            $score
                        );
                    }
                }
            }

            $spreadsheet->getActiveSheet()->setCellValue(
                'N' . $this->lastFilledOutCellY,
                $sessions['client_frequency_active_supervision']
            );
            $spreadsheet->getActiveSheet()->setCellValue(
                'O' . $this->lastFilledOutCellY,
                $sessions['client_frequency_others']
            );
            $spreadsheet->getActiveSheet()
                ->getStyle('A' . $this->lastFilledOutCellY . ':O' . $this->lastFilledOutCellY)
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
        }

        $this->lastFilledOutCellY += 2;
        $beforePlotCellY = $this->lastFilledOutCellY + 1;

        $spreadsheet = $this->plotContinuationHeader($spreadsheet);

        foreach ($this->data as $fieldOffice => $sessions) {
            $this->lastFilledOutCellY++;

            $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, $fieldOffice);
            $spreadsheet->getActiveSheet()->setCellValue('B' . $this->lastFilledOutCellY, $sessions['fsg_frequency']);
            $spreadsheet->getActiveSheet()->setCellValue('C' . $this->lastFilledOutCellY, $sessions['total_vpa']);
            $spreadsheet->getActiveSheet()->setCellValue('D' . $this->lastFilledOutCellY, $sessions['vpa_frequency']);
            $spreadsheet->getActiveSheet()->setCellValue(
                'E' . $this->lastFilledOutCellY,
                $sessions['tree_planting_participants']
            );
            $spreadsheet->getActiveSheet()->setCellValue(
                'F' . $this->lastFilledOutCellY,
                $sessions['tree_planting_activity']
            );
            $spreadsheet->getActiveSheet()->setCellValue(
                'G' . $this->lastFilledOutCellY,
                $sessions['tree_planting_planted']
            );
            $spreadsheet->getActiveSheet()->setCellValue(
                'H' . $this->lastFilledOutCellY,
                $sessions['community_service']
            );
            $spreadsheet->getActiveSheet()->setCellValue(
                'I' . $this->lastFilledOutCellY,
                $sessions['self_help_association']
            );
            $spreadsheet->getActiveSheet()->setCellValue(
                'J' . $this->lastFilledOutCellY,
                $sessions['self_help_activity']
            );
            $spreadsheet->getActiveSheet()->setCellValue(
                'K' . $this->lastFilledOutCellY,
                $sessions['self_help_clients']
            );
        }

        $spreadsheet->getActiveSheet()
            ->getStyle('A' . $beforePlotCellY . ':K' . $this->lastFilledOutCellY)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $spreadsheet;
    }

    public function footer(): Spreadsheet
    {
        return $this->body();
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function prepare(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $textAndCoordinates = [
            'E1' => 'AGENCY IQPR CONSOLIDATION FORM - PPA-PLD-FR-001',
            'A3' => 'DOJ-PPA IQPR CONSOLIDATED REPORT',
            'A4' => $this->quarter->getName() . ' QTR, ' . $this->quarter->getYear(),
            'A5' => 'I.    PROGRAM IMPLEMENTATION',
            'A6' => 'A.1   Therapeutic Community Ladderized Program (TCLP)',
            'A7' => "1.  CLIENTS'/FSG INVOLVEMENT BY PHASE/SESSION Activity",
            'A8' => "1.a  FREQUENCY OF CLIENTS' ATTENDANCE",
            'A9' => "REGION OFFICES",
            'B9' => 'TOTAL NUMBER',
            'N9' => 'FREQUENCY OF',
            'B10' => 'SESSION / ACTIVITIES / REATMENT CATEGORY',
            'B11' => 'MTCS',
            'H11' => 'RA',
            'N11' => "Clients' Involvement (Active Supervision)",
            'O11' => "Other Clients' Involvement (Petitioners/Terminated)",
            'B12' => 'RBM',
            'C12' => 'AEP',
            'D12' => 'S',
            'E12' => 'CI',
            'F12' => 'PVS',
            'G12' => 'TOTAL',
            'H12' => 'RBM',
            'I12' => 'AEP',
            'J12' => 'S',
            'K12' => 'CI',
            'L12' => 'PVS',
            'M12' => 'TOTAL',
        ];

        $mergesCoordinates = ['A9:A12', 'B9:M9', 'B10:M10', 'N10:O10', 'B11:G11', 'H11:M11'];

        $verticalAlignedCoordinates = ['A9:O12' => 'center'];

        $horizontalAlignedCoordinates = ['A9:O12' => 'center'];

        $adjustedColumnWidthCoordinates = ['A' => 35, 'N' => 15, 'O' => 15];

        foreach ($textAndCoordinates as $coordinate => $text) {
            $spreadsheet->getActiveSheet()->setCellValue($coordinate, $text);
        }

        foreach ($mergesCoordinates as $coordinate) {
            $spreadsheet->getActiveSheet()->mergeCells($coordinate);
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

    private function getData(array $data): array
    {
        $quarterId = intval($data['quarter_id']);
        $data = [];

        $this->quarter = $this->quartersRepository->find($quarterId);

        $quarterData = $this->quartersRepository->find($quarterId);
        $minMaxDate = $this->quartersRepository->getQuarterMinMaxDate($quarterData);
        $regions = $this->regionsRepository->findAll();

        foreach ($regions as $region) {
            $fieldOffices = $this->fieldOfficesRepository->findBy(['regionId' => $region->getRegionId()]);
            $fieldOfficesId = array_map(fn($fieldOffice) => $fieldOffice->getFieldOfficeId(), $fieldOffices);
            $treatmentCategoryTotals = $this->getTreatmentCategoriesData(
                $minMaxDate['min'],
                $minMaxDate['max'],
                $fieldOfficesId
            );
            $clientSessionsData = $this->getClientSessionsData();
            $vpas = $this->getVpas();
            $activities = $this->activities();

            $totals = [
                'treatment_categories' => [],
                'client_frequency_active_supervision' => 0,
                'client_frequency_others' => 0,
                'fsg_frequency' => 0,
                'total_vpa' => 0,
                'vpa_frequency' => 0,
                'tree_planting_participants' => 0,
                'tree_planting_activity' => 0,
                'tree_planting_planted' => 0,
                'community_service' => 0,
                'self_help_association' => 0,
                'self_help_activity' => 0,
                'self_help_clients' => 0,
            ];

            foreach ($fieldOffices as $fieldOffice) {
                $treatmentCategoryTotal = $treatmentCategoryTotals[$fieldOffice->getFieldOfficeId()] ?? [];
                $clientFrequency = $clientSessionsData['client_frequency'][$fieldOffice->getFieldOfficeId()] ?? [];
                $fsgFrequency = $clientSessionsData['fsg_frequency'][$fieldOffice->getFieldOfficeId()] ?? [];
                $vpa = $vpas[$fieldOffice->getFieldOfficeId()] ?? [];
                $activity = $activities[$fieldOffice->getFieldOfficeId()] ?? [];

                if (empty($treatmentCategoryTotal)) {
                    continue;
                }

                $totals['treatment_categories'][] = $treatmentCategoryTotal;
                $totals['client_frequency_active_supervision'] += $clientFrequency['active_supervision'] ?? 0;
                $totals['client_frequency_others'] += $clientFrequency['others'] ?? 0;
                $totals['fsg_frequency'] += $fsgFrequency;
                $totals['total_vpa'] += \count(\array_unique($vpa['total']));
                $totals['vpa_frequency'] += $vpa['frequency'];
                $totals['tree_planting_participants'] += $activity['trees_planting']['participants'];
                $totals['tree_planting_activity'] += $activity['trees_planting']['activity'];
                $totals['tree_planting_planted'] += $activity['trees_planting']['planted'];
                $totals['community_service'] += $activity['communityService'];
                $totals['self_help_association'] += $activity['selfHelp']['association'];
                $totals['self_help_activity'] += $activity['selfHelp']['activity'];
                $totals['self_help_clients'] += $activity['selfHelp']['clients'];
            }

            $data[$region->getName()] = $totals;
        }

        return $data;
    }

    private function getTreatmentCategoriesData(string $minDate, string $maxDate, array $fieldOfficesId): array
    {
        $treatmentCategoryTotal = [];
        $sessions = $this->sessionsRepository->getTableIA1SummaryFormTreatmentCategoriesData(
            $minDate,
            $maxDate,
            $fieldOfficesId
        );

        if (empty($sessions)) {
            return [];
        }

        foreach ($sessions as $session) {
            $fieldOfficeId = $session['field_office_id'];
            $treatmentCategories = explode('-', $session['treatment_category']);
            // This is bad, it is classified as side effect.
            $this->sessionIds[] = intval($session['session_id']);

            if (! isset($treatmentCategoryTotal[$fieldOfficeId][$treatmentCategories[0]][$treatmentCategories[1]])) {
                $treatmentCategoryTotal[$fieldOfficeId][$treatmentCategories[0]][$treatmentCategories[1]] = 0;
            }

            $treatmentCategoryTotal[$fieldOfficeId][$treatmentCategories[0]][$treatmentCategories[1]]++;

            if (! isset($treatmentCategoryTotal[$fieldOfficeId][$treatmentCategories[0]]['Total'])) {
                $treatmentCategoryTotal[$fieldOfficeId][$treatmentCategories[0]]['Total'] = 0;
            }

            $treatmentCategoryTotal[$fieldOfficeId][$treatmentCategories[0]]['Total']++;
        }

        return $treatmentCategoryTotal;
    }

    private function getClientSessionsData(): array
    {
        $fsgClients = [];
        $clientsFrequency = [];
        $clientSessions = $this->clientSessionsRepository->findBySessionIds($this->sessionIds);

        foreach ($clientSessions as $clientSession) {
            $fieldOfficeId = (int) $clientSession['field_office_id'];

            if ('Pet' == $clientSession['role'] || 'Term' == $clientSession['role']) {
                if (! isset($clientsFrequency[$fieldOfficeId]['others'])) {
                    $clientsFrequency[$fieldOfficeId]['others'] = 0;
                }

                $clientsFrequency[$fieldOfficeId]['others']++;
            } else {
                if (! isset($clientsFrequency[$fieldOfficeId]['active_supervision'])) {
                    $clientsFrequency[$fieldOfficeId]['active_supervision'] = 0;
                }

                $clientsFrequency[$fieldOfficeId]['active_supervision']++;
            }

            if (intval($clientSession['fsi'])) {
                if (! isset($fsgClients[$fieldOfficeId])) {
                    $fsgClients[$fieldOfficeId] = 0;
                }

                $fsgClients[$fieldOfficeId]++;
            }
        }

        return [
            'client_frequency' => $clientsFrequency,
            'fsg_frequency' => $fsgClients,
        ];
    }

    private function plotContinuationHeader(Spreadsheet $spreadsheet): Spreadsheet
    {
        $this->lastFilledOutCellY++;
        $twoRowLastFilledOutCellY = $this->lastFilledOutCellY + 1;

        $textAndCoordinates = [
            'A' . $this->lastFilledOutCellY => "REGIONAL OFFICES",
            'B' . $this->lastFilledOutCellY => "Frequency of FSG Involvement",
            'C' . $this->lastFilledOutCellY => "VPA INVOLVEMENT",
            'E' . $this->lastFilledOutCellY => "TREE PLANTING",
            'H' . $this->lastFilledOutCellY => "Total Number of Community and Other Related1Activities",
            'I' . $this->lastFilledOutCellY => "Cooperative/Self-Help Associations",
            'C' . $twoRowLastFilledOutCellY => "Total Number of VPAs Involved",
            'D' . $twoRowLastFilledOutCellY => "Frequency of VPAs Involvement",
            'E' . $twoRowLastFilledOutCellY => "Total Number of Participants",
            'F' . $twoRowLastFilledOutCellY => "Number of Tree Planting and Other Related1Activities",
            'G' . $twoRowLastFilledOutCellY => "Total Number of Trees Planted",
            'I' . $twoRowLastFilledOutCellY => "Total # of Coop/Self-Help Association",
            'J' . $twoRowLastFilledOutCellY => "Total # of Coop/Self-Help Associations Activities",
            'K' . $twoRowLastFilledOutCellY => "Total # of Clients' Involved",
        ];

        $mergesCoordinates = [
            'A' . $this->lastFilledOutCellY . ':A' . $twoRowLastFilledOutCellY,
            'B' . $this->lastFilledOutCellY . ':B' . $twoRowLastFilledOutCellY,
            'C' . $this->lastFilledOutCellY . ':D' . $this->lastFilledOutCellY,
            'E' . $this->lastFilledOutCellY . ':G' . $this->lastFilledOutCellY,
            'H' . $this->lastFilledOutCellY . ':H' . $twoRowLastFilledOutCellY,
            'I' . $this->lastFilledOutCellY . ':K' . $this->lastFilledOutCellY,
        ];

        $verticalAlignedCoordinates = ['A' . $this->lastFilledOutCellY . ':K' . $twoRowLastFilledOutCellY => 'center'];

        $horizontalAlignedCoordinates = ['A' . $this->lastFilledOutCellY . ':K' . $twoRowLastFilledOutCellY => 'center'];

        $adjustedColumnWidthCoordinates = [
            'A' => 15, 'B' => 15, 'C' => 15, 'E' => 15, 'F' => 15,
            'G' => 15, 'H' => 15, 'I' => 15, 'J' => 15, 'K' => 15,
        ];

        foreach ($textAndCoordinates as $coordinate => $text) {
            $spreadsheet->getActiveSheet()->setCellValue($coordinate, $text);
        }

        foreach ($mergesCoordinates as $coordinate) {
            $spreadsheet->getActiveSheet()->mergeCells($coordinate);
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

        $this->lastFilledOutCellY++;

        return $spreadsheet;
    }

    private function getVpas(): array
    {
        $results = [];
        $vpas = $this->sessionsRepository->getVpaFacilitatorsByIds($this->sessionIds);

        foreach ($vpas as $vpa) {
            $fieldOfficeId = (int) $vpa['field_office_id'];

            if (! isset($results[$fieldOfficeId]['total'])) {
                $results[$fieldOfficeId]['total'] = [];
            }

            $results[$fieldOfficeId]['total'][] = $vpa['resource_facilitator_id'];

            if (! isset($results[$fieldOfficeId]['frequency'])) {
                $results[$fieldOfficeId]['frequency'] = 0;
            }

            $results[$fieldOfficeId]['frequency']++;
        }

        return $results;
    }

    private function activities(): array
    {
        $activities = [];
        $sessions = $this->sessionsRepository->findWithActivitiesByIds($this->sessionIds);
        $clientsPerSessions = $this->formatClientsPerSession(
            $this->clientSessionsRepository->findBySessionIds($this->sessionIds)
        );

        foreach ($sessions as $session) {
            $fieldOfficeId = (int) $session['field_office_id'];
            $sessionId = (int) $session['session_id'];
            $isTreePlanted = boolval($session['is_tree_planting']);
            $isCooperativeSelfHelp = boolval($session['is_cooperative_self_help']);
            $isCooperativeSelfHelpActivity = boolval($session['is_cooperative_self_help_activities']);
            $isCommunityService = boolval($session['is_community_service']);

            if ($isTreePlanted) {
                if (! isset($activities[$fieldOfficeId]['trees_planting']['participants'])) {
                    $activities[$fieldOfficeId]['trees_planting']['participants'] = 0;
                }
                $activities[$fieldOfficeId]['trees_planting']['participants'] += $clientsPerSessions[$sessionId];

                if (! isset($activities[$fieldOfficeId]['trees_planting']['activity'])) {
                    $activities[$fieldOfficeId]['trees_planting']['activity'] = 0;
                }
                $activities[$fieldOfficeId]['trees_planting']['activity']++;

                $tressPlanted = (int) $session['trees_planted'];
                if (! isset($activities[$fieldOfficeId]['trees_planting']['planted'])) {
                    $activities[$fieldOfficeId]['trees_planting']['planted'] = 0;
                }
                $activities[$fieldOfficeId]['trees_planting']['planted'] += $tressPlanted;
            }

            if ($isCooperativeSelfHelp) {
                if (! isset($activities[$fieldOfficeId]['selfHelp']['association'])) {
                    $activities[$fieldOfficeId]['selfHelp']['association'] = 0;
                }
                $activities[$fieldOfficeId]['selfHelp']['association']++;

                if (! isset($activities[$fieldOfficeId]['selfHelp']['clients'])) {
                    $activities[$fieldOfficeId]['selfHelp']['clients'] = 0;
                }
                $activities[$fieldOfficeId]['selfHelp']['clients'] += $clientsPerSessions[$sessionId];
            }

            if ($isCooperativeSelfHelpActivity) {
                if (! isset($activities[$fieldOfficeId]['selfHelp']['activity'])) {
                    $activities[$fieldOfficeId]['selfHelp']['activity'] = 0;
                }
                $activities[$fieldOfficeId]['selfHelp']['activity']++;

                if (!$isCooperativeSelfHelp) {
                    if (! isset($activities[$fieldOfficeId]['selfHelp']['clients'])) {
                        $activities[$fieldOfficeId]['selfHelp']['clients'] = 0;
                    }
                    $activities[$fieldOfficeId]['selfHelp']['clients'] += $clientsPerSessions[$sessionId];
                }
            }

            if ($isCommunityService) {
                if (! isset($activities[$fieldOfficeId]['communityService'])) {
                    $activities[$fieldOfficeId]['communityService'] = 0;
                }
                $activities[$fieldOfficeId]['communityService']++;
            }
        }

        return $activities;
    }

    private function formatClientsPerSession(array $clientsSessions): array
    {
        $results = [];

        foreach ($clientsSessions as $clientsSession) {
            $sessionId = (int) $clientsSession['session_id'];

            if (! isset($results[$sessionId])) {
                $results[$sessionId] = 0;
            }

            $results[$sessionId]++;
        }

        return $results;
    }
}