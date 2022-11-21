<?php

declare(strict_types=1);

namespace App\Report\Table;

use App\Enum\SystemSettingNames;
use App\Service\Volunteerism\SocialMarketing;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SMIIIA1And2 implements Form
{
    private const TABLE_NAME = "SMIIIA2";
    
    public function __construct(
        private SocialMarketing $service,
        private int             $lastFilledOutCellY = 3,
        private array           $data = [],
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

        return $spreadsheet;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();

        foreach ($this->data['rows'] as $socialMarketing) {
            $this->lastFilledOutCellY++;
            $spreadsheet->getActiveSheet()->setCellValue(
                'A' . $this->lastFilledOutCellY,
                trim(preg_replace('/\s\s+/', ' ', $socialMarketing['social_marketing_activity'] ?? ''))
            );
            $spreadsheet->getActiveSheet()->setCellValue(
                'B' . $this->lastFilledOutCellY,
                $socialMarketing['date'] . ' ' . $socialMarketing['venue']
            );
            $spreadsheet->getActiveSheet()->setCellValue(
                'G' . $this->lastFilledOutCellY,
                'primers: ' . $socialMarketing['primers'] .
                ' - ' . $socialMarketing['remarks']
            );

            $participantCellY = $this->lastFilledOutCellY;
            foreach ($socialMarketing['participants'] as $participant) {
                $spreadsheet->getActiveSheet()->setCellValue('C' . $participantCellY, $participant['no']);
                $spreadsheet->getActiveSheet()->setCellValue('D' . $participantCellY, $participant['type']);

                $participantCellY++;
            }

            $personInvolvedCellY = $this->lastFilledOutCellY;
            foreach ($socialMarketing['personInvolved'] as $personInvolved) {
                $cellX = ('VPA' == $personInvolved['type']['value']) ? 'F' : 'E';
                $name = (strlen($personInvolved['othersName']) > 0)
                    ? $personInvolved['othersName']
                    : $personInvolved['id']['label'];

                $spreadsheet->getActiveSheet()->setCellValue(
                    $cellX . $personInvolvedCellY,
                    $name . '/' . $personInvolved['role']['value']
                );

                $personInvolvedCellY++;
            }

            $this->lastFilledOutCellY = max($participantCellY, $personInvolvedCellY);
        }

        $this->lastFilledOutCellY++;

        $spreadsheet->getActiveSheet()->getStyle('A4:G' . $this->lastFilledOutCellY)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('A4:G' . $this->lastFilledOutCellY)
            ->getAlignment()->setHorizontal('center');
        $spreadsheet->getActiveSheet()->getStyle('A4:G' . $this->lastFilledOutCellY)
            ->getAlignment()->setVertical('center');
        $spreadsheet->getActiveSheet()->getStyle('A4:G' . $this->lastFilledOutCellY)
            ->getAlignment()->setWrapText(true);
        return $spreadsheet;
    }

    public function header(): Spreadsheet
    {
        return $this->prepare();
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function prepare(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $textAndCoordinates = [
            'a1' => 'Table  III.A.2  -  MEETINGS /PARTICIPATIONS IN PEACE & ORDER COUNCIL (POC)/ ANTI-DRUG ABUSE COUNCIL (CADAC)/ MANAGEMENT SCREENING & EVALUATION COMMITTEE (MSEC), DDB AUTHORIZED REPRESENTATIVE, ETC.',
            'g1' => $this->data[SystemSettingNames::GENERATED_REPORTS_CODE],
            'a2' => 'Activity',
            'a3' => '(1)',
            'b2' => 'Date.Venue',
            'b3' => '(2)',
            'c2' => 'Participants (3)',
            'c3' => 'No.',
            'd3' => 'Type',
            'e2' => 'Name of Person/ s Involved  (4)',
            'e3' => 'Personnel',
            'f3' => 'Role',
            'g2' => 'Remarks',
            'g3' => '(5)',

        ];
        $mergesCoordinates = [
            'C2:D2', 'E2:F2',
        ];
        $boldCoordinates = ['a1'];
        $verticalAlignedCoordinates = ['B2:G11' => 'center', 'A2:A3' => 'center'];
        $horizontalAlignedCoordinates = ['B2:G11' => 'center', 'A2:A3' => 'center'];
        $adjustedColumnWidthCoordinates = [
            'A' => 63, 'B' => 45, 'C' => 10, 'D' => 10, 'E' => 25, 'F' => 15, 'G' => 15, 'H' => 20,
        ];
        $outlineBorderThinCoordinates = [
            'A2:A3', 'B2:B3', 'C2:D2', 'E2:F2', 'G2:G3', 
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
        foreach ($outlineBorderThinCoordinates as $coordinate) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)
                ->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        }

        return $spreadsheet;
    }

    private function getData(array $data): array
    {
        $result = $this->service->getReport(
            $data['quarter_id'],
            $data['field_office_id'],
            $data['type'],
        );

        return ['rows' => array_values($result['data'] ?? [])];
    }
}