<?php

namespace App\Report\Table;

use App\Service\Volunteerism\SpecialAssignment;
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

class TableVIA2SummaryForm implements Form
{
    private const TABLE_NAME = "TableVIA2SummaryForm";


    public function __construct(
        private SpecialAssignment           $specialAssignmentService,
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
            "A7:C16"
        ];

        foreach ($thinBorders as $coordinate) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }
        return $spreadsheet;
    }

    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();

        $result = $this->data;

        // print_r($result['rows']);

        $activities = [
            'SPECIAL_ASSIGNMENT' => [
                'national' => [
                    'meetings' => count($this->data['rows']['SPECIAL_ASSIGNMENT']['national']),
                    'personnelInvolved' => 0,
                ],
                'regional' => [
                    'meetings' => count($this->data['rows']['SPECIAL_ASSIGNMENT']['regional']),
                    'personnelInvolved' => 0,
                ],
                'field_office' => [
                    'meetings' => count($this->data['rows']['SPECIAL_ASSIGNMENT']['field_office']),
                    'personnelInvolved' => 0,
                ],
            ],
            'MISCELLANEOUS_ACTIVITIES' => [
                'meetings' => count($this->data['rows']['MISCELLANEOUS_ACTIVITIES']),
                'personnelInvolved' => 0,
            ],
        ];

        foreach ($this->data['rows']['SPECIAL_ASSIGNMENT'] as $category => $v) {
            foreach($v as $activity) {
                $activities['SPECIAL_ASSIGNMENT'][$category]['personnelInvolved'] += count($activity['personnelInvolved']);
            }
        }

        foreach ($this->data['rows']['MISCELLANEOUS_ACTIVITIES'] as $activity) {
            $activities['MISCELLANEOUS_ACTIVITIES']['personnelInvolved'] += count($activity['personnelInvolved']);
        }

        $spreadsheet->getActiveSheet()->setCellValue('B10', $activities['SPECIAL_ASSIGNMENT']['national']['meetings'] + $activities['SPECIAL_ASSIGNMENT']['regional']['meetings'] + $activities['SPECIAL_ASSIGNMENT']['field_office']['meetings']);
        $spreadsheet->getActiveSheet()->setCellValue('B11', $activities['SPECIAL_ASSIGNMENT']['national']['meetings']);
        $spreadsheet->getActiveSheet()->setCellValue('B12', $activities['SPECIAL_ASSIGNMENT']['regional']['meetings']);
        $spreadsheet->getActiveSheet()->setCellValue('B13', $activities['SPECIAL_ASSIGNMENT']['field_office']['meetings']);
        $spreadsheet->getActiveSheet()->setCellValue('B15', $activities['MISCELLANEOUS_ACTIVITIES']['meetings']);
        $spreadsheet->getActiveSheet()->setCellValue('B16', $activities['SPECIAL_ASSIGNMENT']['national']['meetings'] + $activities['SPECIAL_ASSIGNMENT']['regional']['meetings'] + $activities['SPECIAL_ASSIGNMENT']['field_office']['meetings'] + $activities['MISCELLANEOUS_ACTIVITIES']['meetings']);

        $spreadsheet->getActiveSheet()->setCellValue('C10', $activities['SPECIAL_ASSIGNMENT']['national']['personnelInvolved'] + $activities['SPECIAL_ASSIGNMENT']['regional']['personnelInvolved'] + $activities['SPECIAL_ASSIGNMENT']['field_office']['personnelInvolved']);
        $spreadsheet->getActiveSheet()->setCellValue('C11', $activities['SPECIAL_ASSIGNMENT']['national']['personnelInvolved']);
        $spreadsheet->getActiveSheet()->setCellValue('C12', $activities['SPECIAL_ASSIGNMENT']['regional']['personnelInvolved']);
        $spreadsheet->getActiveSheet()->setCellValue('C13', $activities['SPECIAL_ASSIGNMENT']['field_office']['personnelInvolved']);
        $spreadsheet->getActiveSheet()->setCellValue('C15', $activities['MISCELLANEOUS_ACTIVITIES']['personnelInvolved']);
        $spreadsheet->getActiveSheet()->setCellValue('C16', $activities['SPECIAL_ASSIGNMENT']['national']['personnelInvolved'] + $activities['SPECIAL_ASSIGNMENT']['regional']['personnelInvolved'] + $activities['SPECIAL_ASSIGNMENT']['field_office']['personnelInvolved'] + $activities['MISCELLANEOUS_ACTIVITIES']['personnelInvolved']);

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
            'A4' => 'VI. SUPPORT FUNCTION',
            'A5' => 'Table VI.A.2. Special Assignments/ Miscellaneous Activities',
            'A7' => 'Description',
            'A10' => 'I. Special Assignments',
            'A11' => 'a. National',
            'A12' => 'b. Regional',
            'A13' => 'c. Local',
            'A15' => 'II. Miscellaneous Activities',
            'A16' => 'T O T A L',

            'B7' => 'N U M B E R',
            'B8' => 'Meetings/ Activities',
            'C8' => 'Personnel Involved',
            'C9' => '(Head Count only)',
        ];

        $mergesCoordinates = [
            'A1:C1',
            'A2:C2',
            'A3:C3',
            'A4:C4',
            'A5:C5',
            'A7:A9',
            'B8:B9',
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
            'B7:C16' => 'center',
        ];

        $horizontalAlignedCoordinates = [
            'A1:C1' => 'center',
            'A2:C2' => 'center',
            'A3:C3' => 'center',
            'A7:A8' => 'center',
            'B7:C16' => 'center',
        ];

        $adjustedColumnWidthCoordinates = [
            'A' => 30, 
            'B' => 20,
            'C' => 25,
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

        $results = $this->specialAssignmentService->getReport($data['quarter_id'], $data['field_office_id']);

        return ['rows' => $results['data'] ?? []];
    }
}