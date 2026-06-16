<?php

namespace App\Report\Table;

use App\Service\Volunteerism\ResourceMobilization;
use App\Entity\FieldOffices;
use App\Entity\Quarters;
use App\Repository\ClientSessionsRepository;
use App\Repository\ClientsRepository;
use App\Repository\FieldOfficesRepository;
use App\Repository\QuartersRepository;
use App\Repository\SessionsRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TableIVSummaryForm implements Form
{
    private const TABLE_NAME = "TableIVSummaryForm";


    public function __construct(
        private ResourceMobilization        $service,
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
            "A7:E33"
        ];

        foreach ($thinBorders as $coordinate) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }
        return $spreadsheet;
    }

    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();

        $result = $this->data;

        // print_r($result['rows']);

        $trainingTypes = ['TC','RJ','VPA','PWDSC','GAD','OTHERS'];
        $resources     = ['cash', 'materials', 'technicalAssistance', 'donors'];
        $sourceTypes   = ['GO', 'NGO', 'IND'];

        $resMob = [];
        $grandTotal = [];

        foreach ($trainingTypes as $training) {
            foreach ($resources as $resource) {
                foreach ($sourceTypes as $sourceType) {
                    $resMob[$training][$resource][$sourceType] = 0;
                }

                $grandTotal[$resource] = 0;
            }
        }
        
        if ($result['rows']) {
            // Training Type
            foreach ($result['rows'] as $k1 => $v1) {
                // Trainings
                foreach ($v1 as $v2) {
                    
                    // Resource Types
                    $resourceTypes = $resources;
                    unset($resourceTypes[3]);
                    foreach ($resourceTypes as $resource) {
                        // Resources
                        foreach($v2[$resource] as $x) {
                            $resMob[$k1][$resource][$x['source_type']['value']] += $x[$resource == 'cash' ? 'amount' : 'estimated_amount'];
                            $resMob[$k1]['donors'][$x['source_type']['value']] += 1;

                            $grandTotal[$resource] += $x[$resource == 'cash' ? 'amount' : 'estimated_amount'];
                            $grandTotal['donors'] += 1;
                        }
                    }
                }
            }
        }

        $sourceCellNumber = 10;
        foreach ($trainingTypes as $training) {
            foreach ($resources as $idx => $resource) {
                $cellLetters = ['B', 'C', 'D', 'E'];
                $currentCellNumber = $sourceCellNumber;
                foreach ($sourceTypes as $sourceType) {
                    $spreadsheet->getActiveSheet()->setCellValue($cellLetters[$idx] . $currentCellNumber, $resMob[$training][$resource][$sourceType]);

                    $currentCellNumber++;
                }
            }
            $sourceCellNumber += 4;
        }

        $spreadsheet->getActiveSheet()->setCellValue('B33', $grandTotal['cash']);
        $spreadsheet->getActiveSheet()->setCellValue('C33', $grandTotal['materials']);
        $spreadsheet->getActiveSheet()->setCellValue('D33', $grandTotal['technicalAssistance']);
        $spreadsheet->getActiveSheet()->setCellValue('E33', $grandTotal['donors']);

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
            'A4' => 'IV. RESOURCE MOBILIZATION',
            'A5' => 'Table IV. Resources Secured/ Utilized',

            'A7' => 'Resources Provided/ Secured (Indicate Amount/ Estimated Amount)',

            'A9' => '1. Therapeutic Community',
            'A10' => 'GO',
            'A11' => 'NGO',
            'A12' => 'Individual',

            'A13' => '2. Restorative Justice',
            'A14' => 'GO',
            'A15' => 'NGO',
            'A16' => 'Individual',

            'A17' => '3. Volunteerism',
            'A18' => 'GO',
            'A19' => 'NGO',
            'A20' => 'Individual',

            'A21' => '4. Gender and Development',
            'A22' => 'GO',
            'A23' => 'NGO',
            'A24' => 'Individual',

            'A25' => '5. Persons with Disability/ and Senior Citizens ',
            'A26' => 'GO',
            'A27' => 'NGO',
            'A28' => 'Individual',

            'A29' => '6. Others',
            'A30' => 'GO',
            'A31' => 'NGO',
            'A32' => 'Individual',

            'A33' => 'GRAND TOTAL',

            'B8' => 'CASH (Amount)',
            'C8' => 'SUPPLIES AND MATERIALS (Est. Amount)',
            'D8' => 'TECHNICAL (Est. Amount)',

            'E7' => 'NUMBER OF DONORS/ LINKAGES',
        ];

        $mergesCoordinates = [
            'A1:E1',
            'A2:E2',
            'A3:E3',
            'A4:E4',
            'A5:E5',

            'A7:D7',
            'A9:E9',
            'A13:E13',
            'A17:E17',
            'A21:E21',
            'A25:E25',
            'A29:E29',

            'E7:E8',
        ];

        $boldCoordinates = [
            'A1:E8',
        ];

        $verticalAlignedCoordinates = [
            'A1:C1' => 'center',
            'A2:C2' => 'center',
            'A3:C3' => 'center',
            'A7:A8' => 'center',
            'B7:E7' => 'center',
            'B10:E8' => 'center',
        ];

        $horizontalAlignedCoordinates = [
            'A1:C1' => 'center',
            'A2:C2' => 'center',
            'A3:C3' => 'center',
            'A7:A8' => 'center',
            'B7:E7' => 'center',
            'B8:E8' => 'center',
            'A10:A12' => 'center',
            'A14:A16' => 'center',
            'A18:A20' => 'center',
            'A22:A24' => 'center',
            'A26:A28' => 'center',
            'A30:A33' => 'center',
            'B10:E33' => 'center',
        ];

        $adjustedColumnWidthCoordinates = [
            'A' => 30, 
            'B' => 10,
            'C' => 13,
            'D' => 15,
            'E' => 35,
        ];

        $wrappedTextCoordinates = [
            'A1:C10'
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

        $result = $this->service->getReport(
            $data['quarter_id'],
            $data['field_office_id'],
        );

        return ['rows' => $result['data'] ?? []];
    }
}