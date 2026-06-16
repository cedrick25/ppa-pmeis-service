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
use App\Service\Volunteerism\TechnicalAssistance;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TableIIIA3Regional implements Form
{
    private const TABLE_NAME = "TableIIIA3Regional";


    public function __construct(
        private TechnicalAssistance         $service,
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
            "A6:I" . (9 + count($this->fieldOffices))
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

        $result = $this->data;

        if ($this->fieldOffices) {
            $ctr = 9;

            $total = [
                'TECHNICAL ASSISTANCE' => [
                    'agencies_assisted'   => 0,
                    'assistance_rendered' => 0,
                    'participants'        => 0,
                ],
                'OUTREACH ACTIVITIES TO OTHER AGENCIES' => [
                    'agencies_assisted'   => 0,
                    'assistance_rendered' => 0,
                    'participants'        => 0,
                ],
                'OTHER RELATED COMMUNITY PARTICIPATION' => [
                    'agencies_assisted'   => 0,
                    'assistance_rendered' => 0,
                    'participants'        => 0,
                ],
                'PUBLIC ASSISTANCE' => [
                    'agencies_assisted'   => 0,
                    'assistance_rendered' => 0,
                    'participants'        => 0,
                ],
            ];
            foreach ($this->fieldOffices as $k => $v) {
                $index = ($ctr + $k);

                $activityRoles = [
                    'TECHNICAL ASSISTANCE' => [
                        'agencies_assisted'   => 0,
                        'assistance_rendered' => 0,
                        'participants'        => 0,
                    ],
                    'OUTREACH ACTIVITIES TO OTHER AGENCIES' => [
                        'agencies_assisted'   => 0,
                        'assistance_rendered' => 0,
                        'participants'        => 0,
                    ],
                    'OTHER RELATED COMMUNITY PARTICIPATION' => [
                        'agencies_assisted'   => 0,
                        'assistance_rendered' => 0,
                        'participants'        => 0,
                    ],
                    'PUBLIC ASSISTANCE' => [
                        'agencies_assisted'   => 0,
                        'assistance_rendered' => 0,
                        'participants'        => 0,
                    ],
                ];
        
                if ($result['rows']) {
                    if ($result['rows'][$v->getFieldOfficeId()]) {
                        foreach ($result['rows'][$v->getFieldOfficeId()] as $v1) {
                            $activityRoles[$v1['assistance_type']]['agencies_assisted'] += 1;
                            $total[$v1['assistance_type']]['agencies_assisted'] += 1;
                            $activityRoles[$v1['assistance_type']]['assistance_rendered'] += 1;
                            $total[$v1['assistance_type']]['assistance_rendered'] += 1;
                            
                            foreach ($v1['participants'] as $participant) {
                                $activityRoles[$v1['assistance_type']]['participants'] += $participant['no'];
                                $total[$v1['assistance_type']]['participants'] += $participant['no'];
                            }
                        }
                    }
                }

                $spreadsheet->getActiveSheet()->setCellValue('A' . $index, $v->getName());
                $spreadsheet->getActiveSheet()->setCellValue('B' . $index, $activityRoles['TECHNICAL ASSISTANCE']['agencies_assisted']);
                $spreadsheet->getActiveSheet()->setCellValue('C' . $index, $activityRoles['TECHNICAL ASSISTANCE']['assistance_rendered']);
                $spreadsheet->getActiveSheet()->setCellValue('D' . $index, $activityRoles['TECHNICAL ASSISTANCE']['participants']);
                $spreadsheet->getActiveSheet()->setCellValue('E' . $index, $activityRoles['OUTREACH ACTIVITIES TO OTHER AGENCIES']['agencies_assisted']);
                $spreadsheet->getActiveSheet()->setCellValue('F' . $index, $activityRoles['OUTREACH ACTIVITIES TO OTHER AGENCIES']['assistance_rendered']);
                $spreadsheet->getActiveSheet()->setCellValue('G' . $index, $activityRoles['OUTREACH ACTIVITIES TO OTHER AGENCIES']['participants']);
                $spreadsheet->getActiveSheet()->setCellValue('H' . $index, $activityRoles['PUBLIC ASSISTANCE']['assistance_rendered']);
                $spreadsheet->getActiveSheet()->setCellValue('I' . $index, $activityRoles['PUBLIC ASSISTANCE']['participants']);
            }

            $totalIndex = $ctr + count($this->fieldOffices);

            $spreadsheet->getActiveSheet()->setCellValue('A' . $totalIndex, 'TOTAL');
            $spreadsheet->getActiveSheet()->setCellValue('B' . $totalIndex, $total['TECHNICAL ASSISTANCE']['agencies_assisted']);
            $spreadsheet->getActiveSheet()->setCellValue('C' . $totalIndex, $total['TECHNICAL ASSISTANCE']['assistance_rendered']);
            $spreadsheet->getActiveSheet()->setCellValue('D' . $totalIndex, $total['TECHNICAL ASSISTANCE']['participants']);
            $spreadsheet->getActiveSheet()->setCellValue('E' . $totalIndex, $total['OUTREACH ACTIVITIES TO OTHER AGENCIES']['agencies_assisted']);
            $spreadsheet->getActiveSheet()->setCellValue('F' . $totalIndex, $total['OUTREACH ACTIVITIES TO OTHER AGENCIES']['assistance_rendered']);
            $spreadsheet->getActiveSheet()->setCellValue('G' . $totalIndex, $total['OUTREACH ACTIVITIES TO OTHER AGENCIES']['participants']);
            $spreadsheet->getActiveSheet()->setCellValue('H' . $totalIndex, $total['PUBLIC ASSISTANCE']['assistance_rendered']);
            $spreadsheet->getActiveSheet()->setCellValue('I' . $totalIndex, $total['PUBLIC ASSISTANCE']['participants']);
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
            'A4' => 'III. SOCIAL MARKETING',

            'A5' => 'III.A.3. Technical Assistance/ Outreach Program To Other Agencies/ Public Assistance',

            'A6' => 'Field Offices',
            'B6' => 'NUMBER OF',

            'B7' => 'Technical Assistance',
            'B8' => 'Agencies Assisted',
            'C8' => 'Assistance Rendered',
            'D8' => 'Participants/ Beneficiaries',

            'E7' => 'Outreach Programs/ Activities',
            'E8' => 'Agencies Assisted',
            'F8' => 'Assistance Rendered',
            'G8' => 'Participants/ Beneficiaries',

            'H7' => 'Public Assistance (Total # of Walk-in Clients)',
            'H8' => 'Assistance Rendered',
            'I8' => 'Participants/ Beneficiaries',
        ];

        $mergesCoordinates = [
            'A1:I1',
            'A2:I2',
            'A3:I3', 
            'A4:I4', 

            'A5:I5', 
            'A6:A8', 
            'B6:I6', 
            'B7:D7', 
            'E7:G7', 
            'H7:I7', 
        ];

        $boldCoordinates = [
            'A1:A8', 

            'A5:J5', 
            'A6:I8', 
        ];

        $verticalAlignedCoordinates = [
            'A1:I1'  => 'center', 
            'A2:I2'  => 'center', 
            'A3:I3'  => 'center', 

            'A6:I8' => 'center',
        ];

        $horizontalAlignedCoordinates = [
            'A1:I1'  => 'center', 
            'A2:I2'  => 'center', 
            'A3:I3'  => 'center', 

            'A6:I8' => 'center',
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
        ];

        $wrappedTextCoordinates = [
            'D8',
            'G8',
            'H7',
            'I8',
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