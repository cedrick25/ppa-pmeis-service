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
            "A6:S" . (15 + (count($this->fieldOffices) * 2))
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
                'Personnel' => [
                    'Training on Therapeutic Community' => [
                        'trainings'    => 0,
                        'participants' => 0,
                    ],
                    'Training on Restorative Justice' => [
                        'trainings'    => 0,
                        'participants' => 0,
                    ],
                    'Training on Volunteerism' => [
                        'trainings'    => 0,
                        'participants' => 0,
                    ],
                    'Training on GAD' => [
                        'trainings'    => 0,
                        'participants' => 0,
                    ],
                    'Other Training Courses or Seminars or Fora or Symposia' => [
                        'trainings'    => 0,
                        'participants' => 0,
                    ],
                    'Conferences or Conventions' => [
                        'trainings'    => 0,
                        'participants' => 0,
                    ],
                    'Staff or Committee Meetings with Professional Dev\'t' => [
                        'trainings'    => 0,
                        'participants' => 0,
                    ],
                    'managerial'       => 0,
                    'technical'        => 0,
                    'foundation'       => 0,
                    'training_hours'   => 0
                ],
                'VPA' => [
                    'Training on Therapeutic Community' => [
                        'trainings'    => 0,
                        'participants' => 0,
                    ],
                    'Training on Restorative Justice' => [
                        'trainings'    => 0,
                        'participants' => 0,
                    ],
                    'Training on Volunteerism' => [
                        'trainings'    => 0,
                        'participants' => 0,
                    ],
                    'Training on GAD' => [
                        'trainings'    => 0,
                        'participants' => 0,
                    ],
                    'Other Training Courses or Seminars or Fora or Symposia' => [
                        'trainings'    => 0,
                        'participants' => 0,
                    ],
                    'Conferences or Conventions' => [
                        'trainings'    => 0,
                        'participants' => 0,
                    ],
                    'VPA Meetings or Assemblies' => [
                        'trainings'    => 0,
                        'participants' => 0,
                    ],
                    'in_house'         => 0,
                    'out_house'        => 0,
                    'training_hours'   => 0   
                ]
            ];
            
            foreach ($this->fieldOffices as $k => $v) {
                $personnelIndex = ($ctr + $k);
                $volunteerIndex = $personnelIndex + count($this->fieldOffices) + 6;

                $capabilityBuilding = [
                    'Personnel' => [
                        'Training on Therapeutic Community' => [
                            'trainings'    => 0,
                            'participants' => 0,
                        ],
                        'Training on Restorative Justice' => [
                            'trainings'    => 0,
                            'participants' => 0,
                        ],
                        'Training on Volunteerism' => [
                            'trainings'    => 0,
                            'participants' => 0,
                        ],
                        'Training on GAD' => [
                            'trainings'    => 0,
                            'participants' => 0,
                        ],
                        'Other Training Courses or Seminars or Fora or Symposia' => [
                            'trainings'    => 0,
                            'participants' => 0,
                        ],
                        'Conferences or Conventions' => [
                            'trainings'    => 0,
                            'participants' => 0,
                        ],
                        'Staff or Committee Meetings with Professional Dev\'t' => [
                            'trainings'    => 0,
                            'participants' => 0,
                        ],
                        'managerial'       => 0,
                        'technical'        => 0,
                        'foundation'       => 0,
                        'training_hours'   => 0
                    ],
                    'VPA' => [
                        'Training on Therapeutic Community' => [
                            'trainings'    => 0,
                            'participants' => 0,
                        ],
                        'Training on Restorative Justice' => [
                            'trainings'    => 0,
                            'participants' => 0,
                        ],
                        'Training on Volunteerism' => [
                            'trainings'    => 0,
                            'participants' => 0,
                        ],
                        'Training on GAD' => [
                            'trainings'    => 0,
                            'participants' => 0,
                        ],
                        'Other Training Courses or Seminars or Fora or Symposia' => [
                            'trainings'    => 0,
                            'participants' => 0,
                        ],
                        'Conferences or Conventions' => [
                            'trainings'    => 0,
                            'participants' => 0,
                        ],
                        'VPA Meetings or Assemblies' => [
                            'trainings'    => 0,
                            'participants' => 0,
                        ],
                        'in_house'         => 0,
                        'out_house'        => 0,
                        'training_hours'   => 0   
                    ]
                ];

                if ($result['rows']) {
                    if ($result['rows'][$v->getFieldOfficeId()]) {
                        foreach ($result['rows'][$v->getFieldOfficeId()] as $k1 => $v1) {
                            foreach ($v1 as $k2 => $v2) {
                                foreach ($v2 as $k3 => $v3) {
                                    $capabilityBuilding[$k1][$k2]['trainings'] += 1;
                                    $total[$k1][$k2]['trainings'] += 1;
                                    $capabilityBuilding[$k1][$k2]['participants'] += count($v3['participants']);
                                    $total[$k1][$k2]['participants'] += count($v3['participants']);
    
                                    // Personnel
                                    if ($k1 == 'Personnel') {
                                        $capabilityBuilding[$k1]['managerial'] += ($v3['not_managerial_supervisory'] == 1);
                                        $total[$k1]['managerial'] += ($v3['not_managerial_supervisory'] == 1);
                
                                        $capabilityBuilding[$k1]['technical'] += ($v3['not_technical'] == 1);
                                        $total[$k1]['technical'] += ($v3['not_technical'] == 1);
                
                                        $capabilityBuilding[$k1]['foundation'] += ($v3['not_foundation'] == 1);
                                        $total[$k1]['foundation'] += ($v3['not_foundation'] == 1);
                                    }
    
    
                                    // VPA
                                    if ($k1 == 'VPA') {
                                        $capabilityBuilding[$k1]['in_house'] += ($v3['tc_in_house']) ? 1 : 0;
                                        $total[$k1]['in_house'] += ($v3['tc_in_house']) ? 1 : 0;
    
                                        $capabilityBuilding[$k1]['out_house'] += ($v3['tc_out_house']) ? 1 : 0;
                                        $total[$k1]['out_house'] += ($v3['tc_out_house']) ? 1 : 0;
                                    }
    
                                    $capabilityBuilding[$k1]['training_hours'] += $v3['no_of_training_hours'];
                                    $total[$k1]['training_hours'] += $v3['no_of_training_hours'];
                                }
                            }
                        }
                    }
                }

                $spreadsheet->getActiveSheet()->setCellValue('A' . $personnelIndex, $v->getName());
                $spreadsheet->getActiveSheet()->setCellValue('B' . $personnelIndex, $capabilityBuilding['Personnel']['Training on Therapeutic Community']['trainings']);
                $spreadsheet->getActiveSheet()->setCellValue('C' . $personnelIndex, $capabilityBuilding['Personnel']['Training on Therapeutic Community']['participants']);
                $spreadsheet->getActiveSheet()->setCellValue('D' . $personnelIndex, $capabilityBuilding['Personnel']['Training on Restorative Justice']['trainings']);
                $spreadsheet->getActiveSheet()->setCellValue('E' . $personnelIndex, $capabilityBuilding['Personnel']['Training on Restorative Justice']['participants']);
                $spreadsheet->getActiveSheet()->setCellValue('F' . $personnelIndex, $capabilityBuilding['Personnel']['Training on Volunteerism']['trainings']);
                $spreadsheet->getActiveSheet()->setCellValue('G' . $personnelIndex, $capabilityBuilding['Personnel']['Training on Volunteerism']['participants']);
                $spreadsheet->getActiveSheet()->setCellValue('H' . $personnelIndex, $capabilityBuilding['Personnel']['Training on GAD']['trainings']);
                $spreadsheet->getActiveSheet()->setCellValue('I' . $personnelIndex, $capabilityBuilding['Personnel']['Training on GAD']['participants']);
                $spreadsheet->getActiveSheet()->setCellValue('J' . $personnelIndex, $capabilityBuilding['Personnel']['Other Training Courses or Seminars or Fora or Symposia']['trainings']);
                $spreadsheet->getActiveSheet()->setCellValue('K' . $personnelIndex, $capabilityBuilding['Personnel']['Other Training Courses or Seminars or Fora or Symposia']['participants']);
                $spreadsheet->getActiveSheet()->setCellValue('L' . $personnelIndex, $capabilityBuilding['Personnel']['Conferences or Conventions']['trainings']);
                $spreadsheet->getActiveSheet()->setCellValue('M' . $personnelIndex, $capabilityBuilding['Personnel']['Conferences or Conventions']['participants']);
                $spreadsheet->getActiveSheet()->setCellValue('N' . $personnelIndex, $capabilityBuilding['Personnel']['Staff or Committee Meetings with Professional Dev\'t']['trainings']);
                $spreadsheet->getActiveSheet()->setCellValue('O' . $personnelIndex, $capabilityBuilding['Personnel']['Staff or Committee Meetings with Professional Dev\'t']['participants']);
                $spreadsheet->getActiveSheet()->setCellValue('P' . $personnelIndex, $capabilityBuilding['Personnel']['managerial']);
                $spreadsheet->getActiveSheet()->setCellValue('Q' . $personnelIndex, $capabilityBuilding['Personnel']['technical']);
                $spreadsheet->getActiveSheet()->setCellValue('R' . $personnelIndex, $capabilityBuilding['Personnel']['foundation']);
                $spreadsheet->getActiveSheet()->setCellValue('S' . $personnelIndex, $capabilityBuilding['Personnel']['training_hours']);

                $spreadsheet->getActiveSheet()->setCellValue('A' . $volunteerIndex, $v->getName());
                $spreadsheet->getActiveSheet()->setCellValue('B' . $volunteerIndex, $capabilityBuilding['VPA']['Training on Therapeutic Community']['trainings']);
                $spreadsheet->getActiveSheet()->setCellValue('C' . $volunteerIndex, $capabilityBuilding['VPA']['Training on Therapeutic Community']['participants']);
                $spreadsheet->getActiveSheet()->setCellValue('D' . $volunteerIndex, $capabilityBuilding['VPA']['Training on Restorative Justice']['trainings']);
                $spreadsheet->getActiveSheet()->setCellValue('E' . $volunteerIndex, $capabilityBuilding['VPA']['Training on Restorative Justice']['participants']);
                $spreadsheet->getActiveSheet()->setCellValue('F' . $volunteerIndex, $capabilityBuilding['VPA']['Training on Volunteerism']['trainings']);
                $spreadsheet->getActiveSheet()->setCellValue('G' . $volunteerIndex, $capabilityBuilding['VPA']['Training on Volunteerism']['participants']);
                $spreadsheet->getActiveSheet()->setCellValue('H' . $volunteerIndex, $capabilityBuilding['VPA']['Training on GAD']['trainings']);
                $spreadsheet->getActiveSheet()->setCellValue('I' . $volunteerIndex, $capabilityBuilding['VPA']['Training on GAD']['participants']);
                $spreadsheet->getActiveSheet()->setCellValue('J' . $volunteerIndex, $capabilityBuilding['VPA']['Other Training Courses or Seminars or Fora or Symposia']['trainings']);
                $spreadsheet->getActiveSheet()->setCellValue('K' . $volunteerIndex, $capabilityBuilding['VPA']['Other Training Courses or Seminars or Fora or Symposia']['participants']);
                $spreadsheet->getActiveSheet()->setCellValue('L' . $volunteerIndex, $capabilityBuilding['VPA']['Conferences or Conventions']['trainings']);
                $spreadsheet->getActiveSheet()->setCellValue('M' . $volunteerIndex, $capabilityBuilding['VPA']['Conferences or Conventions']['participants']);
                $spreadsheet->getActiveSheet()->setCellValue('N' . $volunteerIndex, $capabilityBuilding['VPA']['VPA Meetings or Assemblies']['trainings']);
                $spreadsheet->getActiveSheet()->setCellValue('O' . $volunteerIndex, $capabilityBuilding['VPA']['VPA Meetings or Assemblies']['participants']);
                $spreadsheet->getActiveSheet()->setCellValue('P' . $volunteerIndex, $capabilityBuilding['VPA']['in_house']);
                $spreadsheet->getActiveSheet()->setCellValue('R' . $volunteerIndex, $capabilityBuilding['VPA']['out_house']);
                $spreadsheet->getActiveSheet()->setCellValue('S' . $volunteerIndex, $capabilityBuilding['VPA']['training_hours']);
            }

            $totalPersonnelIndex = $ctr + count($this->fieldOffices);
            $totalVolunteerIndex = $ctr + (count($this->fieldOffices) * 2) + 6;

            $spreadsheet->getActiveSheet()->setCellValue('A' . $totalPersonnelIndex, 'TOTAL');
            $spreadsheet->getActiveSheet()->setCellValue('B' . $totalPersonnelIndex, $total['Personnel']['Training on Therapeutic Community']['trainings']);
            $spreadsheet->getActiveSheet()->setCellValue('C' . $totalPersonnelIndex, $total['Personnel']['Training on Therapeutic Community']['participants']);
            $spreadsheet->getActiveSheet()->setCellValue('D' . $totalPersonnelIndex, $total['Personnel']['Training on Restorative Justice']['trainings']);
            $spreadsheet->getActiveSheet()->setCellValue('E' . $totalPersonnelIndex, $total['Personnel']['Training on Restorative Justice']['participants']);
            $spreadsheet->getActiveSheet()->setCellValue('F' . $totalPersonnelIndex, $total['Personnel']['Training on Volunteerism']['trainings']);
            $spreadsheet->getActiveSheet()->setCellValue('G' . $totalPersonnelIndex, $total['Personnel']['Training on Volunteerism']['participants']);
            $spreadsheet->getActiveSheet()->setCellValue('H' . $totalPersonnelIndex, $total['Personnel']['Training on GAD']['trainings']);
            $spreadsheet->getActiveSheet()->setCellValue('I' . $totalPersonnelIndex, $total['Personnel']['Training on GAD']['participants']);
            $spreadsheet->getActiveSheet()->setCellValue('J' . $totalPersonnelIndex, $total['Personnel']['Other Training Courses or Seminars or Fora or Symposia']['trainings']);
            $spreadsheet->getActiveSheet()->setCellValue('K' . $totalPersonnelIndex, $total['Personnel']['Other Training Courses or Seminars or Fora or Symposia']['participants']);
            $spreadsheet->getActiveSheet()->setCellValue('L' . $totalPersonnelIndex, $total['Personnel']['Conferences or Conventions']['trainings']);
            $spreadsheet->getActiveSheet()->setCellValue('M' . $totalPersonnelIndex, $total['Personnel']['Conferences or Conventions']['participants']);
            $spreadsheet->getActiveSheet()->setCellValue('N' . $totalPersonnelIndex, $total['Personnel']['Staff or Committee Meetings with Professional Dev\'t']['trainings']);
            $spreadsheet->getActiveSheet()->setCellValue('O' . $totalPersonnelIndex, $total['Personnel']['Staff or Committee Meetings with Professional Dev\'t']['participants']);
            $spreadsheet->getActiveSheet()->setCellValue('P' . $totalPersonnelIndex, $total['Personnel']['managerial']);
            $spreadsheet->getActiveSheet()->setCellValue('Q' . $totalPersonnelIndex, $total['Personnel']['technical']);
            $spreadsheet->getActiveSheet()->setCellValue('R' . $totalPersonnelIndex, $total['Personnel']['foundation']);
            $spreadsheet->getActiveSheet()->setCellValue('S' . $totalPersonnelIndex, $total['Personnel']['training_hours']);

            $spreadsheet->getActiveSheet()->setCellValue('A' . $totalVolunteerIndex, 'TOTAL');
            $spreadsheet->getActiveSheet()->setCellValue('B' . $totalVolunteerIndex, $total['VPA']['Training on Therapeutic Community']['trainings']);
            $spreadsheet->getActiveSheet()->setCellValue('C' . $totalVolunteerIndex, $total['VPA']['Training on Therapeutic Community']['participants']);
            $spreadsheet->getActiveSheet()->setCellValue('D' . $totalVolunteerIndex, $total['VPA']['Training on Restorative Justice']['trainings']);
            $spreadsheet->getActiveSheet()->setCellValue('E' . $totalVolunteerIndex, $total['VPA']['Training on Restorative Justice']['participants']);
            $spreadsheet->getActiveSheet()->setCellValue('F' . $totalVolunteerIndex, $total['VPA']['Training on Volunteerism']['trainings']);
            $spreadsheet->getActiveSheet()->setCellValue('G' . $totalVolunteerIndex, $total['VPA']['Training on Volunteerism']['participants']);
            $spreadsheet->getActiveSheet()->setCellValue('H' . $totalVolunteerIndex, $total['VPA']['Training on GAD']['trainings']);
            $spreadsheet->getActiveSheet()->setCellValue('I' . $totalVolunteerIndex, $total['VPA']['Training on GAD']['participants']);
            $spreadsheet->getActiveSheet()->setCellValue('J' . $totalVolunteerIndex, $total['VPA']['Other Training Courses or Seminars or Fora or Symposia']['trainings']);
            $spreadsheet->getActiveSheet()->setCellValue('K' . $totalVolunteerIndex, $total['VPA']['Other Training Courses or Seminars or Fora or Symposia']['participants']);
            $spreadsheet->getActiveSheet()->setCellValue('L' . $totalVolunteerIndex, $total['VPA']['Conferences or Conventions']['trainings']);
            $spreadsheet->getActiveSheet()->setCellValue('M' . $totalVolunteerIndex, $total['VPA']['Conferences or Conventions']['participants']);
            $spreadsheet->getActiveSheet()->setCellValue('N' . $totalVolunteerIndex, $total['VPA']['VPA Meetings or Assemblies']['trainings']);
            $spreadsheet->getActiveSheet()->setCellValue('O' . $totalVolunteerIndex, $total['VPA']['VPA Meetings or Assemblies']['participants']);
            $spreadsheet->getActiveSheet()->setCellValue('P' . $totalVolunteerIndex, $total['VPA']['in_house']);
            $spreadsheet->getActiveSheet()->setCellValue('R' . $totalVolunteerIndex, $total['VPA']['out_house']);
            $spreadsheet->getActiveSheet()->setCellValue('S' . $totalVolunteerIndex, $total['VPA']['training_hours']);
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

        $result = [];

        foreach ($this->fieldOffices as $fieldOffice) {
            $personnel = $this->capabilityBuilding->getReport(
                $data['quarter_id'],
                $fieldOffice->getFieldOfficeId(),
                'Personnel'
            );

            $vpa = $this->capabilityBuilding->getReport(
                $data['quarter_id'],
                $fieldOffice->getFieldOfficeId(),
                'VPA'
            );

            $result[$fieldOffice->getFieldOfficeId()]['Personnel'] = $personnel['data'] ?? [];
            $result[$fieldOffice->getFieldOfficeId()]['VPA'] = $vpa['data'] ?? [];
        }

        return ['rows' => $result];
    }

}