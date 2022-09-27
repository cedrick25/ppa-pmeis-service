<?php

declare(strict_types=1);

namespace App\Report\Table;

use App\Enum\SystemSettingNames;
use App\Service\TherapeuticCommunity\ResourceFacilitatorSession;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VPAIC3 implements Form
{
    private const TABLE_NAME = "VPAIC3";
    
    public function __construct(
        private ResourceFacilitatorSession  $service,
        private int                         $lastFilledOutCellY = 6,
        private array                       $data = [],
    ){}

    public function supports(string $tableName): bool
    {
        return self::TABLE_NAME === $tableName;
    }

    /**
     * @throws Exception
     */
    public function generate(array $data): BinaryFileResponse
    {
        $this->data = $this->getData($data);
        $this->data[SystemSettingNames::GENERATED_REPORTS_CODE] = $data[SystemSettingNames::GENERATED_REPORTS_CODE];

        $spreadsheet = $this->footer();
        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");

        $filePath = $_ENV['XLSX_PATH_FILE'] . self::TABLE_NAME . "-" . time() . ".xlsx";
        $writer->save($filePath);

        return new BinaryFileResponse($filePath);
    }

    public function footer(): Spreadsheet
    {
        $spreadsheet = $this->body();

        $this->lastFilledOutCellY++;
        $this->lastFilledOutCellY++;
        $lastFilledOutCellY = $this->lastFilledOutCellY;

        $spreadsheet->getActiveSheet()->setCellValue('B' . $this->lastFilledOutCellY, 'NOTE:  ATTACH ATTENDANCE SHEETS OF ALL ACTIVITIES (Include, if any, program / invitation / pictures)');
        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('B' . $this->lastFilledOutCellY, '             SERVICES RENDERED   (COLUMN 5)');
        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('C' . $this->lastFilledOutCellY, '     -  Monitoring (Home/ Work visits)');
        $spreadsheet->getActiveSheet()->setCellValue('I' . $this->lastFilledOutCellY, '   -   Sports/ Recreational activities');
        $spreadsheet->getActiveSheet()->setCellValue('O' . $this->lastFilledOutCellY, '    -   RJ Processes');
        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('C' . $this->lastFilledOutCellY, '     -  Counseling');
        $spreadsheet->getActiveSheet()->setCellValue('I' . $this->lastFilledOutCellY, '   -   Engagement in community work service');
        $spreadsheet->getActiveSheet()->setCellValue('O' . $this->lastFilledOutCellY, '    -   Referral to appropriate agencies or professional for');
        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('C' . $this->lastFilledOutCellY, '     -  Job Referral');
        $spreadsheet->getActiveSheet()->setCellValue('I' . $this->lastFilledOutCellY, '   -   Livelihood programs');
        $spreadsheet->getActiveSheet()->setCellValue('O' . $this->lastFilledOutCellY, '         specialized and support services (counseling, seminars, etc.)');
        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('C' . $this->lastFilledOutCellY, '     -   Participation in values formation activities');
        $spreadsheet->getActiveSheet()->setCellValue('I' . $this->lastFilledOutCellY, '   -   Skills Training');
        $spreadsheet->getActiveSheet()->setCellValue('O' . $this->lastFilledOutCellY, '    -   Drug/ alcohol test');

        $spreadsheet->getActiveSheet()->getStyle('B' . $lastFilledOutCellY . ':O' . $this->lastFilledOutCellY)->getFont()->setBold(true);

        return $spreadsheet;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();
        $total = [
            'volunteer' => ['F' => 0, 'M' => 0],
            'clients' => ['F' => 0, 'M' => 0],
        ];

        foreach ($this->data['rows'] as $row) {
            $volunteer = $row['volunteer'];
            $volunteerName = $volunteer['first_name'] . ' ' . $volunteer['middle_name'] . ' ' . $volunteer['last_name'];
            $this->lastFilledOutCellY++;
            $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, $volunteerName);
            if ($volunteer['gender'] === 'F') {
                $spreadsheet->getActiveSheet()->setCellValue('D' . $this->lastFilledOutCellY, '∕');
                $total['volunteer']['F']++;
            } else {
                $spreadsheet->getActiveSheet()->setCellValue('E' . $this->lastFilledOutCellY, '∕');
                $total['volunteer']['M']++;
            }

            foreach ($row['clients'] as $index=>$client) {
                if ($index === 0) {
                    $spreadsheet->getActiveSheet()->mergeCells('A' . $this->lastFilledOutCellY . ':C' . $this->lastFilledOutCellY);
                    $spreadsheet->getActiveSheet()->mergeCells('F' . $this->lastFilledOutCellY . ':H' . $this->lastFilledOutCellY);
                    $spreadsheet->getActiveSheet()->mergeCells('K' . $this->lastFilledOutCellY . ':L' . $this->lastFilledOutCellY);
                    $spreadsheet->getActiveSheet()->mergeCells('M' . $this->lastFilledOutCellY . ':O' . $this->lastFilledOutCellY);
                    $spreadsheet->getActiveSheet()->mergeCells('P' . $this->lastFilledOutCellY . ':R' . $this->lastFilledOutCellY);
                    $spreadsheet->getActiveSheet()->mergeCells('S' . $this->lastFilledOutCellY . ':T' . $this->lastFilledOutCellY);
                }
                if ($index > 0) {
                    $this->lastFilledOutCellY++;
                }

                $clientFullName = $client['first_name'] . ' ' . $client['middle_name'] . ' ' . $client['last_name'];
                $spreadsheet->getActiveSheet()->setCellValue("F" . $this->lastFilledOutCellY, $clientFullName);
                if ($client['gender'] === 'F') {
                    $spreadsheet->getActiveSheet()->setCellValue('I' . $this->lastFilledOutCellY, '∕');
                    $total['clients']['F']++;
                } else {
                    $spreadsheet->getActiveSheet()->setCellValue('J' . $this->lastFilledOutCellY, '∕');
                    $total['clients']['M']++;
                }

                $spreadsheet->getActiveSheet()->setCellValue('K' . $this->lastFilledOutCellY, $client['service_rendered']);
                $spreadsheet->getActiveSheet()->setCellValue('M' . $this->lastFilledOutCellY, $client['community_resources_tapped']);
                $spreadsheet->getActiveSheet()->setCellValue('P' . $this->lastFilledOutCellY, $client['assistance_received']);
                $spreadsheet->getActiveSheet()->setCellValue('S' . $this->lastFilledOutCellY, $client['remarks']);

                $spreadsheet->getActiveSheet()->mergeCells('A' . $this->lastFilledOutCellY . ':C' . $this->lastFilledOutCellY);
                $spreadsheet->getActiveSheet()->mergeCells('F' . $this->lastFilledOutCellY . ':H' . $this->lastFilledOutCellY);
                $spreadsheet->getActiveSheet()->mergeCells('K' . $this->lastFilledOutCellY . ':L' . $this->lastFilledOutCellY);
                $spreadsheet->getActiveSheet()->mergeCells('M' . $this->lastFilledOutCellY . ':O' . $this->lastFilledOutCellY);
                $spreadsheet->getActiveSheet()->mergeCells('P' . $this->lastFilledOutCellY . ':R' . $this->lastFilledOutCellY);
                $spreadsheet->getActiveSheet()->mergeCells('S' . $this->lastFilledOutCellY . ':T' . $this->lastFilledOutCellY);
            }
        }
        $this->lastFilledOutCellY++;

        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, 'TOTAL');
        $spreadsheet->getActiveSheet()->setCellValue('D' . $this->lastFilledOutCellY, $total['volunteer']['F']);
        $spreadsheet->getActiveSheet()->setCellValue('E' . $this->lastFilledOutCellY, $total['volunteer']['M']);
        $spreadsheet->getActiveSheet()->setCellValue('F' . $this->lastFilledOutCellY, 'TOTAL');
        $spreadsheet->getActiveSheet()->setCellValue('I' . $this->lastFilledOutCellY, $total['clients']['F']);
        $spreadsheet->getActiveSheet()->setCellValue('J' . $this->lastFilledOutCellY, $total['clients']['M']);
        $spreadsheet->getActiveSheet()
            ->getStyle("P" . $this->lastFilledOutCellY . ":T" . $this->lastFilledOutCellY)
            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('80808080');

        $spreadsheet->getActiveSheet()->mergeCells('A' . $this->lastFilledOutCellY . ':C' . $this->lastFilledOutCellY);
        $spreadsheet->getActiveSheet()->mergeCells('F' . $this->lastFilledOutCellY . ':H' . $this->lastFilledOutCellY);
        $spreadsheet->getActiveSheet()->mergeCells('K' . $this->lastFilledOutCellY . ':L' . $this->lastFilledOutCellY);
        $spreadsheet->getActiveSheet()->mergeCells('M' . $this->lastFilledOutCellY . ':O' . $this->lastFilledOutCellY);
        $spreadsheet->getActiveSheet()->mergeCells('P' . $this->lastFilledOutCellY . ':R' . $this->lastFilledOutCellY);
        $spreadsheet->getActiveSheet()->mergeCells('S' . $this->lastFilledOutCellY . ':T' . $this->lastFilledOutCellY);
        $spreadsheet->getActiveSheet()->getStyle('A7:S' . $this->lastFilledOutCellY)->getAlignment()->setHorizontal('center');
        $spreadsheet->getActiveSheet()->getStyle('A7:S' . $this->lastFilledOutCellY)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);


        return $spreadsheet;
    }

    public function header(): Spreadsheet
    {
        $spreadsheet = $this->prepare();

        return $spreadsheet;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function prepare(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $textAndCoordinates = [
            'A1' => 'Table  I.C.3 – SUPERVISION ACTIVITIES', 'T1' => $this->data[SystemSettingNames::GENERATED_REPORTS_CODE], 'D3' => 'Sex', 'I3' => 'Sex', 'M3' => 'Community Resource Development (CRD)   (6)',
            'S3' => 'Remarks   (7)', 'A4' => 'Name', 'D4' => '(2)', 'F4' => 'Name of Client/s Supervised', 'I4' => '(4)', 'K4' => 'Services Rendered', 'S4' => '(Indicate issues, problems',
            'A5' => '(1)', 'D5' => 'F', 'E5' => 'M', 'F5' => '(3)', 'I5' => 'F', 'J5' => 'M', 'K5' => '(5)', 'M5' => 'Community Resources Tapped', 'P5' => 'Assistance Received',
            'S5' => 'encountered & other', 'S6' => 'relevant information)'
        ];
        $mergesCoordinates = [
            'A4:C4', 'A5:C5', 'D3:E3', 'D4:E4', 'F4:H4', 'F5:H5', 'I3:J3', 'I4:J4', 'K4:L4', 'K5:L5', 'M5:O6', 'P5:R6', 'S3:T3',
            'S4:T4', 'S5:T5', 'S6:T6', 'M3:R4', 'D5:D6', 'E5:E6', 'I5:I6', 'J5:J6', 'M5:O6', 'P5:R6'
        ];
        $boldCoordinates = ['A1:T1', 'F5:H5', 'A5:C5', 'D4:E4','I4:J4', 'K5:L5'];
        $verticalAlignedCoordinates = ['A3:T6' => 'center'];
        $horizontalAlignedCoordinates = ['A3:T6' => 'center'];
        $adjustedColumnWidthCoordinates = [
            'A' => 15, 'B' => 15, 'C' => 15, 'D' => 5, 'E' => 5, 'F' => 15, 'G' => 15, 'H' => 15, 'I' => 5, 'J' => 5, 'K' => 15, 'L' => 15,
            'M' => 15, 'N' => 15, 'O' => 15, 'P' => 15, 'Q' => 15, 'R' => 15, 'S' => 15, 'T' => 15
        ];
        $outlineBorderThinCoordinates = ['A3:C6', 'D3:E4', 'D5:D6', 'E5:E6', 'F3:H6', 'I3:J4', 'I5:I6', 'J5:J6', 'K3:L6', 'M3:R4', 'M5:O6', 'P5:R6', 'S3:T6'];

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

        foreach ($outlineBorderThinCoordinates as $coordinate) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        }

        return $spreadsheet;
    }

    private function getData(array $data): array
    {
        $result = $this->service->getVPA3(
            $data['field_office_id'],
            $data['quarter_id']
        );

        return ['rows' => $result['data'] ?? []];
    }
}