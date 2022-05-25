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

class TableIIIA3SummaryForm implements Form
{
    private const TABLE_NAME = "TableIIIA3SummaryForm";


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

        $spreadsheet->getActiveSheet()->setCellValue('B9', $others);
        $spreadsheet->getActiveSheet()->setCellValue('C9', $others);
        $spreadsheet->getActiveSheet()->setCellValue('D9', $others);

        $spreadsheet->getActiveSheet()->setCellValue('B10', $others);
        $spreadsheet->getActiveSheet()->setCellValue('C10', $others);
        $spreadsheet->getActiveSheet()->setCellValue('D10', $others);

        $spreadsheet->getActiveSheet()->setCellValue('B11', $others);
        $spreadsheet->getActiveSheet()->setCellValue('C11', $others);
        $spreadsheet->getActiveSheet()->setCellValue('D11', $others);
        
        $spreadsheet->getActiveSheet()->setCellValue('B12', $others);
        $spreadsheet->getActiveSheet()->setCellValue('C12', $others);
        $spreadsheet->getActiveSheet()->setCellValue('D12', $others);
        $spreadsheet->getActiveSheet()->setCellValue('B13', $others);
        $spreadsheet->getActiveSheet()->setCellValue('C13', $others);
        $spreadsheet->getActiveSheet()->setCellValue('D13', $others);

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
            'A' => 35, 'G' => 15
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
            'MEETINGS_PARTICIPATIONS',
        );

        return ['rows' => array_values($result['data'] ?? [])];
    }
}