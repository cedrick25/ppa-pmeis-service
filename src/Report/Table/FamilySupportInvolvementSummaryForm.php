<?php

namespace App\Report\Table;

use App\Entity\FieldOffices;
use App\Entity\Quarters;
use App\Repository\ClientSessionsRepository;
use App\Repository\ClientsRepository;
use App\Repository\FieldOfficesRepository;
use App\Repository\PhasesRepository;
use App\Repository\QuartersRepository;
use App\Repository\SessionsRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FamilySupportInvolvementSummaryForm implements Form
{
    private const TABLE_NAME = "FamilySupportInvolvementSummaryForm";


    public function __construct(
        private FieldOfficesRepository      $fieldOfficesRepository,
        private QuartersRepository          $quartersRepository,
        private SessionsRepository          $sessionsRepository,
        private ClientSessionsRepository    $clientSessionsRepository,
        private ClientsRepository           $clientsRepository,
        private PhasesRepository            $phasesRepository,
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
        $spreadsheet->getActiveSheet()->getStyle('A7:G15')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $spreadsheet;
    }

    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();
        $cells = [
            'PS' => ['Prep.' => 'B10', 'I' => 'C10', 'II' => 'D10', 'III' => 'E10', 'IV' => 'F10', 'Total' => 'G10'],
            'PR' => ['Prep.' => 'B11', 'I' => 'C11', 'II' => 'D11', 'III' => 'E11', 'IV' => 'F11', 'Total' => 'G11'],
            'PD' => ['Prep.' => 'B12', 'I' => 'C12', 'II' => 'D12', 'III' => 'E12', 'IV' => 'F12', 'Total' => 'G12'],
            'JICL' => ['Prep.' => 'B13', 'I' => 'C13', 'II' => 'D13', 'III' => 'E13', 'IV' => 'F13', 'Total' => 'G13'],
            'FTMDO' => ['Prep.' => 'B14', 'I' => 'C14', 'II' => 'D14', 'III' => 'E14', 'IV' => 'F14', 'Total' => 'G14'],
            'Total' => ['Prep.' => 'B15', 'I' => 'C15', 'II' => 'D15', 'III' => 'E15', 'IV' => 'F15', 'Total' => 'G15'],
        ];

        foreach ($this->data as $role=>$fsi) {
            foreach ($fsi as $phase=>$score) {
                $spreadsheet->getActiveSheet()->setCellValue($cells[$role][$phase], $score);
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
            'A6' => "FAMILY SUPPORT INVOLVEMENT (Tables I.A.2 - 6)",
            'A7' => 'Classification',
            'B7' => 'Total Number of FSI',
            'G7' => 'Total',
            'B8' => 'Phase',
            'B9' => 'Prep.',
            'C9' => 'I',
            'D9' => 'II',
            'E9' => 'III',
            'F9' => 'IV',
            'A10' => 'PS',
            'A11' => 'PR',
            'A12' => 'PD',
            'A13' => 'JICL',
            'A14' => 'FTMDO /SS',
            'A15' => 'Total'
        ];

        $mergesCoordinates = [
            'A1:G1','A2:G2','A3:G3', 'A7:A9', 'B7:F7', 'G7:G9', 'B8:F8'
        ];

        $boldCoordinates = [
            'A1:A7', 'B7', 'G7', 'B8', 'B9:f9', 'A8:A15',
        ];

        $verticalAlignedCoordinates = [
            'A1:G1' => 'center', 'A2:G2' => 'center', 'A3:G3' => 'center', 'A7:G15' => 'center'
        ];

        $horizontalAlignedCoordinates = [
            'A1:G1' => 'center', 'A2:G2' => 'center', 'A3:G3' => 'center', 'A7:G15' => 'center'
        ];

        $adjustedColumnWidthCoordinates = [
            'A' => 30, 'G' => 15
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

        $results = [];
        $quarterData = $this->quartersRepository->find($data['quarter_id']);
        $sessions = $this->sessionsRepository->findByQuarterData($quarterData);

        foreach ($sessions as $session) {
            $clientSessions = $this->clientSessionsRepository->findBySessionIds([$session['session_id']]);
            $phase = $this->phasesRepository->find(intval($session['phase_id']));
            foreach ($clientSessions as $clientSession) {
                if (! isset($results[$clientSession['role']]['Total'])) {
                    $results[$clientSession['role']]['Total'] = 0;
                }

                if (! isset($results[$clientSession['role']][$phase->getName()])) {
                    $results[$clientSession['role']][$phase->getName()] = 0;
                }

                $results[$clientSession['role']]['Total'] += intval($clientSession['fsi']);
                $results[$clientSession['role']][$phase->getName()] += intval($clientSession['fsi']);
            }
        }

        return $results;
    }
}