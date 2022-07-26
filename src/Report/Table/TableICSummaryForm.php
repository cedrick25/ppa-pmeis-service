<?php

declare(strict_types=1);

namespace App\Report\Table;

use App\Entity\FieldOffices;
use App\Entity\Quarters;
use App\Repository\FieldOfficesRepository;
use App\Repository\QuartersRepository;
use App\Service\Volunteerism\Volunteer;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TableICSummaryForm implements Form
{
    private const TABLE_NAME = "TableICSummaryForm";

    public function __construct(
        private Volunteer              $service,
        private FieldOfficesRepository  $fieldOfficesRepository,
        private QuartersRepository     $quartersRepository,
        private ?FieldOffices           $fieldOffice = null,
        private ?Quarters              $quarters = null,
        private array                  $data = [],
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
        $quarterId = intval($data['quarter_id']);
        $fieldOfficeId = intval($data['field_office_id']);

        $this->fieldOffice = $this->fieldOfficesRepository->find($fieldOfficeId);
        $this->quarters = $this->quartersRepository->find($quarterId);
        $this->data = $this->service->getVpaMonitoring($quarterId, $fieldOfficeId);

        $spreadsheet = $this->footer();
        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");

        $filePath = $_ENV['XLSX_PATH_FILE'] . self::TABLE_NAME . "-" . time() . ".xlsx";
        $writer->save($filePath);

        return new BinaryFileResponse($filePath);
    }

    public function footer(): Spreadsheet
    {
        $spreadsheet = $this->body();

        return $spreadsheet;
    }

    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();

        $spreadsheet->getActiveSheet()->setCellValue('A8', $this->data['start_of_quarter_vpa']);
        $spreadsheet->getActiveSheet()->setCellValue('B8', $this->data['new_appointed'] + $this->data['reappointed']);
        $spreadsheet->getActiveSheet()->setCellValue('C8', $this->data['dropped']);
        $spreadsheet->getActiveSheet()->setCellValue('D8', $this->data['total_number_of_vpa_during_quarter']);
        $spreadsheet->getActiveSheet()->setCellValue('E8', $this->data['inactive']);
        $spreadsheet->getActiveSheet()->setCellValue('F8', $this->data['total_active_vpa']);
        $spreadsheet->getActiveSheet()->setCellValue('G8', $this->data['percentage_of_vpa_mobilized'] . '%');
        $spreadsheet->getActiveSheet()->setCellValue('H8', $this->data['no_of_vpa_supervising_clients']);
        $spreadsheet->getActiveSheet()->setCellValue('I8', $this->data['no_of_vpa_supervising_clients_percentage'] . '%');
        $spreadsheet->getActiveSheet()->setCellValue('J8', $this->data['no_of_vpa_acting_as_resource_individuals']);
        $spreadsheet->getActiveSheet()->setCellValue('K8', $this->data['no_of_vpa_acting_as_resource_individuals_percentage'] . '%');
        $spreadsheet->getActiveSheet()->setCellValue('L8', $this->data['vpa_acting_both_supervising_and_resource_individual']);
        $spreadsheet->getActiveSheet()->setCellValue('M8', $this->data['percentage_of_vpa_acting_both_supervising_and_resource_individual'] . '%');
        $spreadsheet->getActiveSheet()->setCellValue('N8', $this->data['total_number_of_clients_supervised']);

        return $spreadsheet;
    }

    public function header(): Spreadsheet
    {
        $spreadsheet = $this->prepare();
        $spreadsheet->getActiveSheet()->getStyle('A5:N8')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $spreadsheet;
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
            'A4' => 'I.C.  VOLUNTEERISM',
            'A5' => 'No. of VPA (start of the qtr.)  Note:  Total number of VPAs from the previous qtr.(Col. 4)',
            'B5' => 'New and Re-Appointed (Table I.C.2, Cols. 1 & 2)',
            'C5' => 'Dropped (expired appointment or any other cause) (Table I.C.2, Col. 4)',
            'D5' => 'TOTAL # of VPAs during the Qtr.',
            'E5' => 'No. of Inactive VPAs during the Qtr. (Table I.C.2, Col. 3)',
            'F5' => 'Total  Active VPAs during the Qtr.',
            'G5' => '% of VPAs Mobilized',
            'H5' => 'No. of VPAs Supervising Clients during the Qtr. (Head count) (Table I.C.3 , Col. 1)',
            'I5' => '% of VPAs Supervising Clients',
            'J5' => 'No. of VPAs acting as Resource Individual during the Qtr. (Head Count) (Table I.C.3)',
            'K5' => '% of VPAs Acting as Resource Individual',
            'L5' => 'Acting as both Supervising VPAs and Resource Individual (Head Count) (Table I.C.3)',
            'M5' => '% of VPAs Acting as Both',
            'N5' => 'Total number of clients supervised (Table I.C.3, Col.3)',
            'D6' => '(1+2)-3', 'F6' => '4-5', 'G6' => '6÷4', 'I6' => '8÷6', 'K6' => '10÷6', 'M6' => '12÷6',
            'A7' => '(1)', 'B7' => '(2)', 'C7' => '(3)', 'D7' => '(4)', 'E7' => '(5)', 'F7' => '(6)',
            'G7' => '(7)', 'H7' => '(8)', 'I7' => '(9)', 'J7' => '(10)', 'K7' => '(11)', 'L7' => '(12)', 'M7' => '(13)', 'N7' => '(14)',
        ];

        $wrapTextCoordinates = ["A5:N5"];

        $verticalAlignedCoordinates = ['A5:N8' => 'center'];

        $horizontalAlignedCoordinates = ['A5:N8' => 'center'];

        $adjustedColumnWidthCoordinates = [
            'A' => 30, 'B' => 30, 'C' => 30, 'D' => 30, 'E' => 30, 'F' => 30, 'G' => 30,
            'H' => 30, 'I' => 30, 'J' => 30, 'K' => 30, 'L' => 30, 'M' => 30, 'N' => 30
        ];

        foreach ($textAndCoordinates as $coordinate => $text) {
            $spreadsheet->getActiveSheet()->setCellValue($coordinate, $text);
        }

        foreach ($wrapTextCoordinates as $coordinate) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getAlignment()->setWrapText(true);
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
}