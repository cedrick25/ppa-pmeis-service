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
use App\Service\Volunteerism\SocialMarketing;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TableIIIA1National implements Form
{
    private const TABLE_NAME = "TableIIIA1National";


    public function __construct(
        private SocialMarketing $service,
        private RegionsRepository           $regionsRepository,
        private FieldOfficesRepository      $fieldOfficesRepository,
        private QuartersRepository          $quartersRepository,
        private SessionsRepository          $sessionsRepository,
        private ClientSessionsRepository    $clientSessionsRepository,
        private ClientsRepository           $clientsRepository,
        private array                       $data = [],
        private array                       $sessionIds = [],
        private array                       $regions = [],
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
            "A6:H" . (9 + count($this->regions))
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

        if ($this->regions) {
            $ctr = 9;
            $totals = [
                'fora' => [
                    'activities'   => 0,
                    'participants' => 0,
                    'primers'      => 0,
                ],
                'press'   => 0,
                'radio'   => 0,
                'tv'      => 0,
                'primers' => 0,
            ];

            foreach ($this->regions as $k => $v) {
                $index = ($ctr + $k);

                $smCategories = [
                    'fora' => [
                        'activities'   => 0,
                        'participants' => 0,
                        'primers'      => 0,
                    ],
                    'press'   => 0,
                    'radio'   => 0,
                    'tv'      => 0,
                    'primers' => 0,
                ];
        
                if ($result['rows']) {
                    if ($result['rows'][$v->getRegionId()]) {
                        foreach ($result['rows'][$v->getRegionId()] as $fieldOffice) {
                            foreach ($fieldOffice as $v1) {
                                switch($v1['social_marketing_activity_id']) {
                                    case 1:
                                        $smCategories['fora']['activities']++;
                                        $totals['fora']['activities']++;
                
                                        foreach ($v1['participants'] as $participant) {
                                            $smCategories['fora']['participants']+= $participant['no'];
                                            $totals['fora']['participants']+= $participant['no'];
                                        }
                
                                        $smCategories['fora']['primers']+= $v1['primers'];
                                        $totals['fora']['primers']+= $v1['primers'];
                                        break;
                                    case 2:
                                        $smCategories['press']++;
                                        $totals['press']++;
                                        $smCategories['primers']+= $v1['primers'];
                                        $totals['primers']+= $v1['primers'];
                                        break;
                                    case 3:
                                        $smCategories['radio']++;
                                        $totals['radio']++;
                                        $smCategories['primers']+= $v1['primers'];
                                        $totals['primers']+= $v1['primers'];
                                        break;
                                    case 4:
                                        $smCategories['tv']++;
                                        $totals['tv']++;
                                        $smCategories['primers']+= $v1['primers'];
                                        $totals['primers']+= $v1['primers'];
                                        break;
                                }
                            }
                        }
                    }
                }

                $spreadsheet->getActiveSheet()->setCellValue('A' . $index, $v->getName());
                $spreadsheet->getActiveSheet()->setCellValue('B' . $index, $smCategories['fora']['activities']);
                $spreadsheet->getActiveSheet()->setCellValue('C' . $index, $smCategories['fora']['participants']);
                $spreadsheet->getActiveSheet()->setCellValue('D' . $index, $smCategories['fora']['primers']);
                $spreadsheet->getActiveSheet()->setCellValue('E' . $index, $smCategories['press']);
                $spreadsheet->getActiveSheet()->setCellValue('F' . $index, $smCategories['radio']);
                $spreadsheet->getActiveSheet()->setCellValue('G' . $index, $smCategories['tv']);
                $spreadsheet->getActiveSheet()->setCellValue('H' . $index, $smCategories['primers']);
            }

            $totalIndex = $ctr + count($this->regions);

            $spreadsheet->getActiveSheet()->setCellValue('A' . $totalIndex, 'TOTAL');
            $spreadsheet->getActiveSheet()->setCellValue('B' . $totalIndex, $totals['fora']['activities']);
            $spreadsheet->getActiveSheet()->setCellValue('C' . $totalIndex, $totals['fora']['participants']);
            $spreadsheet->getActiveSheet()->setCellValue('D' . $totalIndex, $totals['fora']['primers']);
            $spreadsheet->getActiveSheet()->setCellValue('E' . $totalIndex, $totals['press']);
            $spreadsheet->getActiveSheet()->setCellValue('F' . $totalIndex, $totals['radio']);
            $spreadsheet->getActiveSheet()->setCellValue('G' . $totalIndex, $totals['tv']);
            $spreadsheet->getActiveSheet()->setCellValue('H' . $totalIndex, $totals['primers']);
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
        $count = count($this->regions) + 3;
        $x = 8 + $count;

        $spreadsheet = new Spreadsheet();
        $textAndCoordinates = [
            'A2' => 'AGENCY IQPR CONSOLIDATION FORM',
            'A3' => $this->quarters->getName() . ' QTR, ' . $this->quarters->getYear(),
            'A4' => 'III. SOCIAL MARKETING',

            'A5' => 'A.1. INFORMATION DISSEMINATION',

            'A6' => 'REGIONAL OFFICES',
            'B6' => 'NUMBER OF',

            'B7' => 'FORA / SYMPOSIA',
            'B8' => 'ACTIVITIES CONDUCTED',
            'C8' => 'PARTICIPANTS',
            'D8' => 'PRIMERS DISTRIBUTED',

            'E7' => 'MEDIA EXPOSURES',
            'E8' => 'PRINT',
            'F8' => 'RADIO',
            'G8' => 'TV',

            'H7' => 'Primers Distributed',
        ];

        $mergesCoordinates = [
            'A1:H1',
            'A2:H2',
            'A3:H3', 
            'A4:H4', 

            'A5:H5', 
            'A6:A8', 
            'B6:H6', 
            'B7:D7', 
            'E7:G7', 
            'H7:H8', 
            
        ];

        $boldCoordinates = [
            'A1:A8', 

            'A5:J5', 
            'A6:H8', 
        ];

        $verticalAlignedCoordinates = [
            'A1:H1'  => 'center', 
            'A2:H2'  => 'center', 
            'A3:H3'  => 'center', 

            'A6:H8' => 'center',
        ];

        $horizontalAlignedCoordinates = [
            'A1:H1'  => 'center', 
            'A2:H2'  => 'center', 
            'A3:H3'  => 'center', 

            'A6:H8' => 'center',
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
        ];

        $wrappedTextCoordinates = [
            'B7:B8', 
            'C8',
            'D8',
            'E8',
            'F7:F8', 
            'G7:G8', 
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
        $this->quarters = $this->quartersRepository->find($data['quarter_id']);
        $this->regions = $this->regionsRepository->list();

        $result = [];

        foreach ($this->regions as $region) {
            $result[$region->getRegionId()] = [];
            $fieldOffices = $this->fieldOfficesRepository->findBy(['regionId' => $region->getRegionId()]);

            foreach ($fieldOffices as $fieldOffice) {
                $res = $this->service->getReport(
                    $data['quarter_id'],
                    $fieldOffice->getFieldOfficeId(),
                    'INFORMATION_DISSEMINATION',
                );
    
                $result[$region->getRegionId()][$fieldOffice->getFieldOfficeId()] = $res['data'] ?? [];
            }
        }

        return ['rows' => $result];
    }
}