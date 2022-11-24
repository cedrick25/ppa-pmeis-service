<?php

declare(strict_types=1);

namespace App\Report\Table;

use App\Entity\FieldOffices;
use App\Entity\Quarters;
use App\Enum\SystemSettingNames;
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
    ) {}

    public function supports(string $tableName): bool
    {
        return self::TABLE_NAME === $tableName;
    }

    /**
     * @param array $data
     * @return BinaryFileResponse
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function generate(array $data): BinaryFileResponse
    {
        $quarterId = intval($data['quarter_id']);
        $fieldOfficeId = intval($data['field_office_id']);

        $this->fieldOffice = $this->fieldOfficesRepository->find($fieldOfficeId);
        $this->quarters = $this->quartersRepository->find($quarterId);
        $this->data = $this->service->getVpaMonitoring($quarterId, $fieldOfficeId);
        $this->data[SystemSettingNames::GENERATED_REPORTS_CODE] = $data[SystemSettingNames::GENERATED_REPORTS_CODE];

        $spreadsheet = $this->footer();
        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");

        $filePath = $_ENV['XLSX_PATH_FILE'] . self::TABLE_NAME . "-" . time() . ".xlsx";
        $writer->save($filePath);

        return new BinaryFileResponse($filePath);
    }

    public function footer(): Spreadsheet
    {
        return $this->body();
    }

    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();

        $spreadsheet->getActiveSheet()->setCellValue('A8', $this->data['start_of_quarter_vpa']);
        $spreadsheet->getActiveSheet()->setCellValue('B8', $this->data['new_appointed']);
        $spreadsheet->getActiveSheet()->setCellValue('C8', $this->data['dropped']);
        $spreadsheet->getActiveSheet()->setCellValue('D8', $this->data['total_number_of_vpa_during_quarter']);
        $spreadsheet->getActiveSheet()->setCellValue('E8', $this->data['inactive']);
        $spreadsheet->getActiveSheet()->setCellValue('F8', $this->data['total_active_vpa']);
        $spreadsheet->getActiveSheet()->setCellValue('G8', $this->data['no_of_vpa_supervising_clients']);
        $spreadsheet->getActiveSheet()->setCellValue('H8', $this->data['total_number_of_clients_supervised']);
        $spreadsheet->getActiveSheet()->setCellValue('I8', $this->data['no_of_vpa_acting_as_resource_individuals']);
        $spreadsheet->getActiveSheet()->setCellValue(
            'J8',
            $this->data['vpa_acting_both_supervising_and_resource_individual']
        );
        $spreadsheet->getActiveSheet()->setCellValue('K8', $this->data['total_number_of_vpa_mobilize']);
        $spreadsheet->getActiveSheet()->setCellValue('L8', $this->data['percent_of_vpa_mobilized'] . '%');
        $spreadsheet->getActiveSheet()->setCellValue('M8', $this->data['no_of_services_rendered_during_quarter']);
        $spreadsheet->getActiveSheet()->setCellValue('N8', $this->data['no_of_services_rendered_by_vpa']);

        return $spreadsheet;
    }

    public function header(): Spreadsheet
    {
        $spreadsheet = $this->prepare();
        $spreadsheet->getActiveSheet()->getStyle('A5:P8')
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $spreadsheet;
    }

    /**
     */
    private function prepare(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $textAndCoordinates = [
            'G1' => 'Field Office IQPR FORM' . $this->data[SystemSettingNames::GENERATED_REPORTS_CODE],
            'A1' => 'FIELD OFFICE ' . $this->fieldOffice->getName(),
            'A2' => 'IQPR SUMMARY FORM',
            'A3' => $this->quarters->getName() . ' QTR, ' . $this->quarters->getYear(),
            'A4' => 'I.C.  VOLUNTEERISM',
            'A5' => 'No. of VPAs (start of the quarter)',
            'B5' => 'Appointed',
            'C5' => 'Dropped (expired appointment or any other cause)',
            'D5' => 'TOTAL NUMBER OF VPAs  DURING THE QUARTER',
            'E5' => 'No. of INACTIVE VPAs during the QTR',
            'F5' => 'TOTAL  ACTIVE VPAs DURING THE QUARTER',
            'G5' => 'No. of VPAs supervising clients during the quarter (Head count)',
            'H5' => 'Total Number of clients Supervised',
            'I5' => 'No. of VPAs acting as resource individuals during the quarter (Head count)',
            'J5' => 'Acting as Both (Supervising VPAs and Resource Individual/ Head count)',
            'K5' => 'Total Number of VPA mobilized (per head count)',
            'L5' => 'Percent of VPA mobilized (per head count)',
            'M5' => 'No. of services rendered by VPAs during the quarter',
            'N5' => 'No. of services rendered By a VPA ',
            'A6' => '(1)', 'B6' => '(2)', 'C6' => '(3)', 'D6' =>'(4)', 'E6' =>'(5)', 'F6' =>'(6)', 'G6' =>'(7)',
            'H6' => '(8)', 'I6' => '(9)', 'J6' => '(10)', 'K6' => '(11)', 'L6' => '(12)', 'M6' => '(13)',
            'N6' => '(14)', 'E7' => '(1+2)-3', 'G7' => '4-5', 'H7' => '6÷4', 'L7' => '7+9+10=11', 'M7' => '11/6=12',
            'N7' => '13/6=14'
        ];

        $wrapTextCoordinates = ["A5:P5"];

        $verticalAlignedCoordinates = ['A5:P8' => 'center'];

        $horizontalAlignedCoordinates = ['A5:P8' => 'center'];

        $adjustedColumnWidthCoordinates = [
            'A' => 30, 'B' => 30, 'C' => 30, 'D' => 30, 'E' => 30, 'F' => 30, 'G' => 30,
            'H' => 30, 'I' => 30, 'J' => 30, 'K' => 30, 'L' => 30, 'M' => 30, 'N' => 30,
            'O' => 30, 'P' => 30
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