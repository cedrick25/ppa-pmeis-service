<?php

namespace App\Report\Table;

use App\Entity\Quarters;
use App\Entity\Regions;
use App\Repository\ClientSessionsRepository;
use App\Repository\ClientsRepository;
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
        private ClientsRepository           $clientsRepository,
        private FieldOfficesRepository       $fieldOfficesRepository,
        private array                       $data = [],
        private array                       $sessionIds = [],
        private ?Regions                    $region = null,
        private ?Quarters                   $quarter = null,
        private int                         $lastFilledOutCellY = 12,
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

        foreach ($this->data as $fieldOffice => $sessions) {
            $this->lastFilledOutCellY++;

            $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, $fieldOffice);

            foreach ($sessions['treatment_categories'] as $category=>$treatmentCategory) {
                foreach ($treatmentCategory as $subCategory=>$score) {
                    $spreadsheet->getActiveSheet()->setCellValue(
                        $treatmentCategoryCells[$category][$subCategory] . $this->lastFilledOutCellY,
                        $score
                    );
                }
            }

            $spreadsheet->getActiveSheet()->setCellValue('N' . $this->lastFilledOutCellY, $sessions['client_frequency_active_supervision']);
            $spreadsheet->getActiveSheet()->setCellValue('O' . $this->lastFilledOutCellY, $sessions['client_frequency_others']);
            $spreadsheet->getActiveSheet()
                ->getStyle('A' . $this->lastFilledOutCellY . ':O' . $this->lastFilledOutCellY)
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
        }

        $this->lastFilledOutCellY++;
        $beforePlotCellY = $this->lastFilledOutCellY + 1;

        $spreadsheet = $this->plotContinuationHeader($spreadsheet);

        foreach ($this->data as $fieldOffice => $sessions) {
            $this->lastFilledOutCellY++;

            $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, $fieldOffice);
            $spreadsheet->getActiveSheet()->setCellValue('B' . $this->lastFilledOutCellY, $sessions['fsg_frequency']);
            $spreadsheet->getActiveSheet()->setCellValue('C' . $this->lastFilledOutCellY, 'No Data.');
            $spreadsheet->getActiveSheet()->setCellValue('D' . $this->lastFilledOutCellY, 'No Data.');
            $spreadsheet->getActiveSheet()->setCellValue('E' . $this->lastFilledOutCellY, 'No Data.');
            $spreadsheet->getActiveSheet()->setCellValue('F' . $this->lastFilledOutCellY, 'No Data.');
            $spreadsheet->getActiveSheet()->setCellValue('G' . $this->lastFilledOutCellY, 'No Data.');
            $spreadsheet->getActiveSheet()->setCellValue('H' . $this->lastFilledOutCellY, 'No Data.');
            $spreadsheet->getActiveSheet()->setCellValue('I' . $this->lastFilledOutCellY, 'No Data.');
            $spreadsheet->getActiveSheet()->setCellValue('J' . $this->lastFilledOutCellY, 'No Data.');
            $spreadsheet->getActiveSheet()->setCellValue('K' . $this->lastFilledOutCellY, 'No Data.');
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
        $regionId = intval($data['region_id']);
        $data = [];

        $this->region = $this->regionsRepository->find($regionId);
        $this->quarter = $this->quartersRepository->find($quarterId);

        $quarterData = $this->quartersRepository->find($quarterId);
        $minMaxDate = $this->quartersRepository->getQuarterMinMaxDate($quarterData);
        $fieldOffices = $this->fieldOfficesRepository->findBy(['regionId' => $regionId]);

        foreach ($fieldOffices as $fieldOffice) {
            $treatmentCategoryTotal = $this->getTreatmentCategoriesData(
                $minMaxDate['min'],
                $minMaxDate['max'],
                $fieldOffice->getFieldOfficeId()
            );

            if (count($treatmentCategoryTotal) < 1) {
                continue;
            }

            $clientSessionsData = $this->getClientSessionsData();

            $data[$fieldOffice->getName()] = [
                'treatment_categories' => $treatmentCategoryTotal,
                'client_frequency_active_supervision' => $clientSessionsData['client_frequency']['active_supervision'],
                'client_frequency_others' => $clientSessionsData['client_frequency']['others'],
                'fsg_frequency' => $clientSessionsData['fsg_frequency'],
            ];
        }

        return $data;
    }

    private function getTreatmentCategoriesData(string $minDate, string $maxDate, int $fieldOfficeId): array
    {
        $treatmentCategoryTotal = ['MTCS' => ['Total' => 0], 'RA' => ['Total' => 0]];
        $sessions = $this->sessionsRepository
            ->getTableIA1SummaryFormTreatmentCategoryData($minDate, $maxDate, $fieldOfficeId);

        if (empty($sessions)) {
            return [];
        }

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

    private function getClientSessionsData(): array
    {
        $fsgClients = [];
        $clientsId = ['active_supervision' => [], 'others' => []];
        $clientSessions = $this->clientSessionsRepository->findBySessionIds($this->sessionIds);

        foreach ($clientSessions as $clientSession) {
            $clientId = (int) $clientSession['client_id'];

            if ('Pet' == $clientSession['role'] || 'Term' == $clientSession['role']) {
                $clientsId['others'][] = $clientId;
            } else {
                $clientsId['active_supervision'][] = $clientId;
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
}