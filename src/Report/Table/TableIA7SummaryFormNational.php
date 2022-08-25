<?php

namespace App\Report\Table;

use App\Entity\FieldOffices;
use App\Entity\Quarters;
use App\Entity\Regions;
use App\Repository\FieldOfficesRepository;
use App\Repository\QuartersRepository;
use App\Repository\RegionsRepository;
use App\Service\TherapeuticCommunity\Sessions;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TableIA7SummaryFormNational implements Form
{
    private const TABLE_NAME = "TableIA7SummaryFormNational";


    public function __construct(
        private Sessions               $sessionService,
        private FieldOfficesRepository  $fieldOfficesRepository,
        private QuartersRepository     $quartersRepository,
        private RegionsRepository      $regionsRepository,
        private array                  $data = [],
        private ?Regions               $region = null,
        private ?Quarters              $quarter = null,
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
//        $this->data = $this->getData($quarterId, $regionId);

        $spreadsheet = $this->footer();
        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");

        $filePath = $_ENV['XLSX_PATH_FILE'] . self::TABLE_NAME . "-" . time() . ".xlsx";
        $writer->save($filePath);

        return new BinaryFileResponse($filePath);
    }

    public function header(): Spreadsheet
    {
        $spreadsheet = $this->prepare();
        $spreadsheet->getActiveSheet()->getStyle('A1:R6')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $spreadsheet;
    }

    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();




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
            'A1' => $this->quarter->getName() . ' QTR, ' . $this->quarter->getYear(),
            'A2' => 'I.A.7  COMPUTATION',
            'A3' => 'REGIONAL OFFICES',
            'B3' => 'Carry-over',
            'C3' => 'ADD: New Referrals',
            'D3' => 'LESS: Supv. Cases Dropped (Terminated,% Revoked, Transferred)',
            'E3' => 'Total Active Supervision (1+2) 63',
            'F3' => 'LESS',
            'F4' => 'On Courtesy Supervision to other FOs',
            'G4' => 'Died',
            'H4' => 'Absconded',
            'I4' => 'In Jail w/ no report submitted to court/ BPP',
            'J4' => 'With serious ailment',
            'K4' => 'On travel abroad',
            'L4' => 'Supervision cases dropped (Terminated, Revoked, Transferred)',
            'M4' => 'Cases Pending In Court/  BPP',
            'N4' => 'Others',
            'O4' => 'Sub-Total',
            'P3' => 'Total Adjusted Active Supervision Caseload this qrt.',
            'Q3' => 'Total Number TC Pax this Qtr.',
            'R3' => '% (16/%15)',
            'B5' => '1', 'C5' => '2', 'D5' => '3', 'E5' => '4', 'F5' => '5', 'G5' => '6', 'H5' => '7', 'I5' => '8', 'J5' => '9',
            'K5' => '10', 'L5' => '11', 'M5' => '12', 'N5' => '13', 'O5' => '14', 'P5' => '15', 'Q5' => '16', 'R5' => '17',
        ];

        $mergesCoordinates = [
            'A1:R1', 'A2:R2', 'A3:A4', 'B3:B4', 'C3:C4', 'D3:D4', 'E3:E4', 'F3:O3', 'P3:P4', 'Q3:Q4', 'R3:R4'
        ];

        $boldCoordinates = ['A1:R3'];

        $verticalAlignedCoordinates = ['A1:R4' => 'center'];

        $horizontalAlignedCoordinates = ['A1:R4' => 'center'];

        $adjustedColumnWidthCoordinates = [
            'A' => 35, 'Q' => 15
        ];

        $wrappedTextCoordinates = ['A1:R3'];

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

    private function getData(int $quarterId, int $regionId): array
    {

        $clientsAttendingTC = 0;
        $totalSupervisionCasesHandled = 0;
        $percentageOfClientsAttendingTCScore = 0;
        $totalAdjustedSupervisionCaseLoad = 0;
        $tc7 = $this->sessionService->getTC7($quarterId, $fieldOffice = 1);

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
