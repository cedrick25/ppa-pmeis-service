<?php

namespace App\Report\Table;

use App\Entity\FieldOffices;
use App\Entity\Quarters;
use App\Repository\FieldOfficesRepository;
use App\Repository\QuartersRepository;
use App\Service\TherapeuticCommunity\Sessions;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TableIA7SummaryForm implements Form
{
    private const TABLE_NAME = "TableIA7SummaryForm";


    public function __construct(
        private Sessions               $sessionService,
        private FieldOfficesRepository $fieldOfficesRepository,
        private QuartersRepository     $quartersRepository,
        private array                  $data = [],
        private ?FieldOffices          $fieldOffice = null,
        private ?Quarters              $quarters = null,
    ) {}

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
        $spreadsheet->getActiveSheet()->getStyle('A7:B11')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $spreadsheet;
    }

    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();

        $spreadsheet->getActiveSheet()->setCellValue('B8', $this->data['total_supervision_cases_handled']);
        $spreadsheet->getActiveSheet()->setCellValue('B9', $this->data['total_adjusted_supervision_caseLoad']);
        $spreadsheet->getActiveSheet()->setCellValue('B10', $this->data['clients_attending_tc']);
        $spreadsheet->getActiveSheet()->setCellValue('B11', $this->data['percentage_of_clients_attending_tc']);


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
            'A6' => "Table I.A.7   COMPUTATION",
            'A7' => 'Particulars',
            'B7' => 'Number/Percentage',
            'A8' => 'Total Supervision Cases Handled',
            'A9' => 'Total Adjusted Supervision Caseload this Quarter',
            'A10' => "Total Number of Clients' Attending TC",
            'A11' => "Percentage of Clients' Attending TC",
        ];

        $mergesCoordinates = [
            'A1:B1', 'A2:B2', 'A3:B3'
        ];

        $boldCoordinates = [
            'A1:A6', 'A9', 'A11'
        ];

        $verticalAlignedCoordinates = [
            'A1:B1' => 'center', 'A2:B2' => 'center', 'A3:B3' => 'center', 'A7:B7' => 'center'
        ];

        $horizontalAlignedCoordinates = [
            'A1:B1' => 'center', 'A2:B2' => 'center', 'A3:B3' => 'center', 'A7:B7' => 'center'
        ];

        $adjustedColumnWidthCoordinates = [
            'A' => 50, 'B' => 35
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

        $clientsAttendingTC = 0;
        $totalSupervisionCasesHandled = 0;
        $percentageOfClientsAttendingTCScore = 0;
        $totalAdjustedSupervisionCaseLoad = 0;
        $tc7 = $this->sessionService->getTC7(intval($data['quarter_id']), intval($data['field_office_id']));

        foreach ($tc7['data']['totalSupervisionCasesHandled'] as $score) {
            $totalSupervisionCasesHandled += $score;
        }

        foreach ($tc7['data']['totalAdjustedSupervisionCaseLoad'] as $score) {
            $totalAdjustedSupervisionCaseLoad += $score;
        }

        foreach ($tc7['data']['clientsAttendingTC'] as $score) {
            $clientsAttendingTC += $score;
        }

        $percentageOfClientsAttendingTC = $tc7['data']['percentageOfClientsAttendingTC'];
        foreach ($percentageOfClientsAttendingTC as $score) {
            $percentageOfClientsAttendingTCScore += $score;
        }
        $percentageOfClientsAttendingTCScore = count($percentageOfClientsAttendingTC) > 0
            ? $percentageOfClientsAttendingTCScore / count($percentageOfClientsAttendingTC)
            : 0;

        return [
            'total_supervision_cases_handled' => $totalSupervisionCasesHandled,
            'total_adjusted_supervision_caseLoad' => $totalAdjustedSupervisionCaseLoad,
            'clients_attending_tc' => $clientsAttendingTC,
            'percentage_of_clients_attending_tc' => $percentageOfClientsAttendingTCScore,
        ];
    }
}
