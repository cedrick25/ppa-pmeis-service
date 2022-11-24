<?php

namespace App\Report\Table;

use App\Service\Volunteerism\TechnicalAssistance;
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

class TableIIIA3SummaryForm implements Form
{
    private const TABLE_NAME = "TableIIIA3SummaryForm";


    public function __construct(
        private TechnicalAssistance $service,
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
            "A7:D13"
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

        echo var_dump($result['rows']);

        $activityRoles = [
            'TECHNICAL ASSISTANCE' => [
                'agencies_assisted'   => 0,
                'assistance_rendered' => 0,
                'participants'        => 0,
            ],
            'OUTREACH ACTIVITIES TO OTHER AGENCIES' => [
                'agencies_assisted'   => 0,
                'assistance_rendered' => 0,
                'participants'        => 0,
            ],
            'OTHER RELATED COMMUNITY PARTICIPATION' => [
                'agencies_assisted'   => 0,
                'assistance_rendered' => 0,
                'participants'        => 0,
            ],
            'PUBLIC ASSISTANCE' => [
                'agencies_assisted'   => 0,
                'assistance_rendered' => 0,
                'participants'        => 0,
            ],
        ];
        
        if ($result['rows']) {
            foreach ($result['rows'] as $v) {
                foreach ($v['participants'] as $participant) {
                    $paoParticipants += $participant['no'];
                }
            }
        }

        $spreadsheet->getActiveSheet()->setCellValue('B9', $activityRoles['TECHNICAL ASSISTANCE']['agencies_assisted']);
        $spreadsheet->getActiveSheet()->setCellValue('C9', $activityRoles['TECHNICAL ASSISTANCE']['assistance_rendered']);
        $spreadsheet->getActiveSheet()->setCellValue('D9', $activityRoles['TECHNICAL ASSISTANCE']['participants']);

        $spreadsheet->getActiveSheet()->setCellValue('B10', $activityRoles['OUTREACH ACTIVITIES TO OTHER AGENCIES']['agencies_assisted']);
        $spreadsheet->getActiveSheet()->setCellValue('C10', $activityRoles['OUTREACH ACTIVITIES TO OTHER AGENCIES']['assistance_rendered']);
        $spreadsheet->getActiveSheet()->setCellValue('D10', $activityRoles['OUTREACH ACTIVITIES TO OTHER AGENCIES']['participants']);

        $spreadsheet->getActiveSheet()->setCellValue('B11', $activityRoles['OTHER RELATED COMMUNITY PARTICIPATION']['agencies_assisted']);
        $spreadsheet->getActiveSheet()->setCellValue('C11', $activityRoles['OTHER RELATED COMMUNITY PARTICIPATION']['assistance_rendered']);
        $spreadsheet->getActiveSheet()->setCellValue('D11', $activityRoles['OTHER RELATED COMMUNITY PARTICIPATION']['participants']);
        
        $spreadsheet->getActiveSheet()->setCellValue('B12', $activityRoles['PUBLIC ASSISTANCE']['agencies_assisted']);
        $spreadsheet->getActiveSheet()->setCellValue('C12', $activityRoles['PUBLIC ASSISTANCE']['assistance_rendered']);
        $spreadsheet->getActiveSheet()->setCellValue('D12', $activityRoles['PUBLIC ASSISTANCE']['participants']);

        $spreadsheet->getActiveSheet()->setCellValue('B13', $activityRoles['TECHNICAL ASSISTANCE']['agencies_assisted'] + $activityRoles['OUTREACH ACTIVITIES TO OTHER AGENCIES']['agencies_assisted'] + $activityRoles['OTHER RELATED COMMUNITY PARTICIPATION']['agencies_assisted'] + $activityRoles['PUBLIC ASSISTANCE']['agencies_assisted']);
        $spreadsheet->getActiveSheet()->setCellValue('C13', $activityRoles['TECHNICAL ASSISTANCE']['assistance_rendered'] + $activityRoles['OUTREACH ACTIVITIES TO OTHER AGENCIES']['assistance_rendered'] + $activityRoles['OTHER RELATED COMMUNITY PARTICIPATION']['assistance_rendered'] + $activityRoles['PUBLIC ASSISTANCE']['assistance_rendered']);
        $spreadsheet->getActiveSheet()->setCellValue('D13', $activityRoles['TECHNICAL ASSISTANCE']['participants'] + $activityRoles['OUTREACH ACTIVITIES TO OTHER AGENCIES']['participants'] + $activityRoles['OTHER RELATED COMMUNITY PARTICIPATION']['participants'] + $activityRoles['PUBLIC ASSISTANCE']['participants']);

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
            'A5' => 'Table,III. A.3. Technical Assistance/ Outreach Activities to Other Agencies/ Other Related Community Participation /Public Assistance',
            'A7' => 'PARTICULARS',
            'A9' => '1. Technical Assistance to other Agencies',
            'A10' => '2. Outreach Activities to other Agencies',
            'A11' => '3. Other related community participation',
            'A12' => '4. Public Assistance Total no. of walkKin clientsassisted (per office log book/ record)',
            'A13' => 'TOTAL',

            'B7' => 'TOTAL NUMBER',
            'B8' => 'Number of Agencies Assisted',
            'C8' => 'Assistance Rendered',
            'D8' => 'Participants / Beneficiaries',
        ];

        $mergesCoordinates = [
            'A1:D1',
            'A2:D2',
            'A3:D3',
            'A4:D4',
            'A5:D5',
            'A7:A8',
            'B7:D7',
        ];

        $boldCoordinates = [
            'A1:D8',
        ];

        $verticalAlignedCoordinates = [
            'A1:D1' => 'center',
            'A2:D2' => 'center',
            'A3:D3' => 'center',
            'A7:A8' => 'center',
            'B7:D8' => 'center',
            'A13' => 'center',
        ];

        $horizontalAlignedCoordinates = [
            'A1:D1' => 'center',
            'A2:D2' => 'center',
            'A3:D3' => 'center',
            'A7:A8' => 'center',
            'B7:D8' => 'center',
            'A13' => 'center',
        ];

        $adjustedColumnWidthCoordinates = [
            'A' => 35, 
            'G' => 15
        ];

        $wrappedTextCoordinates = [
            'A1:D13'
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
        );

        return ['rows' => array_values($result['data'] ?? [])];
    }
}