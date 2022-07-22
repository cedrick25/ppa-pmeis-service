<?php

namespace App\Report\Table;

use App\Service\Volunteerism\SocialMarketing;
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

class TableIIIA2SummaryForm implements Form
{
    private const TABLE_NAME = "TableIIIA2SummaryForm";


    public function __construct(
        private SocialMarketing $service,
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

        $result = $this->data;

        $pao = 0;
        $others = 0;

        if ($result['rows']) {
            foreach ($result['rows'][0] as $v) {
                switch($v['social_marketing_activity_id']) {
                    case 3:
                        $pao++;
                        break;
                    case 4:
                        $others++;
                        break;
                }
            }
        }

        $spreadsheet->getActiveSheet()->setCellValue('B9', $pao);
        $spreadsheet->getActiveSheet()->setCellValue('C9', $pao);
        $spreadsheet->getActiveSheet()->setCellValue('B10', $pao);
        $spreadsheet->getActiveSheet()->setCellValue('C10', $pao);

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
            'A4' => 'III. SOCIAL MARKETING',
            'A5' => 'Table III.A.2 - MEETINGS /PARTICIPATIONS IN PEACE & ORDER COUNCIL (POC)/ ANTI-DRUG ABUSE COUNCIL (CADAC), MANAGEMENT SCREENING & EVALUATION COMMITTEE (MSEC)',
            'A7' => 'ACTIVITY/ PARTICULARS',
            'A9' => '1. Peace and Order Council ( POC )/
             AntiKDrug Abuse Council ( CADAC ),
             MSEC, DDB Representatives, etc.',
            'A10' => '2. Attendance in significant events as
             Representative of the Agency',

            'B7' => 'Number of',
            'B8' => 'Activities Conducted / Attended',
            'C8' => 'Participants (Headcount)',
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

        $result = $this->service->getReport(
            $data['quarter_id'],
            $data['field_office_id'],
            'MEETINGS_PARTICIPATIONS',
        );

        return ['rows' => array_values($result['data'] ?? [])];
    }
}