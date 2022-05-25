<?php

namespace App\Report\Table;

use App\Entity\FieldOffices;
use App\Entity\Quarters;
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

class TableVSummaryForm implements Form
{
    private const TABLE_NAME = "TableVSummaryForm";


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

        $spreadsheet = $this->footer();
        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");

        $filePath = $_ENV['XLSX_PATH_FILE'] . self::TABLE_NAME . "-" . time() . ".xlsx";
        $writer->save($filePath);

        return new BinaryFileResponse($filePath);
    }

    public function header(): Spreadsheet
    {
        $spreadsheet = $this->prepare();

        $thinBorders = [
            "A7:C10"
        ];

        foreach ($thinBorders as $coordinate) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }
        return $spreadsheet;
    }

    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();
        // $treatmentCategoryCells = [
        //     'MTCS' => [
        //         'RBM' => 'B10', 'AEP' => 'C10', 'S' => 'D10', 'CI' => 'E10', 'PVS' => 'F10', 'Total' => 'G10',
        //     ],
        //     'RA' => [
        //         'RBM' => 'B11', 'AEP' => 'C11', 'S' => 'D11', 'CI' => 'E11', 'PVS' => 'F11', 'Total' => 'G11',
        //     ]
        // ];

        // foreach ($this->data['treatment_categories'] as $category=>$treatmentCategory) {
        //     foreach ($treatmentCategory as $subCategory=>$score) {
        //         $spreadsheet->getActiveSheet()->setCellValue($treatmentCategoryCells[$category][$subCategory], $score);
        //     }
        // }
        // $spreadsheet->getActiveSheet()->setCellValue('E14', $this->data['client_frequency_active_supervision']);
        // $spreadsheet->getActiveSheet()->setCellValue('E17', $this->data['client_frequency_others']);
        // $spreadsheet->getActiveSheet()->setCellValue('E20', $this->data['fsg_frequency']);
        // $spreadsheet->getActiveSheet()->setCellValue('E23', 'No Data');
        // $spreadsheet->getActiveSheet()->setCellValue('E26', 'No Data');
        // $spreadsheet->getActiveSheet()->setCellValue('E30', 'No Data');
        // $spreadsheet->getActiveSheet()->setCellValue('E34', 'No Data');
        // $spreadsheet->getActiveSheet()->setCellValue('E37', 'No Data');
        // $spreadsheet->getActiveSheet()->setCellValue('E40', 'No Data');
        // $spreadsheet->getActiveSheet()->setCellValue('E44', 'No Data');

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
            'A1' => 'FIELD OFFICE ' . $this->fieldOffice->getName(),
            'A2' => 'IQPR SUMMARY FORM',
            'A3' => $this->quarters->getName() . ' QTR, ' . $this->quarters->getYear(),
            'A4' => 'V. PROGRAM AND MATERIALS DEVELOPMENT',
            'A5' => 'Table V. Materials/ Session Plans Developed and Used for Agency Program',
            'A7' => 'Program',
            'A9' => '1. Therapeutic Community',
            'A10' => '2. Restorative Justice',
            'A11' => '3. Volunteerism',
            'A12' => '4. Gender and Development',
            'A13' => '5. Others',
            'A15' => 'T O T A L',

            'B7' => 'NUMBER OF',
            'B8' => 'Materials/ Session Plans Developed',
            'C8' => 'Materials Reproduced/Distributed (If applicable)',
        ];

        $mergesCoordinates = [
            'A1:C1',
            'A2:C2',
            'A3:C3',
            'A4:C4',
            'A5:C5',
            'A7:A8',
            'B7:C7',
        ];

        $boldCoordinates = [
            'A1:C8',
        ];

        $verticalAlignedCoordinates = [
            'A1:C1' => 'center',
            'A2:C2' => 'center',
            'A3:C3' => 'center',
            'A7:A8' => 'center',
            'B7:C7' => 'center',
        ];

        $horizontalAlignedCoordinates = [
            'A1:C1' => 'center',
            'A2:C2' => 'center',
            'A3:C3' => 'center',
            'A7:A8' => 'center',
            'B7:C7' => 'center',
        ];

        $adjustedColumnWidthCoordinates = [
            'A' => 35, 'G' => 15
        ];

        $wrappedTextCoordinates = [
            'A1:C10'
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

        foreach ($wrappedTextCoordinates as $coordinate => $width) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getAlignment()->setWrapText(true); 
        }

        return $spreadsheet;
    }

    private function getData(array $data): array
    {
        $this->fieldOffice = $this->fieldOfficesRepository->find($data['field_office_id']);
        $this->quarters = $this->quartersRepository->find($data['quarter_id']);

        $quarterData = $this->quartersRepository->find($data['quarter_id']);
        $minMaxDate = $this->quartersRepository->getQuarterMinMaxDate($quarterData);
        // has side effect of populating $this->sessionIds
        $treatmentCategoryTotal = $this->getTreatmentCategoriesData($minMaxDate['min'], $minMaxDate['max'], (int) $data['field_office_id']);
        $clientSessionsData = $this->getClientSessionsData($minMaxDate, (int) $data['field_office_id']);

        return [
            'treatment_categories' => $treatmentCategoryTotal,
            'client_frequency_active_supervision' => $clientSessionsData['client_frequency']['active_supervision'],
            'client_frequency_others' => $clientSessionsData['client_frequency']['others'],
            'fsg_frequency' => $clientSessionsData['fsg_frequency'],
        ];
    }

    private function getTreatmentCategoriesData(string $minDate, string $maxDate, int $fieldOfficeId): array
    {
        $treatmentCategoryTotal = ['MTCS' => ['Total' => 0], 'RA' => ['Total' => 0]];
        $sessions = $this->sessionsRepository->getTableIA1SummaryFormTreatmentCategoryData($minDate, $maxDate, $fieldOfficeId);

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

    private function getClientSessionsData(array $minMaxDate, int $fieldOfficeId): array
    {
        $fsgClients = [];
        $clientsId = ['active_supervision' => [], 'others' => []];
        $clientSessions = $this->clientSessionsRepository->findBySessionIds($this->sessionIds);
        $clientsIdUnderSupervision = $this->clientsRepository->findClientsIdUnderSupervisionPeriod($minMaxDate, $fieldOfficeId);

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
}