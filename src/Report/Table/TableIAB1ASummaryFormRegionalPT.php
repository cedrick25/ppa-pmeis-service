<?php

namespace App\Report\Table;

use App\Entity\Quarters;
use App\Entity\Regions;
use App\Repository\FieldOfficesRepository;
use App\Repository\QuartersRepository;
use App\Repository\RegionsRepository;
use App\Service\RestorativeJustice\ConductProcesses;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TableIAB1ASummaryFormRegionalPT implements Form
{
    private const TABLE_NAME = "TableIAB1ASummaryFormRegionalPT";
    private const PRE_ENCOUNTER_ACT = 'Pre-Encounter Activities';


    public function __construct(
        private ConductProcesses       $conductProcessesService,
        private FieldOfficesRepository  $fieldOfficesRepository,
        private QuartersRepository     $quartersRepository,
        private RegionsRepository      $regionsRepository,
        private array                  $data = [],
        private ?Regions               $region = null,
        private ?Quarters              $quarter = null,
        private int                    $lastFilledOutCellY = 10,
    ) {}

    public function supports(string $tableName): bool
    {
        return self::TABLE_NAME === $tableName;
    }

    public function generate(array $data): BinaryFileResponse
    {
        $quarterId = intval($data['quarter_id']);
        $regionId = intval($data['region_id']);

        $this->region = $this->regionsRepository->find($regionId);
        $this->quarter = $this->quartersRepository->find($quarterId);

        $fieldOffices = $this->fieldOfficesRepository->findBy(['regionId' => $regionId]);

        foreach ($fieldOffices as $fieldOffice) {
            // TODO: identify the source of sessions_conducted - RJ is not linked to any session
            $initialValues = [
                'sessions_conducted' => 0,
                'total_clients' => 0,
                'gender' => ['M' => 0, 'F' => 0],
                'is_pwd' => 0,
                'is_sc' => 0
            ];
            $build = [
                self::PRE_ENCOUNTER_ACT => [
                    'acts_conducted' => 0,
                    'total_clients' => 0,
                    'gender' => ['M' => 0, 'F' => 0],
                    'is_pwd' => 0,
                    'is_sc' => 0
                ],
                'Mediation' => $initialValues,
                'Conferencing' => $initialValues,
                'Circle of Support' => $initialValues,
                'Others' => $initialValues,
            ];

            $conductedProcess = $this->conductProcessesService->getRJIB1($quarterId, $fieldOffice->getFieldOfficeId());

            if (isset($conductedProcess['data'])) {
                foreach ($conductedProcess['data'] as $conductedProcessData) {
                    if ('PETITIONER' === $conductedProcessData['rj_group']) {
                        continue;
                    }

                    $rjpType = $conductedProcessData['rjp_type'];
                    $gender = $conductedProcessData['gender'];
                    $build[$rjpType]['total_clients']++;
                    $build[self::PRE_ENCOUNTER_ACT]['acts_conducted']++;
                    $build[self::PRE_ENCOUNTER_ACT]['total_clients']++;
                    $build[$rjpType]['gender'][$gender]++;
                    $build[self::PRE_ENCOUNTER_ACT]['gender'][$gender]++;

                    if (intval($conductedProcessData['is_pwd'])) {
                        $build[$rjpType]['is_pwd']++;
                        $build[self::PRE_ENCOUNTER_ACT]['is_pwd']++;
                    }

                    if (intval($conductedProcessData['is_senior_citizen'])) {
                        $build[$rjpType]['is_sc']++;
                        $build[self::PRE_ENCOUNTER_ACT]['is_sc']++;
                    }
                }
            }

            $this->data[$fieldOffice->getName()] = $build;
        }

        $spreadsheet = $this->footer();
        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");

        $filePath = $_ENV['XLSX_PATH_FILE'] . self::TABLE_NAME . "-" . time() . ".xlsx";
        $writer->save($filePath);

        return new BinaryFileResponse($filePath);
    }

    public function header(): Spreadsheet
    {
        $spreadsheet = $this->prepare();
        $spreadsheet->getActiveSheet()->getStyle('A7:AE10')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $spreadsheet;
    }

    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();
        $coordinates = [
            self::PRE_ENCOUNTER_ACT => [
                "acts_conducted" => 'B',
                "total_clients" => 'C',
                "gender" => ["M" => 'E', "F" => 'D'],
                "is_pwd" => 'F',
                "is_sc" => 'G',
            ],
            "Mediation" => [
                "sessions_conducted" => 'H',
                "total_clients" => 'I',
                "gender" => ["M" => 'K', "F" => 'J'],
                "is_pwd" => 'L',
                "is_sc" => 'M',
            ],
            "Conferencing" => [
                "sessions_conducted" => 'N',
                "total_clients" => 'O',
                "gender" => ["M" => 'Q', "F" => 'P'],
                "is_pwd" => 'R',
                "is_sc" => 'S',
            ],
            "Circle of Support" => [
                "sessions_conducted" => 'T',
                "total_clients" => 'U',
                "gender" => ["M" => 'W', "F" => 'V'],
                "is_pwd" => 'X',
                "is_sc" => 'Y',
            ],
            "Others" => [
                "sessions_conducted" => 'Z',
                "total_clients" => 'AA',
                "gender" => ["M" => 'AC', "F" => 'AB'],
                "is_pwd" => 'AD',
                "is_sc" => 'AE',
            ],
        ];

        foreach ($this->data as $fieldOffice=>$activities)
        {
            $this->lastFilledOutCellY++;

            foreach ($activities as $rjType=>$activity) {
                $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, $fieldOffice);
                foreach ($activity as $field=>$value) {
                    if ('gender' === $field) {
                        foreach ($value as $type=>$val) {
                            $spreadsheet->getActiveSheet()
                                ->setCellValue($coordinates[$rjType][$field][$type] . $this->lastFilledOutCellY, $val);
                        }

                        continue;
                    }

                    $spreadsheet->getActiveSheet()->setCellValue($coordinates[$rjType][$field] . $this->lastFilledOutCellY, $value);
                }
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
            'A1' => 'REGION ' . $this->region->getName(),
            'A2' => 'IQPR SUMMARY FORM',
            'A3' => $this->quarter->getName() . ' QTR, ' . $this->quarter->getYear(),
            'A4' => 'I.B.1   RESTORATIVE JUSTICE(RJ)',
            'A5' => "Table I.B.1   Number of RJ Processes Conducted/ Clients' Involvement",
            'A6' => 'PETITIONERS',
            'A7' => 'FIELD OFFICES',
            'B7' => 'T    O    T    A    L            N    U    M    B    E    R',
            'B8' => self::PRE_ENCOUNTER_ACT,
            'H8' => 'Mediation',
            'N8' => 'Conferencing',
            'U8' => 'CIRCLE OF SUPPORT',
            'AB8' => 'Others',
            'B9' => '# of Acts CONDUCTED',
            'C9' => 'Total # of Clients Involved',
            'D9' => 'SEX',
            'D10' => 'F',
            'E10' => 'M',
            'F9' => 'PWD',
            'G9' => 'SC',
            'H9' => 'Sessions CONDUCTED',
            'I9' => 'Total # of Clients Involved',
            'J9' => 'SEX',
            'J10' => 'F',
            'K10' => 'M',
            'L9' => 'PWD',
            'M9' => 'SC',
            'N9' => 'Sessions CONDUCTED',
            'O9' => 'Total # of Clients Involved',
            'P9' => 'SEX',
            'P10' => 'F',
            'Q10' => 'M',
            'R9' => 'PWD',
            'S9' => 'SC',
            'T9' => 'Sessions CONDUCTED',
            'U9' => 'Total # of Clients Involved',
            'V9' => 'SEX',
            'V10' => 'F',
            'W10' => 'M',
            'X9' => 'PWD',
            'Y9' => 'SC',
            'Z9' => 'Sessions CONDUCTED',
            'AA9' => 'Total # of Clients Involved',
            'AB9' => 'SEX',
            'AB10' => 'F',
            'AC10' => 'M',
            'AD9' => 'PWD',
            'AE9' => 'SC',
        ];

        $mergesCoordinates = [
            'A7:A10', 'B7:AI7', 'B8:G8', 'H8:N8', 'N8:T8', 'U8:AA8', 'AB8:AE8', 'B9:B10', 'C9:C10', 'D9:E9', 'F9:F10', 'G9:G10', 'H9:H10',
            'I9:I10', 'J9:K9', 'L9:L10', 'M9:M10', 'N9:N10', 'O9:O10', 'P9:Q9', 'R9:R10', 'S9:S10', 'T9:T10', 'U9:U10', 'V9:W9', 'X9:X10',
            'Y9:Y10','Z9:Z10','AA9:AA10','AB9:AC9','AD9:AD10','AE9:AE10'
        ];

        $verticalAlignedCoordinates = ['A7:AE10' => 'center'];
        $horizontalAlignedCoordinates = ['A7:AE10' => 'center'];

        $adjustedColumnWidthCoordinates = ['A' => 30];

        $wrappedTextCoordinates = ['A7:AE10'];

        foreach ($textAndCoordinates as $coordinate => $text) {
            $spreadsheet->getActiveSheet()->setCellValue($coordinate, $text);
        }

        foreach ($mergesCoordinates as $coordinate) {
            $spreadsheet->getActiveSheet()->mergeCells($coordinate);
        }

        foreach ($wrappedTextCoordinates as $coordinate => $width) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getAlignment()->setWrapText(true);
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
}