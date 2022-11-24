<?php

namespace App\Report\Table;

use App\Entity\FieldOffices;
use App\Entity\Quarters;
use App\Enum\SystemSettingNames;
use App\Repository\ClientSessionsRepository;
use App\Repository\ClientsRepository;
use App\Repository\FieldOfficesRepository;
use App\Repository\QuartersRepository;
use App\Repository\SessionsRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TableIA1SummaryForm implements Form
{
    private const TABLE_NAME = "TableIA1SummaryForm";


    public function __construct(
        private FieldOfficesRepository      $fieldOfficesRepository,
        private QuartersRepository          $quartersRepository,
        private SessionsRepository          $sessionsRepository,
        private ClientSessionsRepository    $clientSessionsRepository,
        private ClientsRepository           $clientsRepository,
        private array                       $data = [],
        private array                       $sessionIds = [],
        private ?FieldOffices               $fieldOffice = null,
        private ?Quarters                   $quarters = null,
    ){}

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
        $this->data = $this->getData($data);
        $this->data[SystemSettingNames::GENERATED_REPORTS_CODE] = $data[SystemSettingNames::GENERATED_REPORTS_CODE];

        $spreadsheet = $this->footer();
        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");

        $filePath = $_ENV['XLSX_PATH_FILE'] . self::TABLE_NAME . "-" . time() . ".xlsx";
        $writer->save($filePath);

        return new BinaryFileResponse($filePath);
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function header(): Spreadsheet
    {
        $spreadsheet = $this->prepare();
        $spreadsheet->getActiveSheet()->getStyle('A7:G12')
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $spreadsheet;
    }

    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();
        $treatmentCategoryCells = [
            'MTCS' => [
                'RBM' => 'B10', 'AEP' => 'C10', 'S' => 'D10', 'CI' => 'E10', 'PVS' => 'F10', 'Total' => 'G10',
            ],
            'RA' => [
                'RBM' => 'B11', 'AEP' => 'C11', 'S' => 'D11', 'CI' => 'E11', 'PVS' => 'F11', 'Total' => 'G11',
            ]
        ];

        foreach ($this->data['treatment_categories'] as $category => $treatmentCategory) {
            foreach ($treatmentCategory as $subCategory => $score) {
                $spreadsheet->getActiveSheet()->setCellValue($treatmentCategoryCells[$category][$subCategory], $score);
            }
        }

        $spreadsheet->getActiveSheet()->setCellValue('E14', $this->data['client_frequency_active_supervision']);
        $spreadsheet->getActiveSheet()->setCellValue('E17', $this->data['client_frequency_others']);
        $spreadsheet->getActiveSheet()->setCellValue('E20', $this->data['fsg_frequency']);
        $spreadsheet->getActiveSheet()->setCellValue('E23', $this->data['vpa_head_count']);
        $spreadsheet->getActiveSheet()->setCellValue('E26', $this->data['vpa_frequency']);
        $spreadsheet->getActiveSheet()->setCellValue('E27', $this->data['tress_planting_and_others']);
        $spreadsheet->getActiveSheet()->setCellValue('E30', $this->data['tress_planting']);
        $spreadsheet->getActiveSheet()->setCellValue('E34', $this->data['tress_planted']);
        $spreadsheet->getActiveSheet()->setCellValue('E37', $this->data['communityService']);
        $spreadsheet->getActiveSheet()->setCellValue('E40', $this->data['selfHelp']);
        $spreadsheet->getActiveSheet()->setCellValue('E44', $this->data['selfHelpActivity']);

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
            'G1' => 'Field Office IQPR FORM' . $this->data[SystemSettingNames::GENERATED_REPORTS_CODE],
            'A1' => 'FIELD OFFICE ' . $this->fieldOffice->getName(),
            'A2' => 'IQPR SUMMARY FORM',
            'A3' => $this->quarters->getName() . ' QTR, ' . $this->quarters->getYear(),
            'A4' => 'I.    PROGRAM IMPLEMENTATION',
            'A5' => 'A.1   Therapeutic Community Ladderized Program (TCLP)',
            'A6' => "Table  I.A.1  Clients'/ FSG Involvement by Phase/ Session/ Activity/ Treatment!Category",
            'A7' => 'SESSION / ACTIVITIES',
            'B7' => 'TOTAL NUMBER',
            'G7' => 'TOTAL',
            'B8' => 'TREATMENT CATEGORY',
            'B9' => 'RBM',
            'C9' => 'AEP',
            'D9' => 'S',
            'E9' => 'CI',
            'F9' => 'PVS',
            'A10' => 'MTCS',
            'A11' => 'RA',
            'A14' => "FREQUENCY OF CLIENTS' Involvement",
            'A15' => "  (Total of Table I.A.1,  Col.6 under Active Supervision)",
            'A17' => "FREQUENCY OF OTHER CLIENTS' Involvement",
            'A18' => "  (Total of Table I.A.1,  Col.6 under OTHERS)",
            'A20' => "FREQUENCY OF FSG Involvement",
            'A21' => "  (Total of Table I.A.1,  Col.3 under FSG)",
            'A23' => "TOTAL NUMBER OF VPAs Involved (Headcount)",
            'A24' => "  (Table I.A.1,  Col. 7)",
            'A26' => "TOTAL NUMBER OF PARTICIPANTS TO TREE PLANTING",
            'A27' => "AND OTHER RELATED TREE PLANTING ACTIVITIES",
            'A28' => "  (Table I.A.1,  Cols. 2 & 6)",
            'A30' => "TOTAL NUMBER OF TREE PLANTING AND OTHER",
            'A31' => "RELATED ACTIVITIES CONDUCTED",
            'A32' => "  (Table I.A.1,  Col. 2)",
            'A34' => "TOTAL NUMBER OF TREES PLANTED",
            'A35' => "  (Table I.A.1,  Col. 8)",
            'A37' => "TOTAL NUMBER OF COMMUNITY SERVICES AND",
            'A38' => "OTHER RELATED ACTIVITIES",
            'A39' => "  (Table I.A.1,  Cols. 2 & 8)",
            'A40' => "TOTAL NUMBER OF COOPERATIVE/SELF-HELP",
            'A41' => "ASSOCIATIONS",
            'A42' => "  (Table I.A.1,  Cols. 2 & 8)",
            'A44' => "TOTAL NUMBER OF CLIENT'S INVOLVED IN COOPERATIVE/",
            'A45' => "SELF-HELP ASSOCIATIONS RELATED ACTIVITIES",
            'A46' => "  (Table I.A.1,  Cols. 2 & 6)",
        ];

        $mergesCoordinates = [
            'A1:G1','A2:G2','A3:G3', 'A7:A9', 'B7:F7', 'G7:G9', 'B8:F8'
        ];

        $boldCoordinates = [
            'A1:A7', 'B7', 'G7', 'B8', 'B9:f9', 'A10', 'A11'
        ];

        $verticalAlignedCoordinates = [
            'A1:G1' => 'center', 'A2:G2' => 'center', 'A3:G3' => 'center', 'A7:G12' => 'center'
        ];

        $horizontalAlignedCoordinates = [
            'A1:G1' => 'center', 'A2:G2' => 'center', 'A3:G3' => 'center', 'A7:G12' => 'center'
        ];

        $adjustedColumnWidthCoordinates = [
            'A' => 35, 'G' => 15
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

    private function getData(array $data): array
    {
        $fieldOfficeId = (int) $data['field_office_id'];
        $quarterId = (int) $data['quarter_id'];

        $this->fieldOffice = $this->fieldOfficesRepository->find($fieldOfficeId);
        $this->quarters = $this->quartersRepository->find($quarterId);

        $quarterData = $this->quartersRepository->find($quarterId);
        $minMaxDate = $this->quartersRepository->getQuarterMinMaxDate($quarterData);
        // has side effect of populating $this->sessionIds
        $treatmentCategoryTotal = $this->getTreatmentCategoriesData(
            $minMaxDate['min'],
            $minMaxDate['max'],
            $fieldOfficeId
        );
        $clientSessionsData = $this->getClientSessionsData($minMaxDate, $fieldOfficeId);
        $tca1Part2 = $this->sessionsRepository->fetchTCA1Part2($fieldOfficeId, $quarterData);
        $vpa = $this->extractVpa($tca1Part2);
        $sessions = $this->sessionsRepository->findWithActivitiesByIds($this->sessionIds);
        $activities = $this->getActivities($sessions);

        return [
            'treatment_categories' => $treatmentCategoryTotal,
            'client_frequency_active_supervision' => $clientSessionsData['client_frequency']['active_supervision'],
            'client_frequency_others' => $clientSessionsData['client_frequency']['others'],
            'fsg_frequency' => $clientSessionsData['fsg_frequency'],
            'vpa_head_count' => $vpa['headCount'],
            'vpa_frequency' => $vpa['frequency'],
            'tress_planting_and_others' => $activities['treesPlantedActivitiesAndOthers'],
            'tress_planting' => $activities['treesPlantedActivities'],
            'tress_planted' => $activities['treesPlanted'],
            'communityService' => $activities['communityService'],
            'selfHelp' => $activities['selfHelp'],
            'selfHelpActivity' => $activities['selfHelpActivity'],
        ];
    }

    private function getTreatmentCategoriesData(string $minDate, string $maxDate, int $fieldOfficeId): array
    {
        $treatmentCategoryTotal = ['MTCS' => ['Total' => 0], 'RA' => ['Total' => 0]];
        $sessions = $this->sessionsRepository->getTableIA1SummaryFormTreatmentCategoryData(
            $minDate,
            $maxDate,
            $fieldOfficeId
        );

        foreach ($sessions as $session) {
            $treatmentCategories = explode('-', $session['treatment_category']);
            // This is bad, it is classified as side effect.
            $this->sessionIds[] = intval($session['session_id']);

            if (! isset($treatmentCategoryTotal[$treatmentCategories[0]][$treatmentCategories[1]])) {
                $treatmentCategoryTotal[$treatmentCategories[0]][$treatmentCategories[1]] = 0;
            }

            $treatmentCategoryTotal[$treatmentCategories[0]]['Total']++;
            $treatmentCategoryTotal[$treatmentCategories[0]][$treatmentCategories[1]]++;
        }

        return $treatmentCategoryTotal;
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function getClientSessionsData(array $minMaxDate, int $fieldOfficeId): array
    {
        $fsgClients = [];
        $clientsId = ['active_supervision' => [], 'others' => []];
        if (empty($this->sessionIds)) {
            return [
                'client_frequency' => $clientsId,
                'fsg_frequency' => count(array_unique($fsgClients)),
            ];
        }
        $clientSessions = $this->clientSessionsRepository->findBySessionIds($this->sessionIds);
        $clientsIdUnderSupervision = $this->clientsRepository
            ->findClientsIdUnderSupervisionPeriod($minMaxDate, $fieldOfficeId);

        foreach ($clientSessions as $clientSession) {
            $clientId = (int) $clientSession['client_id'];

            if (in_array($clientId, $clientsIdUnderSupervision)) {
                $clientsId['active_supervision'][] = $clientId;
            } else {
                $clientsId['others'][] = $clientId;
            }

            if (intval($clientSession['fsi'])) {
                $fsgClients[] = $clientId;
            }
        }

        $clientsId['active_supervision'] = count(array_unique($clientsId['active_supervision']));
        $clientsId['others'] = count(array_unique($clientsId['others']));

        return [
            'client_frequency' => $clientsId,
            'fsg_frequency' => count(array_unique($fsgClients)),
        ];
    }

    private function extractVpa(array $part2): array
    {
        $vpaHeadCount = [];
        $vpaFrequency = 0;

        foreach ($part2 as $item) {
            foreach ($item['vpa_resource_person'] as $vpa) {
                $vpaFrequency++;
                $vpaHeadCount[] = $vpa['volunteer_id'];
            }
        }

        return [
            'headCount' => \count(\array_unique($vpaHeadCount)),
            'frequency' => $vpaFrequency,
        ];
    }

    private function getActivities(array $sessions): array
    {
        $tressPlanted = 0;
        $treesPlantedActivities = 0;
        $otherActivities = 0;
        $communityService = 0;
        $selfHelp = 0;
        $selfHelpActivity = 0;

        foreach ($sessions as $session) {
            $isTreePlanted = boolval($session['is_tree_planting']);
            $isCommunityService = boolval($session['is_community_service']);
            $isCooperativeSelfHelp = boolval($session['is_cooperative_self_help']);
            $isCooperativeSelfHelpActivity = boolval($session['is_cooperative_self_help_activities']);

            if ($isTreePlanted) {
                $tressPlanted += (int) $session['trees_planted'];
                $treesPlantedActivities++;
            }

            $communityService += $isCommunityService ? 1 : 0;
            $selfHelp += $isCooperativeSelfHelp ? 1 : 0;
            $selfHelpActivity += $isCooperativeSelfHelpActivity ? 1 : 0;
        }

        return [
            'treesPlantedActivitiesAndOthers' => $treesPlantedActivities + $otherActivities,
            'treesPlantedActivities' => $treesPlantedActivities,
            'treesPlanted' => $tressPlanted,
            'communityService' => $communityService,
            'selfHelp' => $selfHelp,
            'selfHelpActivity' => $selfHelpActivity,
        ];
    }
}