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

        $result = $this->data;

        // var_dump($result);

        $capabilityBuilding = [
            'Personnel' => [
                'Training on Therapeutic Community' => [
                    'participants' => 0,
                    'managerial' => 0,
                    'technical' => 0,
                    'foundation' => 0,
                    'training_hours' => 0
                ],
                'Training on Restorative Justice' => [
                    'participants' => 0,
                    'managerial' => 0,
                    'technical' => 0,
                    'foundation' => 0,
                    'training_hours' => 0
                ],
                'Training on Volunteerism' => [
                    'participants' => 0,
                    'managerial' => 0,
                    'technical' => 0,
                    'foundation' => 0,
                    'training_hours' => 0
                ],
                'Training on GAD' => [
                    'participants' => 0,
                    'managerial' => 0,
                    'technical' => 0,
                    'foundation' => 0,
                    'training_hours' => 0
                ],
                'Other Training Courses or Seminars or Fora or Symposia' => [
                    'participants' => 0,
                    'managerial' => 0,
                    'technical' => 0,
                    'foundation' => 0,
                    'training_hours' => 0
                ],
                'Conferences or Conventions' => [
                    'participants' => 0,
                    'managerial' => 0,
                    'technical' => 0,
                    'foundation' => 0,
                    'training_hours' => 0
                ],
                'Staff or Committee Meetings with Professional Dev\'t' => [
                    'participants' => 0,
                    'managerial' => 0,
                    'technical' => 0,
                    'foundation' => 0,
                    'training_hours' => 0
                ],
                'Total' => [
                    'participants' => 0,
                    'managerial' => 0,
                    'technical' => 0,
                    'foundation' => 0,
                    'training_hours' => 0
                ]
            ],
            'VPA' => [
                'Training on Therapeutic Community' => [
                    'participants' => 0,
                    'in_house' => 0,
                    'out_house' => 0,
                    'training_hours' => 0
                ],
                'Training on Restorative Justice' => [
                    'participants' => 0,
                    'in_house' => 0,
                    'out_house' => 0,
                    'training_hours' => 0
                ],
                'Training on Volunteerism' => [
                    'participants' => 0,
                    'in_house' => 0,
                    'out_house' => 0,
                    'training_hours' => 0
                ],
                'Training on GAD' => [
                    'participants' => 0,
                    'in_house' => 0,
                    'out_house' => 0,
                    'training_hours' => 0
                ],
                'Other Training Courses or Seminars or Fora or Symposia' => [
                    'participants' => 0,
                    'in_house' => 0,
                    'out_house' => 0,
                    'training_hours' => 0
                ],
                'Conferences or Conventions' => [
                    'participants' => 0,
                    'in_house' => 0,
                    'out_house' => 0,
                    'training_hours' => 0
                ],
                'VPA Meetings or Assemblies' => [
                    'participants' => 0,
                    'in_house' => 0,
                    'out_house' => 0,
                    'training_hours' => 0
                ],
                'Total' => [
                    'participants' => 0,
                    'in_house' => 0,
                    'out_house' => 0,
                    'training_hours' => 0   
                ]
            ]
        ];


        if ($result['rows']) {
            foreach ($result['rows'] as $k1 => $v1) {
                foreach ($v1 as $k2 => $v2) {
                    foreach ($v2 as $k3 => $v3) {
                        $capabilityBuilding[$k1][$k2]['participants'] += count($v3['participants']);
                        $capabilityBuilding[$k1]['Total']['participants'] += count($v3['participants']);

                        // Personnel
                        if ($k1 == 'Personnel') {
                            $capabilityBuilding[$k1][$k2]['managerial'] += ($v3['not_managerial_supervisory'] == 1);
                            $capabilityBuilding[$k1]['Total']['managerial'] += ($v3['not_managerial_supervisory'] == 1);
    
                            $capabilityBuilding[$k1][$k2]['technical'] += ($v3['not_technical'] == 1);
                            $capabilityBuilding[$k1]['Total']['technical'] += ($v3['not_technical'] == 1);
    
                            $capabilityBuilding[$k1][$k2]['foundation'] += ($v3['not_foundation'] == 1);
                            $capabilityBuilding[$k1]['Total']['foundation'] += ($v3['not_foundation'] == 1);
                        }


                        // VPA
                        if ($k1 == 'VPA') {
                            $capabilityBuilding[$k1][$k2]['in_house'] += ($v3['tc_in_house'] == 1);
                            $capabilityBuilding[$k1]['Total']['in_house'] += ($v3['tc_in_house'] == 1);

                            $capabilityBuilding[$k1][$k2]['out_house'] += ($v3['tc_out_house']) ? 1 : 0;
                            $capabilityBuilding[$k1]['Total']['out_house'] += ($v3['tc_out_house']) ? 1 : 0;
                        }

                        $capabilityBuilding[$k1][$k2]['training_hours'] += $v3['no_of_training_hours'];
                        $capabilityBuilding[$k1]['Total']['training_hours'] += $v3['no_of_training_hours'];
                    }
                }
            }
        }

        $spreadsheet->getActiveSheet()->setCellValue('B9', $capabilityBuilding['Personnel']['Training on Therapeutic Community']['participants']);
        $spreadsheet->getActiveSheet()->setCellValue('B10', $capabilityBuilding['Personnel']['Training on Restorative Justice']['participants']);
        $spreadsheet->getActiveSheet()->setCellValue('B11', $capabilityBuilding['Personnel']['Training on Volunteerism']['participants']);
        $spreadsheet->getActiveSheet()->setCellValue('B12', $capabilityBuilding['Personnel']['Training on GAD']['participants']);
        $spreadsheet->getActiveSheet()->setCellValue('B13', $capabilityBuilding['Personnel']['Other Training Courses or Seminars or Fora or Symposia']['participants']);
        $spreadsheet->getActiveSheet()->setCellValue('B14', $capabilityBuilding['Personnel']['Conferences or Conventions']['participants']);
        $spreadsheet->getActiveSheet()->setCellValue('B15', $capabilityBuilding['Personnel']['Staff or Committee Meetings with Professional Dev\'t']['participants']);
        $spreadsheet->getActiveSheet()->setCellValue('B16', $capabilityBuilding['Personnel']['Total']['participants']);

        $spreadsheet->getActiveSheet()->setCellValue('C9', $capabilityBuilding['Personnel']['Training on Therapeutic Community']['managerial']);
        $spreadsheet->getActiveSheet()->setCellValue('C10', $capabilityBuilding['Personnel']['Training on Restorative Justice']['managerial']);
        $spreadsheet->getActiveSheet()->setCellValue('C11', $capabilityBuilding['Personnel']['Training on Volunteerism']['managerial']);
        $spreadsheet->getActiveSheet()->setCellValue('C12', $capabilityBuilding['Personnel']['Training on GAD']['managerial']);
        $spreadsheet->getActiveSheet()->setCellValue('C13', $capabilityBuilding['Personnel']['Other Training Courses or Seminars or Fora or Symposia']['managerial']);
        $spreadsheet->getActiveSheet()->setCellValue('C14', $capabilityBuilding['Personnel']['Conferences or Conventions']['managerial']);
        $spreadsheet->getActiveSheet()->setCellValue('C15', $capabilityBuilding['Personnel']['Staff or Committee Meetings with Professional Dev\'t']['managerial']);
        $spreadsheet->getActiveSheet()->setCellValue('C16', $capabilityBuilding['Personnel']['Total']['managerial']);

        $spreadsheet->getActiveSheet()->setCellValue('D9', $capabilityBuilding['Personnel']['Training on Therapeutic Community']['technical']);
        $spreadsheet->getActiveSheet()->setCellValue('D10', $capabilityBuilding['Personnel']['Training on Restorative Justice']['technical']);
        $spreadsheet->getActiveSheet()->setCellValue('D11', $capabilityBuilding['Personnel']['Training on Volunteerism']['technical']);
        $spreadsheet->getActiveSheet()->setCellValue('D12', $capabilityBuilding['Personnel']['Training on GAD']['technical']);
        $spreadsheet->getActiveSheet()->setCellValue('D13', $capabilityBuilding['Personnel']['Other Training Courses or Seminars or Fora or Symposia']['technical']);
        $spreadsheet->getActiveSheet()->setCellValue('D14', $capabilityBuilding['Personnel']['Conferences or Conventions']['technical']);
        $spreadsheet->getActiveSheet()->setCellValue('D15', $capabilityBuilding['Personnel']['Staff or Committee Meetings with Professional Dev\'t']['technical']);
        $spreadsheet->getActiveSheet()->setCellValue('D16', $capabilityBuilding['Personnel']['Total']['technical']);

        $spreadsheet->getActiveSheet()->setCellValue('E9', $capabilityBuilding['Personnel']['Training on Therapeutic Community']['foundation']);
        $spreadsheet->getActiveSheet()->setCellValue('E10', $capabilityBuilding['Personnel']['Training on Restorative Justice']['foundation']);
        $spreadsheet->getActiveSheet()->setCellValue('E11', $capabilityBuilding['Personnel']['Training on Volunteerism']['foundation']);
        $spreadsheet->getActiveSheet()->setCellValue('E12', $capabilityBuilding['Personnel']['Training on GAD']['foundation']);
        $spreadsheet->getActiveSheet()->setCellValue('E13', $capabilityBuilding['Personnel']['Other Training Courses or Seminars or Fora or Symposia']['foundation']);
        $spreadsheet->getActiveSheet()->setCellValue('E14', $capabilityBuilding['Personnel']['Conferences or Conventions']['foundation']);
        $spreadsheet->getActiveSheet()->setCellValue('E15', $capabilityBuilding['Personnel']['Staff or Committee Meetings with Professional Dev\'t']['foundation']);
        $spreadsheet->getActiveSheet()->setCellValue('E16', $capabilityBuilding['Personnel']['Total']['foundation']);

        $spreadsheet->getActiveSheet()->setCellValue('F9', $capabilityBuilding['Personnel']['Training on Therapeutic Community']['training_hours']);
        $spreadsheet->getActiveSheet()->setCellValue('F10', $capabilityBuilding['Personnel']['Training on Restorative Justice']['training_hours']);
        $spreadsheet->getActiveSheet()->setCellValue('F11', $capabilityBuilding['Personnel']['Training on Volunteerism']['training_hours']);
        $spreadsheet->getActiveSheet()->setCellValue('F12', $capabilityBuilding['Personnel']['Training on GAD']['training_hours']);
        $spreadsheet->getActiveSheet()->setCellValue('F13', $capabilityBuilding['Personnel']['Other Training Courses or Seminars or Fora or Symposia']['training_hours']);
        $spreadsheet->getActiveSheet()->setCellValue('F14', $capabilityBuilding['Personnel']['Conferences or Conventions']['training_hours']);
        $spreadsheet->getActiveSheet()->setCellValue('F15', $capabilityBuilding['Personnel']['Staff or Committee Meetings with Professional Dev\'t']['training_hours']);
        $spreadsheet->getActiveSheet()->setCellValue('F16', $capabilityBuilding['Personnel']['Total']['training_hours']);

        $spreadsheet->getActiveSheet()->setCellValue('G9', $capabilityBuilding['VPA']['Training on Therapeutic Community']['participants']);
        $spreadsheet->getActiveSheet()->setCellValue('G10', $capabilityBuilding['VPA']['Training on Restorative Justice']['participants']);
        $spreadsheet->getActiveSheet()->setCellValue('G11', $capabilityBuilding['VPA']['Training on Volunteerism']['participants']);
        $spreadsheet->getActiveSheet()->setCellValue('G12', $capabilityBuilding['VPA']['Training on GAD']['participants']);
        $spreadsheet->getActiveSheet()->setCellValue('G13', $capabilityBuilding['VPA']['Other Training Courses or Seminars or Fora or Symposia']['participants']);
        $spreadsheet->getActiveSheet()->setCellValue('G14', $capabilityBuilding['VPA']['Conferences or Conventions']['participants']);
        $spreadsheet->getActiveSheet()->setCellValue('G15', $capabilityBuilding['VPA']['VPA Meetings or Assemblies']['participants']);
        $spreadsheet->getActiveSheet()->setCellValue('G16', $capabilityBuilding['VPA']['Total']['participants']);

        $spreadsheet->getActiveSheet()->setCellValue('H9', $capabilityBuilding['VPA']['Training on Therapeutic Community']['in_house']);
        $spreadsheet->getActiveSheet()->setCellValue('H10', $capabilityBuilding['VPA']['Training on Restorative Justice']['in_house']);
        $spreadsheet->getActiveSheet()->setCellValue('H11', $capabilityBuilding['VPA']['Training on Volunteerism']['in_house']);
        $spreadsheet->getActiveSheet()->setCellValue('H12', $capabilityBuilding['VPA']['Training on GAD']['in_house']);
        $spreadsheet->getActiveSheet()->setCellValue('H13', $capabilityBuilding['VPA']['Other Training Courses or Seminars or Fora or Symposia']['in_house']);
        $spreadsheet->getActiveSheet()->setCellValue('H14', $capabilityBuilding['VPA']['Conferences or Conventions']['in_house']);
        $spreadsheet->getActiveSheet()->setCellValue('H15', $capabilityBuilding['VPA']['VPA Meetings or Assemblies']['in_house']);
        $spreadsheet->getActiveSheet()->setCellValue('H16', $capabilityBuilding['VPA']['Total']['in_house']);

        $spreadsheet->getActiveSheet()->setCellValue('I9', $capabilityBuilding['VPA']['Training on Therapeutic Community']['out_house']);
        $spreadsheet->getActiveSheet()->setCellValue('I10', $capabilityBuilding['VPA']['Training on Restorative Justice']['out_house']);
        $spreadsheet->getActiveSheet()->setCellValue('I11', $capabilityBuilding['VPA']['Training on Volunteerism']['out_house']);
        $spreadsheet->getActiveSheet()->setCellValue('I12', $capabilityBuilding['VPA']['Training on GAD']['out_house']);
        $spreadsheet->getActiveSheet()->setCellValue('I13', $capabilityBuilding['VPA']['Other Training Courses or Seminars or Fora or Symposia']['out_house']);
        $spreadsheet->getActiveSheet()->setCellValue('I14', $capabilityBuilding['VPA']['Conferences or Conventions']['out_house']);
        $spreadsheet->getActiveSheet()->setCellValue('I15', $capabilityBuilding['VPA']['VPA Meetings or Assemblies']['out_house']);
        $spreadsheet->getActiveSheet()->setCellValue('I16', $capabilityBuilding['VPA']['Total']['out_house']);

        $spreadsheet->getActiveSheet()->setCellValue('J9', $capabilityBuilding['VPA']['Training on Therapeutic Community']['training_hours']);
        $spreadsheet->getActiveSheet()->setCellValue('J10', $capabilityBuilding['VPA']['Training on Restorative Justice']['training_hours']);
        $spreadsheet->getActiveSheet()->setCellValue('J11', $capabilityBuilding['VPA']['Training on Volunteerism']['training_hours']);
        $spreadsheet->getActiveSheet()->setCellValue('J12', $capabilityBuilding['VPA']['Training on GAD']['training_hours']);
        $spreadsheet->getActiveSheet()->setCellValue('J13', $capabilityBuilding['VPA']['Other Training Courses or Seminars or Fora or Symposia']['training_hours']);
        $spreadsheet->getActiveSheet()->setCellValue('J14', $capabilityBuilding['VPA']['Conferences or Conventions']['training_hours']);
        $spreadsheet->getActiveSheet()->setCellValue('J15', $capabilityBuilding['VPA']['VPA Meetings or Assemblies']['training_hours']);
        $spreadsheet->getActiveSheet()->setCellValue('J16', $capabilityBuilding['VPA']['Total']['training_hours']);

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
            'A' => 35, 
            'B' => 15,
            'C' => 15,
            'D' => 15,
            'E' => 15,
            'F' => 15,
            'G' => 15,
            'J' => 15
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

        $personnel = $this->capabilityBuilding->getReport(
            $data['quarter_id'],
            $data['field_office_id'],
            'Personnel'
        );
        $vpa = $this->capabilityBuilding->getReport(
            $data['quarter_id'],
            $data['field_office_id'],
            'VPA'
        );

        $result = [
            'Personnel' => $personnel['data'] ?? [], 
            'VPA' => $vpa['data'] ?? []
        ];

        return ['rows' => $result];
    }

}