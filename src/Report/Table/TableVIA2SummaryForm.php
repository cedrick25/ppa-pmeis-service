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
        private SpecialAssignment   $specialAssignmentService,
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

        $spreadsheet->getActiveSheet()->setCellValue('B10', count($this->data['rows']['SPECIAL_ASSIGNMENT']['national']) + count($this->data['rows']['SPECIAL_ASSIGNMENT']['regional']) + count($this->data['rows']['SPECIAL_ASSIGNMENT']['field_office']));
        $spreadsheet->getActiveSheet()->setCellValue('B11', count($this->data['rows']['SPECIAL_ASSIGNMENT']['national']));
        $spreadsheet->getActiveSheet()->setCellValue('B12', count($this->data['rows']['SPECIAL_ASSIGNMENT']['regional']));
        $spreadsheet->getActiveSheet()->setCellValue('B13', count($this->data['rows']['SPECIAL_ASSIGNMENT']['field_office']));
        $spreadsheet->getActiveSheet()->setCellValue('B15', count($this->data['rows']['MISCELLANEOUS_ACTIVITIES']));
        $spreadsheet->getActiveSheet()->setCellValue('B16', count($this->data['rows']['SPECIAL_ASSIGNMENT']['national']) + count($this->data['rows']['SPECIAL_ASSIGNMENT']['regional']) + count($this->data['rows']['SPECIAL_ASSIGNMENT']['field_office']) + count($this->data['rows']['MISCELLANEOUS_ACTIVITIES']));

        $spreadsheet->getActiveSheet()->setCellValue('C10', count($this->data['rows']['SPECIAL_ASSIGNMENT']['national']) + count($this->data['rows']['SPECIAL_ASSIGNMENT']['regional']) + count($this->data['rows']['SPECIAL_ASSIGNMENT']['field_office']));
        $spreadsheet->getActiveSheet()->setCellValue('C11', count($this->data['rows']['SPECIAL_ASSIGNMENT']['national']));
        $spreadsheet->getActiveSheet()->setCellValue('C12', count($this->data['rows']['SPECIAL_ASSIGNMENT']['regional']));
        $spreadsheet->getActiveSheet()->setCellValue('C13', count($this->data['rows']['SPECIAL_ASSIGNMENT']['field_office']));
        $spreadsheet->getActiveSheet()->setCellValue('C15', count($this->data['rows']['MISCELLANEOUS_ACTIVITIES']));
        $spreadsheet->getActiveSheet()->setCellValue('C16', count($this->data['rows']['SPECIAL_ASSIGNMENT']['national']) + count($this->data['rows']['SPECIAL_ASSIGNMENT']['regional']) + count($this->data['rows']['SPECIAL_ASSIGNMENT']['field_office']) + count($this->data['rows']['MISCELLANEOUS_ACTIVITIES']));

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

        $results = $this->specialAssignmentService->getReport($data['quarter_id'], $data['field_office_id']);

        return ['rows' => $results['data'] ?? []];
    }
}