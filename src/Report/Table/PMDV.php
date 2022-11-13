<?php

declare(strict_types=1);

namespace App\Report\Table;

use App\Enum\SystemSettingNames;
use App\Service\Volunteerism\ProgramMaterialsDevelopment;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PMDV implements Form
{
    private const TABLE_NAME = "PMDV";
    
    public function __construct(
        private ProgramMaterialsDevelopment $programMaterialsDevelopmentService,
        private int                         $lastFilledOutCellY = 8,
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

        return $spreadsheet;
    }

    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();

        $program = [
            'TC' => 'D', 'RJ' => 'E', 'VPA' => 'F', 'GAD' => 'G', 'OTHERS' => 'H'
        ];

        foreach ($this->data['rows'] as $row) {
            $this->lastFilledOutCellY++;

            $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, $row['particulars']);
            $spreadsheet->getActiveSheet()->setCellValue("B" . $this->lastFilledOutCellY, $row['date']);
            $spreadsheet->getActiveSheet()->setCellValue($program[$row['program']] . $this->lastFilledOutCellY, '/');
            $spreadsheet->getActiveSheet()->setCellValue("I" . $this->lastFilledOutCellY, $row['remarks']);

            foreach ($row['personsResponsible'] as $personResponsible) {
                if (strlen($personResponsible['othersName']) > 0) {
                    $spreadsheet->getActiveSheet()->setCellValue(
                        "C" . $this->lastFilledOutCellY,
                        $personResponsible['othersName']
                    );

                    continue;
                }

                $spreadsheet->getActiveSheet()->setCellValue(
                    "C" . $this->lastFilledOutCellY,
                    $personResponsible['type']['value']
                    . ' - ' . $personResponsible['id']['label']
                );

                $this->lastFilledOutCellY++;
            }
        }

        $spreadsheet->getActiveSheet()->getStyle('A9:i' . $this->lastFilledOutCellY)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

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
            'a1' => 'V.  PROGRAM AND MATERIALS DEVELOPMENT',
            'j1' => $this->data[SystemSettingNames::GENERATED_REPORTS_CODE],
            'a4' => 'Table V.  MATERIALS/ SESSION PLANS DEVELOPED AND USED FOR AGENCY PROGRAMS',
            'a6' => 'Particulars',
            'a7' => '(1)',
            'b6' => 'Date',
            'b7' => '(2)',
            'c6' => 'Person Responsible ',
            'c7' => '(Personnel/ VPA)',
            'c8' => '(3)',
            'd6' => 'Utilized for:  (4)',
            'd7' => 'TC',
            'e7' => 'RJ',
            'f7' => 'VPA',
            'g7' => 'GAD',
            'h7' => 'OTHERS',

            'i6' => 'Remarks',
            'i7' => '(5)',

        ];
        $mergesCoordinates = [
            'D6:h6', 'D7:D8', 'E7:E8', 'F7:F8', 'G7:G8', 'H7:H8',
        ];
        $boldCoordinates = ['a1','a4',];
        $verticalAlignedCoordinates = ['B3:T30' => 'center', 'A3:A8' => 'center'];
        $horizontalAlignedCoordinates = ['B3:T30' => 'center', 'A3:A8' => 'center'];
        $adjustedColumnWidthCoordinates = [
            'A' => 40, 'B' => 20, 'C' => 20, 'D' => 15, 'E' => 5, 'F' => 5, 'G' => 5, 'H' => 30, 'I' => 30, 'J' => 30,
            'K' => 5, 'L' => 5, 'M' => 5, 'N' => 30, 'O' => 20, 'P' => 20, 'Q' => 5, 'R' => 5, 'S' => 5, 'T' => 20,
        ];
        $outlineBorderThinCoordinates = [
            'A6:A8', 'B6:B8', 'C6:C8', 'D6:G6', 'D7:D8', 'E7:E8', 'F7:F8', 'G7:G8', 'H7:H8', 'I6:I8'
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
        $results = $this->programMaterialsDevelopmentService
            ->getIdSupportReport($data['quarter_id'], $data['field_office_id']);

        return ['rows' => isset($results['data']) ? array_values($results['data']) : []];
    }
}