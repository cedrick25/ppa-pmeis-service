<?php

namespace App\Report\Table;

use App\Entity\FieldOffices;
use App\Entity\Quarters;
use App\Enum\SystemSettingNames;
use App\Repository\FieldOfficesRepository;
use App\Repository\QuartersRepository;
use App\Service\RestorativeJustice\ConductProcesses;
use App\Service\RestorativeJustice\RelatedActivities;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TableIAB1SummaryForm implements Form
{
    private const TABLE_NAME = "TableIAB1SummaryForm";
    private const PRE_ENCOUNTER_ACT = 'Pre-Encounter Activities';
    private const COMMUNITY_WORK_SERVICES = 'Community Work Services';
    private const RESTORED_RELATIONSHIPS = 'Restored Relationships';
    private const ACTIVE_SUPERVISION = 'Active Supervision';


    public function __construct(
        private RelatedActivities      $service,
        private ConductProcesses       $conductProcessesService,
        private FieldOfficesRepository  $fieldOfficesRepository,
        private QuartersRepository     $quartersRepository,
        private array                  $data = [],
        private ?FieldOffices           $fieldOffice = null,
        private ?Quarters              $quarters = null,
    ) {}

    public function supports(string $tableName): bool
    {
        return self::TABLE_NAME === $tableName;
    }

    public function generate(array $data): BinaryFileResponse
    {
        $quarterId = intval($data['quarter_id']);
        $fieldOfficeId = intval($data['field_office_id']);

        $this->fieldOffice = $this->fieldOfficesRepository->find($fieldOfficeId);
        $this->quarters = $this->quartersRepository->find($quarterId);
        $this->data['activities'] = $this->getActivities($quarterId, $fieldOfficeId);
        $rjb1 = $this->conductProcessesService->getRJIB1($quarterId, $fieldOfficeId);
        $this->data['process_conducted'] = $rjb1['data'] ?? [];
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
        $spreadsheet->getActiveSheet()->getStyle('A7:Y10')->getBorders()
            ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('A13:Y16')->getBorders()
            ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('A18:D24')->getBorders()
            ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('A35:C42')->getBorders()
            ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $spreadsheet;
    }

    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();
        $coordinateX = ['ACTIVE_SUPERVISION' => 10, 'PETITIONER' => 16];

        $activitiesCoordinateY = [
            self::PRE_ENCOUNTER_ACT => ['Conducted' => 'A', 'F' => 'B', 'M' => 'C', 'PWD' => 'D', 'SC' => 'E'],
            'Mediation' => ['Conducted' => 'F', 'F' => 'G', 'M' => 'H', 'PWD' => 'I', 'SC' => 'J'],
            'Conferencing' => ['Conducted' => 'K', 'F' => 'L', 'M' => 'M', 'PWD' => 'N', 'SC' => 'O'],
            'COS' => ['Conducted' => 'P', 'F' => 'Q', 'M' => 'R', 'PWD' => 'S', 'SC' => 'T'],
            'Others' => ['Conducted' => 'U', 'F' => 'V', 'M' => 'W', 'PWD' => 'X', 'SC' => 'Y']
        ];

        $rjProcess = [
            self::PRE_ENCOUNTER_ACT => ['ACTIVE_SUPERVISION' => 0, 'PETITIONER' => 0],
            'Mediation' => ['ACTIVE_SUPERVISION' => 0, 'PETITIONER' => 0],
            'Conferencing' => ['ACTIVE_SUPERVISION' => 0, 'PETITIONER' => 0],
            'COS' => ['ACTIVE_SUPERVISION' => 0, 'PETITIONER' => 0],
            'Others' => ['ACTIVE_SUPERVISION' => 0, 'PETITIONER' => 0]
        ];

        foreach ($this->data['activities'] as $type=>$activity) {
            foreach ($activity as $process=>$item) {
                foreach ($item as $label=>$value) {
                    $coordinate = $activitiesCoordinateY[$process][$label] . $coordinateX[$type];
                    $spreadsheet->getActiveSheet()->setCellValue($coordinate, $value);
                }
            }

            if (! empty(key($activity))) {
                $rjProcess[key($activity)][$type]++;
            }
        }

        $positiveParticulars = ['Resolved', 'Completed', 'Agreement', 'Reached'];
        $particulars = [
            'positive' => ['ACTIVE_SUPERVISION' => 0, 'PETITIONER' => 0],
            'negative' => ['ACTIVE_SUPERVISION' => 0, 'PETITIONER' => 0]
        ];

        $outcomes = [
            'Restitution' => ['ACTIVE_SUPERVISION' => 0, 'PETITIONER' => 0],
            self::COMMUNITY_WORK_SERVICES => ['ACTIVE_SUPERVISION' => 0, 'PETITIONER' => 0],
            self::RESTORED_RELATIONSHIPS => ['ACTIVE_SUPERVISION' => 0, 'PETITIONER' => 0],
            'Others' => ['ACTIVE_SUPERVISION' => 0, 'PETITIONER' => 0]

        ];

        foreach ($this->data['process_conducted'] as $processConducted) {
            $rjGroup = $processConducted['rj_group'];

            if (in_array($processConducted['rjp_status'], $positiveParticulars)) {
                $particulars['positive'][$rjGroup]++;
            } else {
                $particulars['negative'][$rjGroup]++;
            }

            $outcomes[$processConducted['rj_outcome_name']][$rjGroup]++;
            $rjProcess[$processConducted['rjp_type']][$processConducted['rj_group']]++;
        }

        $spreadsheet = $this->plotParticulars($particulars, $spreadsheet);
        $spreadsheet = $this->plotOutcomes($outcomes, $spreadsheet);

        return $this->plotProcess($rjProcess, $spreadsheet);
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
            'A4' => 'I.B.1   RESTORATIVE JUSTICE',
            'A5' => "Table I.B.1   Number of RJ Processes Conducted/ Clients' Involvement",
            'A6' => "TOTAL NUMBER (". self::ACTIVE_SUPERVISION .")",
            'A7' => self::PRE_ENCOUNTER_ACT, 'F7' => 'Mediation', 'K7' => 'Conferencing', 'P7' => 'COS',
            'U7' => 'Others',
            'A8' => '# of Acts', 'B8' => 'SEX', 'D8' => 'PWD', 'E8' => 'SC',
            'F8' => 'Sessions', 'G8' => 'Sex', 'I8' => 'PWD', 'J8' => 'SC',
            'K8' => 'Sessions', 'L8' => 'Sex', 'N8' => 'PWD', 'O8' => 'SC',
            'P8' => 'Sessions', 'Q8' => 'Sex', 'S8' => 'PWD', 'T8' => 'SC',
            'U8' => 'Sessions', 'V8' => 'Sex', 'X8' => 'PWD', 'Y8' => 'SC',
            'A9' => 'Conducted', 'B9' => 'F', 'C9' => 'M',
            'F9' => 'Conducted', 'G9' => 'F', 'H9' => 'M',
            'K9' => 'Conducted', 'L9' => 'F', 'M9' => 'M',
            'P9' => 'Conducted', 'Q9' => 'F', 'R9' => 'M',
            'U9' => 'Conducted', 'V9' => 'F', 'W9' => 'M',
            'A12' => "TOTAL NUMBER (PETITIONERS)",
            'A13' => self::PRE_ENCOUNTER_ACT, 'F13' => 'Mediation', 'K13' => 'Conferencing', 'P13' => 'COS',
            'U13' => 'Others',
            'A14' => '# of Acts', 'B14' => 'SEX', 'D14' => 'PWD', 'E14' => 'SC',
            'F14' => 'Sessions', 'G14' => 'Sex', 'I14' => 'PWD', 'J14' => 'SC',
            'K14' => 'Sessions', 'L14' => 'Sex', 'N14' => 'PWD', 'O14' => 'SC',
            'P14' => 'Sessions', 'Q14' => 'Sex', 'S14' => 'PWD', 'T14' => 'SC',
            'U14' => 'Sessions', 'V14' => 'Sex', 'X14' => 'PWD', 'Y14' => 'SC',
            'A15' => 'Conducted', 'B15' => 'F', 'C15' => 'M',
            'F15' => 'Conducted', 'G15' => 'F', 'H15' => 'M',
            'K15' => 'Conducted', 'L15' => 'F', 'M15' => 'M',
            'P15' => 'Conducted', 'Q15' => 'F', 'R15' => 'M',
            'U15' => 'Conducted', 'V15' => 'F', 'W15' => 'M',
            'A18' => 'Table I.B.1(Columns 1 & 10)',
            'A19' => 'PARTICULARS (RJ STATUS)', 'B19' => 'Number', 'D19' => 'Total', 'B20' => self::ACTIVE_SUPERVISION,
            'C20' => 'Petitioner',
            'A21' => 'Resolved', 'A22' => '(Completed, Agreement reached)', 'A23' => 'Unresolved',
            'A24' => '(Shelved/Deffered, On-going)',
            'A26' => 'Table I.B.1 (Columns 1 & 11)',
            'A27' => 'PARTICULARS (RJ OUTCOME)',
            'B27' => 'Number',
            'D27' => 'Total', 'B28' => self::ACTIVE_SUPERVISION, 'C28' => 'Petitioner', 'A29' => 'Restitution',
            'A30' => self::COMMUNITY_WORK_SERVICES, 'A31' => 'Restoration of Relationship', 'A32' => 'Others',
            'A34' => 'Table I.B.1 (Columns 6 & 7)',
            'A35' => 'RJ PROCESS',
            'B35' => 'TOTAL NUMBER OF',
            'B36' => self::ACTIVE_SUPERVISION,
            'C36' => 'Petitioners',
            'A37' => self::PRE_ENCOUNTER_ACT,
            'A38' => 'Mediation',
            'A39' => 'Conferencing',
            'A40' => 'Circle of Support',
            'A41' => 'Others (Indigenous Practices, etc.)',
            'A42' => 'TOTAL',
        ];

        $mergesCoordinates = [
            'A7:E7', 'F7:O7', 'P7:T7', 'U7:Y7', 'A13:E13', 'F13:O13', 'P13:T13', 'U13:Y13',
            'A19:A20', 'B18:C18', 'B21:B22', 'C21:C22', 'B23:B24', 'C23:C24', 'D19:D20', 'D21:D22', 'D23:D24',
            'A27:A28', 'B27:C27', 'D27:D28', 'A35:A36', 'B35:C35'
        ];

        $boldCoordinates = [
            'A1:Y7', 'A12:Y13',
        ];

        $verticalAlignedCoordinates = [
            'A1:Y15' => 'center', 'A18:D24' => 'center', 'A35:C42' => 'center'
        ];

        $horizontalAlignedCoordinates = [
            'A1:Y15' => 'center', 'A18:D24' => 'center', 'A35:C42' => 'center'
        ];

        $adjustedColumnWidthCoordinates = [
            'A' => 25, 'F' => 25, 'K' => 25, 'P' => 25,  'U' => 25,
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

    private function getActivities(int $quarterId, int $fieldOfficeId): array
    {
        $response = ['ACTIVE_SUPERVISION' => [], 'PETITIONER' => []];
        $RJIB2Data = $this->service->getRJIB2Data($quarterId, $fieldOfficeId);

        if (! isset($RJIB2Data['data'])) {
            return $response;
        }

        foreach ($RJIB2Data['data'] as $item) {
            $rjGroup = $item['rj_group'];
            $rjProcess = $item['rj_process'];
            $gender = $item['gender'];

            if (!isset($response[$rjGroup][$rjProcess])) {
                $response[$rjGroup][$rjProcess] = [];
            }

            if (!isset($response[$rjGroup][$rjProcess]['Conducted'])) {
                $response[$rjGroup][$rjProcess]['Conducted'] = 0;
            }
            $response[$rjGroup][$rjProcess]['Conducted']++;

            if (!isset($response[$rjGroup][$rjProcess][$gender])) {
                $response[$rjGroup][$rjProcess][$gender] = 0;
            }
            $response[$rjGroup][$rjProcess][$gender]++;

            if (intval($item['is_pwd'])) {
                if (!isset($response[$rjGroup][$rjProcess]['isPwd'])) {
                    $response[$rjGroup][$rjProcess]['isPwd'] = 0;
                }
                $response[$rjGroup][$rjProcess]['isPwd']++;
            }

            if (intval($item['is_senior_citizen'])) {
                if (!isset($response[$rjGroup][$rjProcess]['isSC'])) {
                    $response[$rjGroup][$rjProcess]['isSC'] = 0;
                }
                $response[$rjGroup][$rjProcess]['isSC']++;
            }
        }

        return $response;
    }

    /**
     * @param array<string, array<string, int>> $particulars
     */
    private function plotParticulars(array $particulars, Spreadsheet $spreadsheet): Spreadsheet {
        $positiveActive = $particulars['positive']['ACTIVE_SUPERVISION'];
        $positivePetitioner = $particulars['positive']['PETITIONER'];
        $negativeActive = $particulars['negative']['ACTIVE_SUPERVISION'];
        $negativePetitioner = $particulars['negative']['PETITIONER'];


        $spreadsheet->getActiveSheet()->setCellValue('B21', $positiveActive);
        $spreadsheet->getActiveSheet()->setCellValue('C21', $positivePetitioner);
        $spreadsheet->getActiveSheet()->setCellValue('D21', intval($positiveActive) + intval($positivePetitioner));
        $spreadsheet->getActiveSheet()->setCellValue('B23', $negativeActive);
        $spreadsheet->getActiveSheet()->setCellValue('C23', $negativePetitioner);
        $spreadsheet->getActiveSheet()->setCellValue('D23', intval($negativeActive) + intval($negativePetitioner));

        return $spreadsheet;
    }

    /**
     * @param array<string, array<string, int>> $outcomes
     */
    private function plotOutcomes(array $outcomes, Spreadsheet $spreadsheet): Spreadsheet
    {
        $restitutionActive = $outcomes['Restitution']['ACTIVE_SUPERVISION'];
        $restitutionPetitioner = $outcomes['Restitution']['PETITIONER'];
        $communityWorkServicesActive = $outcomes[self::COMMUNITY_WORK_SERVICES]['ACTIVE_SUPERVISION'];
        $communityWorkServicesPetitioner = $outcomes[self::COMMUNITY_WORK_SERVICES]['PETITIONER'];
        $restoredRelationshipsActive = $outcomes[self::RESTORED_RELATIONSHIPS]['ACTIVE_SUPERVISION'];
        $restoredRelationshipsPetitioner = $outcomes[self::RESTORED_RELATIONSHIPS]['PETITIONER'];
        $othersActive = $outcomes['Others']['ACTIVE_SUPERVISION'];
        $othersPetitioner = $outcomes['Others']['PETITIONER'];

        $spreadsheet->getActiveSheet()->setCellValue('B29', $restitutionActive);
        $spreadsheet->getActiveSheet()->setCellValue('C29', $restitutionPetitioner);
        $spreadsheet->getActiveSheet()->setCellValue('D29', intval($restitutionActive) + intval($restitutionPetitioner));
        $spreadsheet->getActiveSheet()->setCellValue('B30', $communityWorkServicesActive);
        $spreadsheet->getActiveSheet()->setCellValue('C30', $communityWorkServicesPetitioner);
        $spreadsheet->getActiveSheet()->setCellValue('D30', intval($communityWorkServicesActive) + intval($communityWorkServicesPetitioner));
        $spreadsheet->getActiveSheet()->setCellValue('B31', $restoredRelationshipsActive);
        $spreadsheet->getActiveSheet()->setCellValue('C31', $restoredRelationshipsPetitioner);
        $spreadsheet->getActiveSheet()->setCellValue('D31', intval($restoredRelationshipsActive) + intval($restoredRelationshipsPetitioner));
        $spreadsheet->getActiveSheet()->setCellValue('B32', $othersActive);
        $spreadsheet->getActiveSheet()->setCellValue('C32', $othersPetitioner);
        $spreadsheet->getActiveSheet()->setCellValue('D32', intval($othersActive) + intval($othersPetitioner));

        return $spreadsheet;
    }

    /**
     * @param array<string, array<string, int>> $process
     */
    private function plotProcess(array $process, Spreadsheet $spreadsheet): Spreadsheet
    {
        $preEncounterActActive = $process[self::PRE_ENCOUNTER_ACT]['ACTIVE_SUPERVISION'];
        $preEncounterActPetitioner = $process[self::PRE_ENCOUNTER_ACT]['PETITIONER'];
        $mediationActive = $process['Mediation']['ACTIVE_SUPERVISION'];
        $mediationPetitioner = $process['Mediation']['PETITIONER'];
        $conferencingActive = $process['Conferencing']['ACTIVE_SUPERVISION'];
        $conferencingPetitioner = $process['Conferencing']['PETITIONER'];
        $cosActive = $process['COS']['ACTIVE_SUPERVISION'];
        $cosPetitioner = $process['COS']['PETITIONER'];
        $othersActive = $process['Others']['ACTIVE_SUPERVISION'];
        $othersPetitioner = $process['Others']['PETITIONER'];

        $totalActive = array_sum([
            intval($preEncounterActActive),
            intval($mediationActive),
            intval($conferencingActive),
            intval($cosActive),
            intval($othersActive)
        ]);
        $totalPetitioner = array_sum([
            intval($preEncounterActPetitioner),
            intval($mediationPetitioner),
            intval($conferencingPetitioner),
            intval($cosPetitioner),
            intval($othersPetitioner),
        ]);

        $spreadsheet->getActiveSheet()->setCellValue('B37', $preEncounterActActive);
        $spreadsheet->getActiveSheet()->setCellValue('C37', $preEncounterActPetitioner);
        $spreadsheet->getActiveSheet()->setCellValue('B38', $mediationActive);
        $spreadsheet->getActiveSheet()->setCellValue('C39', $mediationPetitioner);
        $spreadsheet->getActiveSheet()->setCellValue('B39', $conferencingActive);
        $spreadsheet->getActiveSheet()->setCellValue('C39', $conferencingPetitioner);
        $spreadsheet->getActiveSheet()->setCellValue('B40', $cosActive);
        $spreadsheet->getActiveSheet()->setCellValue('C40', $cosPetitioner);
        $spreadsheet->getActiveSheet()->setCellValue('B41', $othersActive);
        $spreadsheet->getActiveSheet()->setCellValue('C41', $othersPetitioner);
        $spreadsheet->getActiveSheet()->setCellValue('B42', $totalActive);
        $spreadsheet->getActiveSheet()->setCellValue('C42', $totalPetitioner);

        return $spreadsheet;
    }
}