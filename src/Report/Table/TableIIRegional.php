<?php

namespace App\Report\Table;

use App\Entity\FieldOffices;
use App\Entity\Quarters;
use App\Repository\ClientSessionsRepository;
use App\Repository\ClientsRepository;
use App\Repository\RegionsRepository;
use App\Repository\FieldOfficesRepository;
use App\Repository\QuartersRepository;
use App\Repository\SessionsRepository;
use App\Service\Volunteerism\CapabilityBuilding;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TableIIRegional implements Form
{
    private const TABLE_NAME = "TableIIRegional";


    public function __construct(
        private CapabilityBuilding          $capabilityBuilding,
        private RegionsRepository           $regionsRepository,
        private FieldOfficesRepository      $fieldOfficesRepository,
        private QuartersRepository          $quartersRepository,
        private SessionsRepository          $sessionsRepository,
        private ClientSessionsRepository    $clientSessionsRepository,
        private ClientsRepository           $clientsRepository,
        private array                       $data = [],
        private array                       $sessionIds = [],
        private array                       $fieldOffices = [],
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

        if ($this->fieldOffices) {
            $ctr = 9;
            foreach ($this->fieldOffices as $k => $v) {
                $personnelIndex = ($ctr + $k);
                $volunteerIndex = $personnelIndex + count($this->fieldOffices) + 6;

                $spreadsheet->getActiveSheet()->setCellValue('A' . $personnelIndex, $v->getName());
                $spreadsheet->getActiveSheet()->setCellValue('B' . $personnelIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('C' . $personnelIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('D' . $personnelIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('E' . $personnelIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('F' . $personnelIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('G' . $personnelIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('H' . $personnelIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('I' . $personnelIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('J' . $personnelIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('K' . $personnelIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('L' . $personnelIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('M' . $personnelIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('N' . $personnelIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('O' . $personnelIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('P' . $personnelIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('Q' . $personnelIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('R' . $personnelIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('S' . $personnelIndex, '0');

                $spreadsheet->getActiveSheet()->setCellValue('A' . $volunteerIndex, $v->getName());
                $spreadsheet->getActiveSheet()->setCellValue('B' . $volunteerIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('C' . $volunteerIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('D' . $volunteerIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('E' . $volunteerIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('F' . $volunteerIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('G' . $volunteerIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('H' . $volunteerIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('I' . $volunteerIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('J' . $volunteerIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('K' . $volunteerIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('L' . $volunteerIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('M' . $volunteerIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('N' . $volunteerIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('O' . $volunteerIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('P' . $volunteerIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('R' . $volunteerIndex, '0');
                $spreadsheet->getActiveSheet()->setCellValue('S' . $volunteerIndex, '0');
            }

            $totalPersonnelIndex = $ctr + count($this->fieldOffices);
            $totalVolunteerIndex = $ctr + (count($this->fieldOffices) * 2) + 6;

            $spreadsheet->getActiveSheet()->setCellValue('A' . $totalPersonnelIndex, 'TOTAL');
            $spreadsheet->getActiveSheet()->setCellValue('B' . $totalPersonnelIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('C' . $totalPersonnelIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('D' . $totalPersonnelIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('E' . $totalPersonnelIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('F' . $totalPersonnelIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('G' . $totalPersonnelIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('H' . $totalPersonnelIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('I' . $totalPersonnelIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('J' . $totalPersonnelIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('K' . $totalPersonnelIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('L' . $totalPersonnelIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('M' . $totalPersonnelIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('N' . $totalPersonnelIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('O' . $totalPersonnelIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('P' . $totalPersonnelIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('Q' . $totalPersonnelIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('R' . $totalPersonnelIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('S' . $totalPersonnelIndex, '0');

            $spreadsheet->getActiveSheet()->setCellValue('A' . $totalVolunteerIndex, 'TOTAL');
            $spreadsheet->getActiveSheet()->setCellValue('B' . $totalVolunteerIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('C' . $totalVolunteerIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('D' . $totalVolunteerIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('E' . $totalVolunteerIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('F' . $totalVolunteerIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('G' . $totalVolunteerIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('H' . $totalVolunteerIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('I' . $totalVolunteerIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('J' . $totalVolunteerIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('K' . $totalVolunteerIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('L' . $totalVolunteerIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('M' . $totalVolunteerIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('N' . $totalVolunteerIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('O' . $totalVolunteerIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('P' . $totalVolunteerIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('R' . $totalVolunteerIndex, '0');
            $spreadsheet->getActiveSheet()->setCellValue('S' . $totalVolunteerIndex, '0');
        }

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
        $count = count($this->fieldOffices) + 3;
        $x = 8 + $count;

        $spreadsheet = new Spreadsheet();
        $textAndCoordinates = [
            'A1' => 'REGION ' . $this->region->getName(),
            'A2' => 'REGIONAL OFFICE IQPR CONSOLIDATION FORM',
            'A3' => $this->quarters->getName() . ' QTR, ' . $this->quarters->getYear(),
            'A4' => 'II.   CAPABILITY BUILDING (Tables II.A.1 & II.A.2)',

            // FOR PERSONNEL
            'A5' => 'FOR PERSONNEL',

            'A6' => 'FIELD OFFICES',
            'B6' => 'Number of',

            'B7' => 'TC',
            'B8' => 'Trng',
            'C8' => 'Pax',

            'D7' => 'RJ',
            'D8' => 'Trng',
            'E8' => 'Pax',
            
            'F7' => 'VPA',
            'F8' => 'Trng',
            'G8' => 'Pax',

            'H7' => 'GAD',
            'H8' => 'Trng',
            'I8' => 'Pax',

            'J7' => 'Others',
            'J8' => 'Trng',
            'K8' => 'Pax',

            'L7' => 'Conferences / Conventions',
            'L8' => 'No.',
            'M8' => 'Pax',

            'N7' => 'Staff / Committee Meetings',
            'N8' => 'No.',
            'O8' => 'Pax',

            'P7' => 'Nature of Training',
            'P8' => 'Managerial',
            'Q8' => 'Technical',
            'R8' => 'Foundation',

            'S7' => 'Training Hours',

            // FOR VOLUNTEER PROBATION ASSISTANT
            'A' . $x => 'FOR VOLUNTEER PROBATION ASSISTANT',

            'A' . ($x + 1) => 'FIELD OFFICES',
            'B' . ($x + 1) => 'Number of',

            'B' . ($x + 2) => 'TC',
            'B' . ($x + 3) => 'Trng',
            'C' . ($x + 3) => 'Pax',

            'D' . ($x + 2) => 'RJ',
            'D' . ($x + 3) => 'Trng',
            'E' . ($x + 3) => 'Pax',
            
            'F' . ($x + 2) => 'VPA',
            'F' . ($x + 3) => 'Trng',
            'G' . ($x + 3) => 'Pax',

            'H' . ($x + 2) => 'GAD',
            'H' . ($x + 3) => 'Trng',
            'I' . ($x + 3) => 'Pax',

            'J' . ($x + 2) => 'Others',
            'J' . ($x + 3) => 'Trng',
            'K' . ($x + 3) => 'Pax',

            'L' . ($x + 2) => 'Conferences / Conventions',
            'L' . ($x + 3) => 'No.',
            'M' . ($x + 3) => 'Pax',

            'N' . ($x + 2) => 'Meetings / Assemblies',
            'N' . ($x + 3) => 'No.',
            'O' . ($x + 3) => 'Pax',

            'P' . ($x + 2) => 'Trainings Conducted',
            'P' . ($x + 3) => 'In-house',
            'R' . ($x + 3) => 'Out-house',

            'S' . ($x + 2) => 'Training Hours',
        ];

        $mergesCoordinates = [
            'A1:S1',
            'A2:S2',
            'A3:S3', 
            'A4:S4', 

            'A5:S5', 
            'A6:A8', 
            'B6:S6', 
            'B7:C7', 
            'D7:E7', 
            'F7:G7', 
            'H7:I7', 
            'J7:K7', 
            'L7:M7', 
            'N7:O7', 
            'P7:R7', 
            'S7:S8', 

            'A'. $x . ':S'. $x, 
            'A'. ($x + 1) . ':A'. ($x + 3), 
            'B'. ($x + 1) . ':S'. ($x + 1), 
            'B'. ($x + 2) . ':C'. ($x + 2), 
            'D'. ($x + 2) . ':E'. ($x + 2),
            'F'. ($x + 2) . ':G'. ($x + 2),
            'H'. ($x + 2) . ':I'. ($x + 2),
            'J'. ($x + 2) . ':K'. ($x + 2),
            'L'. ($x + 2) . ':M'. ($x + 2),
            'N'. ($x + 2) . ':O'. ($x + 2),
            'P'. ($x + 2) . ':R'. ($x + 2),
            'P'. ($x + 3) . ':Q'. ($x + 3),
            'S'. ($x + 2) . ':S'. ($x + 3), 
            
        ];

        $boldCoordinates = [
            'A1:A8', 

            'A5:J5', 
            'A6:S8', 

            'A'. $x . ':J'. $x, 
            'A'. ($x + 1) . ':S'. ($x + 3), 
        ];

        $verticalAlignedCoordinates = [
            'A1:S1'  => 'center', 
            'A2:S2'  => 'center', 
            'A3:S3'  => 'center', 

            'A6:S8' => 'center',
            'A'. ($x + 1) . ':S'. ($x + 3) => 'center',
        ];

        $horizontalAlignedCoordinates = [
            'A1:S1'  => 'center', 
            'A2:S2'  => 'center', 
            'A3:S3'  => 'center', 

            'A6:S8' => 'center',
            'A'. ($x + 1) . ':S'. ($x + 3) => 'center',
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
        $this->region   = $this->regionsRepository->find($data['region_id']);
        $this->quarters = $this->quartersRepository->find($data['quarter_id']);

        $this->fieldOffices = $this->fieldOfficesRepository->findBy(['regionId' => $data['region_id']]);

        // $results = $this->capabilityBuilding->getReport(
        //     $data['quarter_id'],
        //     $data['field_office_id'],
        //     'Personnel'
        // );

        // return ['rows' => array_values($results['data']) ?? []];
        return [];
    }

}