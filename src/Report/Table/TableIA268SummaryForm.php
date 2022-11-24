<?php

namespace App\Report\Table;

use App\Entity\FieldOffices;
use App\Entity\Quarters;
use App\Enum\SystemSettingNames;
use App\Repository\ClientSessionsRepository;
use App\Repository\FieldOfficesRepository;
use App\Repository\QuartersRepository;
use App\Repository\SessionsRepository;
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
        $this->data['results'] = $this->getData($data);
        $this->data[SystemSettingNames::GENERATED_REPORTS_CODE] = $data[SystemSettingNames::GENERATED_REPORTS_CODE];

        $spreadsheet = $this->footer();
        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");

        $filePath = $_ENV['XLSX_PATH_FILE'] . self::TABLE_NAME . "-" . time() . ".xlsx";
        $writer->save($filePath);

        return new BinaryFileResponse($filePath);
    }

    public function header(): Spreadsheet
    {
        $spreadsheet = $this->prepare();

        $spreadsheet->getActiveSheet()->getStyle('A7:O19')
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $spreadsheet;
    }

    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();
        $cellsY = [
            'gender' => ['F' => 'C', 'M' => 'D'],
            'is_pwd' => 'E',
            'is_senior_citizen' => 'F',
            'offense_category' => ['DO' => 'G', 'NDO' => 'H'],
            'Prep' => 'I',
            'I' => 'J',
            'II' => 'K',
            'III' => 'L',
            'IV-Ongoing' => 'M',
            'IV-Completed' => 'N',
        ];
        $cellsX = [
            'PS' => 11, 'PR' => 12, 'PD' => 13, 'JICL' => 14, 'FTMDO' => 15, 'Pet' => 17, 'Term' => 18,
        ];
        $verticalTotalExemptions = ['Prep', 'I', 'II', 'III', 'IV-Ongoing', 'IV-Completed'];
        $totalColumnCoordinates = [
            'F' => 'C', 'M' => 'D', 'is_pwd' => 'E', 'is_senior_citizen' => 'F', 'DO' => 'G', 'NDO' => 'H',
        ];
        $total = [
            'F' => 0, 'M' => 0, 'is_pwd' => 0, 'is_senior_citizen' => 0, 'DO' => 0, 'NDO' => 0,
        ];
        $totalPhasesPerClient = [
            'PS' => 0, 'PR' => 0, 'PD' => 0, 'JICL' => 0, 'FTMDO' => 0, 'Pet' => 0, 'Term' => 0,
        ];
        $totalPhasesPerClientCoordinates = [
            'PS' => 'O' . 11, 'PR' => 'O' . 12, 'PD' => 'O' . 13, 'JICL' => 'O' . 14, 'FTMDO' => 'O' . 15,
            'Pet' => 'O' . 17, 'Term' => 'O' . 18,
        ];

        $totalPetTerm = $total;

        foreach ($this->data['results'] as $clientType => $client) {
            $cellX = $cellsX[$clientType];
            $to = ('Pet' == $clientType || 'Term' == $clientType) ? $totalPetTerm : $total;

            foreach ($verticalTotalExemptions as $exemption) {
                $totalPhasesPerClient[$clientType] += $client[$exemption];
            }

            foreach ($client as $columns => $data) {
                if (gettype($data) === 'array') {
                    foreach ($data as $subtype => $score) {
                        $cellY = $cellsY[$columns][$subtype];
                        $spreadsheet->getActiveSheet()->setCellValue($cellY . $cellX, $score);
                        $to[$subtype] += $score;
                    }

                    continue;
                }

                $cellY = $cellsY[$columns];

                if (! in_array($columns, $verticalTotalExemptions)) {
                    $to[$columns] += $data;
                }

                $spreadsheet->getActiveSheet()->setCellValue($cellY . $cellX, $data);
            }

            if ('Pet' == $clientType || 'Term' == $clientType) {
                $totalPetTerm = $to;
            } else {
                $total = $to;
            }
        }

        foreach ($total as $type => $item) {
            $spreadsheet->getActiveSheet()->setCellValue($totalColumnCoordinates[$type] . '16', $item);
        }

        foreach ($totalPetTerm as $type => $item) {
            $spreadsheet->getActiveSheet()->setCellValue($totalColumnCoordinates[$type] . '19', $item);
        }

        foreach ($totalPhasesPerClient as $type => $score) {
            $spreadsheet->getActiveSheet()->setCellValue($totalPhasesPerClientCoordinates[$type], $score);
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
            'G1' => 'Field Office IQPR FORM' . $this->data[SystemSettingNames::GENERATED_REPORTS_CODE],
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
            'A1:O1','A2:O2','A3:O3','A7:H7','I7:O7','A8:A10','B8:B10','C9:C10','D9:D10','E8:E10','F8:F10','G8:H8',
            'G9:H9','G10:H10','I8:I10','J8:J10','K8:K10','L8:L10','M8:N8','O8:O10'
        ];

        $boldCoordinates = [
            'A7:O10', 'A16', 'B17', 'A19'
        ];

        $verticalAlignedCoordinates = [
            'A1:O1' => 'center', 'A2:O2' => 'center', 'A3:O3' => 'center', 'A7:O10' => 'center'
        ];

        $horizontalAlignedCoordinates = [
            'A1:O1' => 'center', 'A2:O2' => 'center', 'A3:O3' => 'center', 'A7:O10' => 'center'
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
        $fieldOfficeId = (int) $data['field_office_id'];
        $quarterId = (int) $data['quarter_id'];

        $this->fieldOffice = $this->fieldOfficesRepository->find($fieldOfficeId);
        $this->quarters = $this->quartersRepository->find($quarterId);

        $minMaxDate = $this->quartersRepository->getQuarterMinMaxDate($this->quarters);
        $sessions = $this->sessionsRepository->getTableIA1SummaryFormTreatmentCategoryData(
            $minMaxDate['min'],
            $minMaxDate['max'],
            $fieldOfficeId,
        );
        $sessionsPhase = [];

        foreach ($sessions as $session) {
            $sessionId = intval($session['session_id']);

            $this->sessionIds[] = $sessionId;

            if (! isset($sessionsPhase[$sessionId])) {
                $sessionsPhase[$sessionId][] = $session['phase'];

                continue;
            }

            $sessionsPhase[$sessionId][] = $session['phase'];
        }

        $dataInitialValues = [
            'gender' => ['F' => 0, 'M' => 0,],
            'is_pwd' => 0,
            'is_senior_citizen' => 0,
            'offense_category' => ['DO' => 0, 'NDO' => 0,],
            'Prep' => 0,
            'I' => 0,
            'II' => 0,
            'III' => 0,
            'IV-Ongoing' => 0,
            'IV-Completed' => 0,
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
            $sessionId = (int) $client['session_id'];

            if (intval($client['is_pwd'])) {
                $data[$client['client_type_code']]['is_pwd']++;
            }
            if (intval($client['is_senior_citizen'])) {
                $data[$client['client_type_code']]['is_senior_citizen']++;
            }
            $data[$client['client_type_code']]['offense_category'][$client['offense_category']]++;
            $data[$client['client_type_code']]['gender'][$client['gender']]++;

            foreach ($sessionsPhase[$sessionId] as $phase) {
                $data[$client['client_type_code']][$phase]++;
            }
        }

        return $data;
    }
}