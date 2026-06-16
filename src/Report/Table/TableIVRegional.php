<?php

namespace App\Report\Table;

use App\Entity\Quarters;
use App\Entity\Regions;
use App\Repository\ClientSessionsRepository;
use App\Repository\ClientsRepository;
use App\Repository\RegionsRepository;
use App\Repository\FieldOfficesRepository;
use App\Repository\QuartersRepository;
use App\Repository\SessionsRepository;
use App\Service\Volunteerism\ResourceMobilization;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TableIVRegional implements Form
{
    private const TABLE_NAME = "TableIVRegional";

    public function __construct(
        private ResourceMobilization        $service,
        private RegionsRepository           $regionsRepository,
        private FieldOfficesRepository      $fieldOfficesRepository,
        private QuartersRepository          $quartersRepository,
        private SessionsRepository          $sessionsRepository,
        private ClientSessionsRepository    $clientSessionsRepository,
        private ClientsRepository           $clientsRepository,
        private string                      $type = '',
        private array                       $data = [],
        private array                       $sessionIds = [],
        private array                       $fieldOffices = [],
        private ?Regions                    $region = null,
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
            "A6:K" . (9 + count($this->fieldOffices))
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

        if ($this->fieldOffices) {
            $ctr = 9;

            $resources     = ['cash', 'materials', 'technicalAssistance'];

            $total = [
                'cash' => [
                    'GO' => 0,
                    'NGO' => 0,
                    'IND' => 0,
                ],
                'materials' => [
                    'GO' => 0,
                    'NGO' => 0,
                    'IND' => 0,
                ],
                'technicalAssistance' => [
                    'GO' => 0,
                    'NGO' => 0,
                    'IND' => 0,
                ],
                'donors' => 0,
            ];

            foreach ($this->fieldOffices as $k => $v) {
                $index = ($ctr + $k);

                $resMob = [
                    'cash' => [
                        'GO' => 0,
                        'NGO' => 0,
                        'IND' => 0,
                    ],
                    'materials' => [
                        'GO' => 0,
                        'NGO' => 0,
                        'IND' => 0,
                    ],
                    'technicalAssistance' => [
                        'GO' => 0,
                        'NGO' => 0,
                        'IND' => 0,
                    ],
                    'donors' => 0,
                ];

                if ($result['rows']) {
                    if (isset($result['rows'][$v->getFieldOfficeId()][$this->type])) {
                        foreach ($result['rows'][$v->getFieldOfficeId()][$this->type] as $v1) {
                            foreach ($resources as $resource) {
                                // Resources
                                foreach($v1[$resource] as $x) {
                                    $resMob[$resource][$x['source_type']['value']] += $x[$resource == 'cash' ? 'amount' : 'estimated_amount'];
                                    $resMob['donors'] += 1;
        
                                    $total[$resource][$x['source_type']['value']] += $x[$resource == 'cash' ? 'amount' : 'estimated_amount'];
                                    $total['donors'] += 1;
                                }
                            }
                        }
                    }
                }

                $spreadsheet->getActiveSheet()->setCellValue('A' . $index, $v->getName());
                $spreadsheet->getActiveSheet()->setCellValue('B' . $index, $resMob['cash']['GO']);
                $spreadsheet->getActiveSheet()->setCellValue('C' . $index, $resMob['cash']['NGO']);
                $spreadsheet->getActiveSheet()->setCellValue('D' . $index, $resMob['cash']['IND']);
                $spreadsheet->getActiveSheet()->setCellValue('E' . $index, $resMob['materials']['GO']);
                $spreadsheet->getActiveSheet()->setCellValue('F' . $index, $resMob['materials']['NGO']);
                $spreadsheet->getActiveSheet()->setCellValue('G' . $index, $resMob['materials']['IND']);
                $spreadsheet->getActiveSheet()->setCellValue('H' . $index, $resMob['technicalAssistance']['GO']);
                $spreadsheet->getActiveSheet()->setCellValue('I' . $index, $resMob['technicalAssistance']['NGO']);
                $spreadsheet->getActiveSheet()->setCellValue('J' . $index, $resMob['technicalAssistance']['IND']);
                $spreadsheet->getActiveSheet()->setCellValue('K' . $index, $resMob['donors']);
            }

            $totalIndex = $ctr + count($this->fieldOffices);

            $spreadsheet->getActiveSheet()->setCellValue('A' . $totalIndex, 'TOTAL');
            $spreadsheet->getActiveSheet()->setCellValue('B' . $totalIndex, $total['cash']['GO']);
            $spreadsheet->getActiveSheet()->setCellValue('C' . $totalIndex, $total['cash']['NGO']);
            $spreadsheet->getActiveSheet()->setCellValue('D' . $totalIndex, $total['cash']['IND']);
            $spreadsheet->getActiveSheet()->setCellValue('E' . $totalIndex, $total['materials']['GO']);
            $spreadsheet->getActiveSheet()->setCellValue('F' . $totalIndex, $total['materials']['NGO']);
            $spreadsheet->getActiveSheet()->setCellValue('G' . $totalIndex, $total['materials']['IND']);
            $spreadsheet->getActiveSheet()->setCellValue('H' . $totalIndex, $total['technicalAssistance']['GO']);
            $spreadsheet->getActiveSheet()->setCellValue('I' . $totalIndex, $total['technicalAssistance']['NGO']);
            $spreadsheet->getActiveSheet()->setCellValue('J' . $totalIndex, $total['technicalAssistance']['IND']);
            $spreadsheet->getActiveSheet()->setCellValue('K' . $totalIndex, $total['donors']);
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

        $types = [
            'TC'     => 'THERAPEUTIC COMMUNITY',
            'RJ'     => 'RESTORATIVE JUSTICE',
            'VPA'    => 'VOLUNTEERISM',
            'GAD'    => 'GENDER AND DEVELOPMENT (GAD)',
            'OTHERS' => 'OTHERS',
        ];

        $spreadsheet = new Spreadsheet();
        $textAndCoordinates = [
            'A1' => 'REGION ' . $this->region->getName(),
            'A2' => 'REGIONAL OFFICE IQPR CONSOLIDATION FORM',
            'A3' => $this->quarters->getName() . ' QTR, ' . $this->quarters->getYear(),
            'A4' => 'IV. RESOURCE MOBILIZATION',

            'A5' => $types[$this->type],

            'A6' => 'FIELD OFFICES',
            'B6' => 'AMOUNT',

            'B7' => 'CASH',
            'B8' => 'GO',
            'C8' => 'NGO',
            'D8' => 'Individual',

            'E7' => 'SUPPLIES AND MATERIALS',
            'E8' => 'GO',
            'F8' => 'NGO',
            'G8' => 'Individual',

            'H7' => 'TECHNICAL',
            'H8' => 'GO',
            'I8' => 'NGO',
            'J8' => 'Individual',

            'K6' => '# OF DONORS / LINKAGES',
        ];

        $mergesCoordinates = [
            'A1:K1',
            'A2:K2',
            'A3:K3', 
            'A4:K4', 

            'A5:K5', 
            'A6:A8', 
            'B6:J6', 
            'B7:D7', 
            'E7:G7', 
            'H7:J7', 
            'K6:K8',
        ];

        $boldCoordinates = [
            'A1:A8', 

            'A5:J5', 
            'A6:K8', 
        ];

        $verticalAlignedCoordinates = [
            'A1:K1'  => 'center', 
            'A2:K2'  => 'center', 
            'A3:K3'  => 'center', 

            'A6:K8' => 'center',
        ];

        $horizontalAlignedCoordinates = [
            'A1:K1'  => 'center', 
            'A2:K2'  => 'center', 
            'A3:K3'  => 'center', 
            'A5:K5'  => 'center', 

            'A6:K8' => 'center',
        ];

        $adjustedColumnWidthCoordinates = [
            'A' => 35, 
            'B' => 15,
            'C' => 15,
            'D' => 15,
            'E' => 15,
            'F' => 15,
            'G' => 15,
            'H' => 15,
            'I' => 15,
            'J' => 15,
            'K' => 15,
        ];

        $wrappedTextCoordinates = [
            'K6',
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
        $this->type = $data['type'];

        $this->fieldOffices = $this->fieldOfficesRepository->findBy(['regionId' => $data['region_id']]);

        $result = [];

        foreach ($this->fieldOffices as $fieldOffice) {
            $res = $this->service->getReport(
                $data['quarter_id'],
                $fieldOffice->getFieldOfficeId(),
            );

            $result[$fieldOffice->getFieldOfficeId()] = $res['data'] ?? [];
        }

        return ['rows' => $result];
    }
}