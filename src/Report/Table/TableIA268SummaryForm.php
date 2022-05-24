<?php

namespace App\Report\Table;

use App\Entity\FieldOffices;
use App\Entity\Quarters;
use App\Repository\ClientSessionsRepository;
use App\Repository\ClientsRepository;
use App\Repository\FieldOfficesRepository;
use App\Repository\QuartersRepository;
use App\Repository\SessionsRepository;
use App\Service\TherapeuticCommunity\Sessions;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TableIA268SummaryForm implements Form
{
    private const TABLE_NAME = "TableIA268SummaryForm";


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
            "A7:O19"
        ];

        foreach ($thinBorders as $coordinate) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }
        return $spreadsheet;
    }

    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();
        $cells = [
            'PS' => [
                'gender' => ['F' => 'C11', 'M' => 'D11'],
                'is_pwd' => 'E11',
                'is_senior_citizen' => 'F11',
                'offense_category' => ['DO' => 'G11', 'NDO' => 'H11']
            ],
            'PR' => [
                'gender' => ['F' => 'C12', 'M' => 'D12'],
                'is_pwd' => 'E12',
                'is_senior_citizen' => 'F12',
                'offense_category' => ['DO' => 'G12', 'NDO' => 'H12']
            ],
            'PD' => [
                'gender' => ['F' => 'C13', 'M' => 'D13'],
                'is_pwd' => 'E13',
                'is_senior_citizen' => 'F13',
                'offense_category' => ['DO' => 'G13', 'NDO' => 'H13']
            ],
            'JICL' => [
                'gender' => ['F' => 'C14', 'M' => 'D14'],
                'is_pwd' => 'E14',
                'is_senior_citizen' => 'F14',
                'offense_category' => ['DO' => 'G14', 'NDO' => 'H14']
            ],
            'FTMDO' => [
                'gender' => ['F' => 'C15', 'M' => 'D15'],
                'is_pwd' => 'E15',
                'is_senior_citizen' => 'F15',
                'offense_category' => ['DO' => 'G15', 'NDO' => 'H15']
            ],
            'Pet' => [
                'gender' => ['F' => 'C17', 'M' => 'D17'],
                'is_pwd' => 'E17',
                'is_senior_citizen' => 'F17',
                'offense_category' => ['DO' => 'G17', 'NDO' => 'H17']
            ],
            'Term' => [
                'gender' => ['F' => 'C18', 'M' => 'D18'],
                'is_pwd' => 'E18',
                'is_senior_citizen' => 'F18',
                'offense_category' => ['DO' => 'G18', 'NDO' => 'H18']
            ],
        ];

        foreach ($this->data as $clientType=>$client) {
            foreach ($client as $columns=>$data) {
                if (gettype($data) === 'array') {
                    foreach ($data as $subtype => $score) {
                        $spreadsheet->getActiveSheet()->setCellValue($cells[$clientType][$columns][$subtype], $score);
                    }

                    continue;
                }
                $spreadsheet->getActiveSheet()->setCellValue($cells[$clientType][$columns], $data);
            }
        }

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
            'A4' => 'I.    PROGRAM IMPLEMENTATION',
            'A5' => 'A.1   Therapeutic Community Ladderized Program (TCLP)',
            'A6' => "Table I.A.2 1 6 and Table I.A.8  Number of Clients' Involvement by Phase",
            'I7' => 'PHASES',
            'A8' => 'Reference Table',
            'B8' => 'Classification',
            'C8' => 'Sex',
            'E8' => 'PWD',
            'F8' => 'SC',
            'G8' => 'OFFENSE',
            'I8' => 'Prep.',
            'J8' => 'I',
            'K8' => 'II',
            'L8' => 'III',
            'M8' => 'IV',
            'O8' => 'TOTAL',
            'C9' => 'F',
            'D9' => 'M',
            'G9' => 'CATEGORY',
            'M9' => 'On-',
            'N9' => 'Com-',
            'G10' => 'DO',
            'H10' => 'NDO',
            'M10' => 'going',
            'N10' => 'pleted',
            'A11' => 'Table I.A.2',
            'B11' => 'PS',
            'A12' => 'Table I.A.3',
            'B12' => 'PR',
            'A13' => 'Table I.A.4',
            'B13' => 'PD',
            'A14' => 'Table I.A.5',
            'B14' => 'JICL',
            'A15' => 'Table I.A.6',
            'B15' => 'FTMDO/ SS',
            'A16' => 'TOTAL',
            'A17' => 'Table I.A.8',
            'B17' => 'PETITIONERS',
            'B18' => 'TERMINATED',
            'A19' => 'TOTAL',
        ];

        $mergesCoordinates = [
            'A7:H7','I7:O7','A8:A10','B8:B10','C9:C10','D9:D10','E8:E10','F8:F10','G8:H8','G9:H9','G10:H10','I8:I10','J8:J10','K8:K10','L8:L10','M8:N8','O8:O10'
        ];

        $boldCoordinates = [
            'A7:O10', 'A16', 'B17', 'A19'
        ];

        $verticalAlignedCoordinates = [
            'A7:O10' => 'center'
        ];

        $horizontalAlignedCoordinates = [
            'A7:O10' => 'center'
        ];

        $adjustedColumnWidthCoordinates = [
            'A' => 30, 'B' => 30, 'O' => 15
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
        $this->fieldOffice = $this->fieldOfficesRepository->find($data['field_office_id']);
        $this->quarters = $this->quartersRepository->find($data['quarter_id']);

        $quarterData = $this->quartersRepository->find($data['quarter_id']);
        $minMaxDate = $this->quartersRepository->getQuarterMinMaxDate($quarterData);
        $sessions = $this->sessionsRepository->getTableIA1SummaryFormTreatmentCategoryData($minMaxDate['min'], $minMaxDate['max'], (int) $data['field_office_id']);

        foreach ($sessions as $session) {
            $this->sessionIds[] = intval($session['session_id']);
        }

        $dataInitialValues = [
            'gender' => [
                'F' => 0,
                'M' => 0,
            ],
            'is_pwd' => 0,
            'is_senior_citizen' => 0,
            'offense_category' => [
                'DO' => 0,
                'NDO' => 0,
            ],
        ];
        $data = [
            'PS' => $dataInitialValues,
            'PR' => $dataInitialValues,
            'PD' => $dataInitialValues,
            'JICL' => $dataInitialValues,
            'FTMDO' => $dataInitialValues,
            'Pet' => $dataInitialValues,
            'Term' => $dataInitialValues,
        ];
        $clients = $this->clientSessionsRepository->findClientsBySessionIds($this->sessionIds);
        foreach ($clients as $client) {
            if (intval($client['is_pwd'])) {
                $data[$client['client_type_code']]['is_pwd']++;
            }
            if (intval($client['is_senior_citizen'])) {
                $data[$client['client_type_code']]['is_senior_citizen']++;
            }
            $data[$client['client_type_code']]['offense_category'][$client['offense_category']]++;
            $data[$client['client_type_code']]['gender'][$client['gender']]++;
        }

        return $data;
    }
}