<?php

declare(strict_types=1);

namespace App\Report\Table;

use App\Enum\SystemSettingNames;
use App\Service\Volunteerism\CapabilityBuilding;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CBIIA1 implements Form
{
    private const TABLE_NAME = "CBIIA1";
    
    public function __construct(
        private CapabilityBuilding $capabilityBuilding,
        private int                $lastFilledOutCellY = 7,
        private array              $data = [],
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

    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();

        $total = ['nop' => 0, 'technical' => 0, 'foundation' => 0];

        foreach ($this->data['rows'] as $subtype => $rows) {
            $this->lastFilledOutCellY++;
            $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, $subtype);

            $this->lastFilledOutCellY++;
            foreach ($rows as $row) {
                $spreadsheet->getActiveSheet()->setCellValue("a" . $this->lastFilledOutCellY, $row['title']);
                $spreadsheet->getActiveSheet()->setCellValue("b" . $this->lastFilledOutCellY, $row['start_date'] . ' - ' . $row['end_date']);
                $spreadsheet->getActiveSheet()->setCellValue("c" . $this->lastFilledOutCellY, $row['no_of_participants']);
                $total['nop'] += (int) $row['no_of_participants'];
                if (intval($row['is_pwd']) > 0) {
                    $spreadsheet->getActiveSheet()->setCellValue("e" . $this->lastFilledOutCellY, '/');
                }
                if (intval($row['is_senior_citizen']) > 0) {
                    $spreadsheet->getActiveSheet()->setCellValue("f" . $this->lastFilledOutCellY, '/');
                }
                $spreadsheet->getActiveSheet()->setCellValue("g" . $this->lastFilledOutCellY, $row['not_managerial_supervisory']);

                if (intval($row['not_technical']) > 0) {
                    $spreadsheet->getActiveSheet()->setCellValue("h" . $this->lastFilledOutCellY, '/');
                    $total['technical']++;
                }
                if (intval($row['not_foundation']) > 0) {
                    $spreadsheet->getActiveSheet()->setCellValue("i" . $this->lastFilledOutCellY, '/');
                    $total['foundation']++;
                }
                $spreadsheet->getActiveSheet()->setCellValue("j" . $this->lastFilledOutCellY, $row['no_of_training_hours']);

                foreach ($row['participants'] as $participant) {
                    $spreadsheet->getActiveSheet()->setCellValue("d" . $this->lastFilledOutCellY, $participant['personnel_name']);
                    $spreadsheet->getActiveSheet()->setCellValue("k" . $this->lastFilledOutCellY, $participant['remarks']);
                    $this->lastFilledOutCellY++;
                }
            }
        }

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, 'TOTAL (Headcount)');
        $spreadsheet->getActiveSheet()->setCellValue('c' . $this->lastFilledOutCellY, $total['nop']);
        $spreadsheet->getActiveSheet()->mergeCells('A' . $this->lastFilledOutCellY . ':b' . $this->lastFilledOutCellY);
        $spreadsheet->getActiveSheet()->setCellValue('g' . $this->lastFilledOutCellY, 'TOTAL:');
        $spreadsheet->getActiveSheet()->setCellValue('h' . $this->lastFilledOutCellY, $total['technical']);
        $spreadsheet->getActiveSheet()->setCellValue('i' . $this->lastFilledOutCellY, $total['foundation']);

        $spreadsheet->getActiveSheet()->getStyle('A8:k' . $this->lastFilledOutCellY)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()
        ->getStyle("d" . $this->lastFilledOutCellY)
        ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('80808080');
        $spreadsheet->getActiveSheet()
        ->getStyle("j" . $this->lastFilledOutCellY. ':k' . $this->lastFilledOutCellY)
        ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('80808080');
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
            'a1' => 'II.  CAPABILITY BUILDING', 'l1' => $this->data[SystemSettingNames::GENERATED_REPORTS_CODE],
            'a3' => 'Table II.A.1 –  PERSONNEL',
            'a5' => 'Title', 'a6' => '(1)',
            'b5' => 'Date', 'b6' => '(2)',
            'c5' => 'No. of', 'c6' => 'Participants', 'c7' => '(3)', 
            'd5' => 'Name/s', 'd6' => '(4)',
            'e4' => 'P', 'e5' => 'W', 'e6' => 'D', 'e7' => '(5)',
            'f4' => 'S', 'f5' => 'C', 'f6' => '', 'f7' => '(6)',
            'g4' => 'Nature of Training   (7)', 'g5' => 'Managerial/ 
            Supervisory', 'h5' => 'Technical', 'I5' => 'Foundation',
            'j4' => 'No. of', 'j5' => 'Training', 'j6' => 'Hours', 'j7' => '(8)',
            'K4' => 'REMARKS', 'K7' => '(9)', 
        ];
        $mergesCoordinates = [
            'G4:I4', 'G5:G7', 'H5:H7', 'I5:I7', 'K4:K6',
        ];
        $boldCoordinates = [];
        $verticalAlignedCoordinates = ['b4:K47' => 'center', 'a4:a7' => 'center',];
        $horizontalAlignedCoordinates = ['b4:K47' => 'center', 'a4:a7' => 'center',];
        $adjustedColumnWidthCoordinates = [
            'A' => 49, 'B' => 20, 'C' => 15, 'D' => 20, 'E' => 5, 'F' => 5, 'G' => 20, 'H' => 10, 'I' => 10, 'J' => 10, 'K' => 10, 'L' => 10,
            'M' => 10, 'N' => 10,
        ];
        $outlineBorderThinCoordinates = [
            'A4:A7', 'B4:B7', 'C4:C7', 'D4:D7', 'E4:E7', 'F4:F7', 'G4:I4', 'G5:G7', 'H5:H7', 'I5:I7', 'J4:J7', 'K4:K7',
        ];

        foreach ($textAndCoordinates as $coordinate=>$text) {
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
        $results = $this->capabilityBuilding->getReport(
            $data['quarter_id'],
            $data['field_office_id'],
            'Personnel'
        );

        return ['rows' => isset($results['data']) ? array_values($results['data']) : []];
    }
}