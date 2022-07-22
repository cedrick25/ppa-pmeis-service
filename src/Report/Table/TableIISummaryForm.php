<?php

namespace App\Report\Table;

use App\Entity\FieldOffices;
use App\Entity\Quarters;
use App\Repository\ClientSessionsRepository;
use App\Repository\ClientsRepository;
use App\Repository\FieldOfficesRepository;
use App\Repository\QuartersRepository;
use App\Repository\SessionsRepository;
use App\Service\Volunteerism\CapabilityBuilding;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TableIISummaryForm implements Form
{
    private const TABLE_NAME = "TableIISummaryForm";


    public function __construct(
        private CapabilityBuilding          $capabilityBuilding,
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
            "A7:G12"
        ];

        foreach ($thinBorders as $coordinate) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }
        return $spreadsheet;
    }

    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();

        $count = 0;
        // $count = $this->data ? count($this->data['rows']) : 0;
        // foreach ($this->data['rows'] as $v) {
        // }

        $spreadsheet->getActiveSheet()->setCellValue('B9', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('B10', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('B11', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('B12', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('B13', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('B14', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('B15', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('B16', 'No Data');

        $spreadsheet->getActiveSheet()->setCellValue('C9', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('C10', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('C11', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('C12', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('C13', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('C14', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('C15', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('C16', 'No Data');

        $spreadsheet->getActiveSheet()->setCellValue('D9', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('D10', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('D11', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('D12', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('D13', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('D14', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('D15', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('D16', 'No Data');

        $spreadsheet->getActiveSheet()->setCellValue('E9', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('E10', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('E11', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('E12', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('E13', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('E14', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('E15', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('E16', 'No Data');

        $spreadsheet->getActiveSheet()->setCellValue('F9', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('F10', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('F11', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('F12', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('F13', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('F14', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('F15', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('F16', 'No Data');

        $spreadsheet->getActiveSheet()->setCellValue('G9', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('G10', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('G11', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('G12', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('G13', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('G14', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('G15', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('G16', 'No Data');

        $spreadsheet->getActiveSheet()->setCellValue('H9', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('H10', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('H11', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('H12', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('H13', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('H14', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('H15', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('H16', 'No Data');

        $spreadsheet->getActiveSheet()->setCellValue('I9', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('I10', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('I11', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('I12', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('I13', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('I14', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('I15', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('I16', 'No Data');

        $spreadsheet->getActiveSheet()->setCellValue('J9', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('J10', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('J11', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('J12', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('J13', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('J14', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('J15', 'No Data');
        $spreadsheet->getActiveSheet()->setCellValue('J16', 'No Data');

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
            'A4' => 'II.   CAPABILITY BUILDING (Tables II.A.1 & II.A.2)',
            'A5' => 'TITLE',
            'B5' => 'Table II.A.1',
            'G5' => 'Table II.A.2',
            'B6' => 'FOR PERSONNEL',
            'G6' => 'FOR VOLUNTEER PROBATION ASSISTANTS',

            'B7' => 'Number of Participants',
            'C7' => 'Nature of Training',
            'C8' => 'Managerial/Supervisory',
            'D8' => 'Technical',
            'E8' => 'Foundation',
            'F7' => 'Number of Training Hours',

            'G7' => 'Number of Participants',
            'H7' => 'Trainings Conducted',
            'H8' => 'In-house',
            'I8' => 'Out-house',
            'J7' => 'Number of Training Hours',

            'A9' => 'A. TC',
            'A10' => 'B. RJ',
            'A11' => 'C. VPAs',
            'A12' => 'D. GAD',
            'A13' => 'E. Other Training Courses, Seminars, For a, Symposia',
            'A14' => 'F. Conferences / Conventions/ Congress Attended',
            'A15' => 'G. Meetings (for Personnel: to include staff meetings with Professional Development and Committee meetings)',
            'A16' => 'T O T A L (Headcount or service count as the case maybe)',
        ];

        $mergesCoordinates = [
            'A1:J1',
            'A2:J2',
            'A3:J3', 
            'A4:J4', 
            'A5:A8', 
            'B5:F5', 
            'G5:J5', 

            'B6:E6',
            'G6:J6',

            'B7:B8', 
            'C7:E7', 
            'F7:F8', 
            'G7:G8', 
            'H7:I7', 
            'J7:J8', 
        ];

        $boldCoordinates = [
            'A1:A8', 
            'A5:J5', 
            'A6:J6', 
            'A16:J16',
        ];

        $verticalAlignedCoordinates = [
            'A1:G1'  => 'center', 
            'A2:G2'  => 'center', 
            'A3:G3'  => 'center', 
            'A5:J8' => 'center',
            'A16' => 'center',
        ];

        $horizontalAlignedCoordinates = [
            'A1:G1'  => 'center', 
            'A2:G2'  => 'center', 
            'A3:G3'  => 'center', 
            'A5:J8' => 'center',
            'A16' => 'center',
        ];

        $adjustedColumnWidthCoordinates = [
            'A' => 35, 'G' => 15
        ];

        $wrappedTextCoordinates = [
            'B7:B8', 
            'C8',
            'D8',
            'E8',
            'F7:F8', 
            'G7:G8', 
            'J7:J8', 
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

        // $results = $this->capabilityBuilding->getReport(
        //     $data['quarter_id'],
        //     $data['field_office_id'],
        //     'Personnel'
        // );

        // return ['rows' => array_values($results['data']) ?? []];
        return [];
    }

}