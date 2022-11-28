<?php

declare(strict_types=1);

namespace App\Report\Table;

use App\Common\AppDateHelper;
use App\Service\Volunteerism\Volunteer;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VPADatabase implements Form
{
    private const TABLE_NAME = "VPADatabase";

    public function __construct(
        private AppDateHelper   $appDateHelper,
        private Volunteer       $service,
        private int             $lastFilledOutCellY = 5,
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
     * @throws \Exception
     */
    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();
        $volunteers = $this->data['volunteers'] ?? [];

        foreach ($volunteers as $volunteer) {
            $this->lastFilledOutCellY++;

            $middleInitial = $volunteer['middle_name'] != null ? substr($volunteer['middle_name'], 0, 1) . '.' : '';
            $fullName = $volunteer['last_name'] . ', ' . $volunteer['last_name'] . ' ' . $middleInitial;
            $dateAppointed = $this->appDateHelper->convertStringToImmutableDate($volunteer['date_appointed']);
            $dateOfBirth = $this->appDateHelper->convertStringToImmutableDate($volunteer['date_of_birth']);

            $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, $fullName);
            // TODO: replaced with volunteer_id -> id_no
            $spreadsheet->getActiveSheet()->setCellValue("B" . $this->lastFilledOutCellY, $volunteer['volunteer_id']);
            $spreadsheet->getActiveSheet()->setCellValue("C" . $this->lastFilledOutCellY, $dateAppointed->format('y-M-d'));
            $spreadsheet->getActiveSheet()->setCellValue("D" . $this->lastFilledOutCellY, $volunteer['present_address']);
            $spreadsheet->getActiveSheet()->setCellValue("E" . $this->lastFilledOutCellY, $volunteer['height']);
            $spreadsheet->getActiveSheet()->setCellValue("F" . $this->lastFilledOutCellY, $volunteer['weight']);
            $spreadsheet->getActiveSheet()->setCellValue("G" . $this->lastFilledOutCellY, $volunteer['gender']);
            $spreadsheet->getActiveSheet()->setCellValue("H" . $this->lastFilledOutCellY, $dateOfBirth->format('y-M-d'));
            $spreadsheet->getActiveSheet()->setCellValue("I" . $this->lastFilledOutCellY, $volunteer['age']);
            $spreadsheet->getActiveSheet()->setCellValue("J" . $this->lastFilledOutCellY, $volunteer['civil_status']);
            $spreadsheet->getActiveSheet()->setCellValue("K" . $this->lastFilledOutCellY, $volunteer['religion']);
            $spreadsheet->getActiveSheet()->setCellValue("L" . $this->lastFilledOutCellY, $volunteer['education_attainment']);
            $spreadsheet->getActiveSheet()->setCellValue("M" . $this->lastFilledOutCellY, $volunteer['occupation']);
        }

        $spreadsheet->getActiveSheet()->getStyle('A5:M' . $this->lastFilledOutCellY)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('A5:M' . $this->lastFilledOutCellY)->getAlignment()->setHorizontal('center');
        $spreadsheet->getActiveSheet()->getStyle('A5:M' . $this->lastFilledOutCellY)->getAlignment()->setVertical('center');

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
            'L1' => 'PPA-CSD-FR-009-01', 'A2' => 'VPA DATABASE', 'A3' => 'REGION: ' . $this->data['header']['region'] ?? 'NCR',
            'A5' => 'NAME', 'B5' => 'ID Number', 'C5' => 'Date of Appointment', 'D5' => 'Address', 'E5' => 'Ht.',
            'F5' => 'Wt.', 'G5' => 'Gender', 'H5' => 'Date of Birth', 'I5' => 'Age', 'J5' => 'Civil Status',
            'K5' => 'Religion', 'L5' => 'Education', 'M5' => 'Occupation'
        ];
        $mergesCoordinates = ['A2:M2'];
        $boldCoordinates = ['A1:M5'];
        $verticalAlignedCoordinates = ['A2:M2' => 'center', 'A5:M5' => 'center'];
        $horizontalAlignedCoordinates = ['A2:M2' => 'center', 'A5:M5' => 'center'];
        $adjustedColumnWidthCoordinates = [
            'A' => 35, 'B' => 15, 'C' => 22, 'D' => 35, 'E' => 5, 'F' => 5, 'G' => 12, 'H' => 23, 'I' => 5, 'J' => 25,
            'K' => 25, 'L' => 25, 'M' => 25
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

        $spreadsheet->getActiveSheet()->getStyle('A5:M5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $spreadsheet;
    }

    private function getData(array $data): array
    {
        $result = $this->service->getVPADatabase($data['region_id']);

        return $result['data'] ?? [];
    }
}