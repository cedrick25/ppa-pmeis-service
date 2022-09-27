<?php

declare(strict_types=1);

namespace App\Report\Table;

use App\Common\AppDateHelper;
use App\Enum\SystemSettingNames;
use App\Service\Volunteerism\Volunteer;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VPAIC1 implements Form
{
    private const TABLE_NAME = "VPAIC1";
    
    public function __construct(
        private AppDateHelper   $appDateHelper,
        private Volunteer       $service,
        private int             $lastFilledOutCellY = 6,
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
        $spreadsheet->getActiveSheet()->getStyle('B' . $this->lastFilledOutCellY)->getFont()->setBold(true);
        $spreadsheet->getActiveSheet()->setCellValue(
            'B' . $this->lastFilledOutCellY,
            'Recruit:  Has passed screening by the CPPO,  and recommended to the Regional Office/Regional VPA Coordinator');

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->getStyle('B' . $this->lastFilledOutCellY)->getFont()->setBold(true);
        $spreadsheet->getActiveSheet()->setCellValue(
            'B' . $this->lastFilledOutCellY,
            'Date Recruited:  Date complete requirements submitted to the Regional VPA Coordinator / Regional Office');

        return $spreadsheet;
    }

    /**
     * @throws \Exception
     */
    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();

        $rowNumber = 1;
        /** @var \App\Entity\Volunteer $row */
        foreach ($this->data['rows'] as $row) {
            $this->lastFilledOutCellY++;
            $middleInitial = $row->getMiddleName() != null ? substr($row->getMiddleName(), 0, 1) . '.' : '';
            $nameOfRecruit = $row->getLastName() . ', ' . $row->getFirstName() . ' ' . $middleInitial;
            $dateOfBirth = $this->appDateHelper->convertStringToImmutableDate($row->getDateOfBirth());

            $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, $rowNumber);
            $spreadsheet->getActiveSheet()->setCellValue("B" . $this->lastFilledOutCellY, $nameOfRecruit);
            if ($row->getGender() === 'F') {
                $spreadsheet->getActiveSheet()->setCellValue('C' . $this->lastFilledOutCellY, '∕');
            } else {
                $spreadsheet->getActiveSheet()->setCellValue('D' . $this->lastFilledOutCellY, '∕');
            }
            $spreadsheet->getActiveSheet()->setCellValue("E" . $this->lastFilledOutCellY, $dateOfBirth->format('d-M-y'));
            $spreadsheet->getActiveSheet()->setCellValue("F" . $this->lastFilledOutCellY, $row->getDateRecruited()->format('d-M-y'));
            $spreadsheet->getActiveSheet()->setCellValue("G" . $this->lastFilledOutCellY, $row->getRecruitingOfficer());
            $spreadsheet->getActiveSheet()->getStyle("A" . $this->lastFilledOutCellY . ":G" . $this->lastFilledOutCellY)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            $rowNumber++;
        }

        $spreadsheet->getActiveSheet()->getStyle('A7:G' . $this->lastFilledOutCellY)->getAlignment()->setHorizontal('center');

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
            'A1' => 'C.  VOLUNTEERISM', 'G1' => $this->data[SystemSettingNames::GENERATED_REPORTS_CODE], 'A2' => 'Table I.C.1 – RECRUITMENT', 'C3' => 'Sex', 'E3' => 'Date of', 'A4' => 'No.',
            'B4' => 'Name of Recruit', 'C4' => '(3)', 'E4' => 'Birth', 'F4' => 'Date Recruited', 'G4' => 'Recruiting Officer', 'A5' => '(1)', 'B5' => '(2)',
            'C5' => 'F', 'D5' => 'M', 'E5' => 'mm/dd/yyyy', 'F5' => '(5)', 'G5' => '(6)', 'E6' => '(4)'
        ];
        $mergesCoordinates = ['C4:D4', 'C5:C6', 'D5:D6'];
        $boldCoordinates = ['A1:G2', 'C4', 'A5', 'B5', 'E6', 'F5', 'G5'];
        $verticalAlignedCoordinates = ['A3:G6' => 'center'];
        $horizontalAlignedCoordinates = ['A3:G6' => 'center'];
        $adjustedColumnWidthCoordinates = ['A' => 4, 'B' => 40, 'C' => 4, 'D' => 4, 'E' => 25, 'F' => 25, 'G' => 25];
        $outlineBorderThinCoordinates = ['A3:A6', 'B3:B6', 'C3:D4', 'C5:C6', 'D5:D6', 'E3:E6', 'F3:F6', 'G3:G6'];


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
        $result = $this->service->getByFieldOfficeAndMonthRange(
            $data['field_office_id'],
            $data['quarter_id']
        );

        return ['rows' => $result['data'] ?? []];
    }
}