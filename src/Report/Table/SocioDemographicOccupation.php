<?php

declare(strict_types=1);

namespace App\Report\Table;

use App\Enum\OccupationType;
use App\Service\Volunteerism\Volunteer;
use App\Repository\QuartersRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SocioDemographicOccupation implements Form
{
    private const TABLE_NAME = "SocioDemographicOccupation";
     private string $regionName = "";
     private Spreadsheet $spreadsheet;
     private ?int $quarterId = null;
    
    public function __construct(
        private Volunteer   $service,
        private QuartersRepository                          $quartersRepository,
        private int   $lastFilledOutCellY = 10,
        private array $data = [],
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
        $this->data = $this->getData($data);
        $this->quarterId = $data['quarter_id'] ?? 0;
        unset($this->data['rows']['Regional Office - Region I']);
        unset($this->data['rows']['Regional Office - Region II']);
        unset($this->data['rows']['Regional Office - Region III']);
        unset($this->data['rows']['Regional Office - Region IV-A']);
        unset($this->data['rows']['Regional Office - Region IV-B']);
        unset($this->data['rows']['Regional Office - Region V']);
        unset($this->data['rows']['Regional Office - Region VI']);
        unset($this->data['rows']['Regional Office - Region VII']);
        unset($this->data['rows']['Regional Office - Region VIII']);
        unset($this->data['rows']['Regional Office - Region IX']);
        unset($this->data['rows']['Regional Office - Region X']);
        unset($this->data['rows']['Regional Office - Region XI']);
        unset($this->data['rows']['Regional Office - Region XII']);
        unset($this->data['rows']['Regional Office - Region XIII']);
        unset($this->data['rows']['Regional Office - CAR']);
        unset($this->data['rows']['Regional Office - NCR']);

        $this->spreadsheet = new Spreadsheet();

        /** remove default empty sheet */
        $this->spreadsheet->removeSheetByIndex(0);

        /** OCCUPATION SHEET */
        $sheet = $this->spreadsheet->createSheet();
        $sheet->setTitle('Occupation');
        $this->spreadsheet->setActiveSheetIndexByName('Occupation');
        $this->footer(); 

        /** Education SHEET */
        $this->lastFilledOutCellY = 10;
        $sheet = $this->spreadsheet->createSheet();
        $sheet->setTitle('Education & Age');
        $this->spreadsheet->setActiveSheetIndexByName('Education & Age');
        $this->footer(); 

         /** Gender SHEET */
        $this->lastFilledOutCellY = 10;
        $sheet = $this->spreadsheet->createSheet();
        $sheet->setTitle('Gender Civil Religion');
        $this->spreadsheet->setActiveSheetIndexByName('Gender Civil Religion');
        $this->footer(); 

        $writer = IOFactory::createWriter($this->spreadsheet, "Xlsx");

        $filePath = $_ENV['XLSX_PATH_FILE'] . "VPASocioDemographicReport" . "-" . time() . ".xlsx";
        $writer->save($filePath);

        return new BinaryFileResponse($filePath);
    }

    public function footer(): Spreadsheet
    {
        $spreadsheet = $this->body();
        $this->lastFilledOutCellY++;

        return $spreadsheet;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function body(): Spreadsheet
    {
        return $this->getSocioDemograpicBody();
    }

    public function getSocioDemograpicBody(): Spreadsheet
    {
        $spreadsheet = $this->header();
        $sheetTitle = $spreadsheet->getActiveSheet()->getTitle();
        if($sheetTitle === 'Occupation') {
           $coordinates = [
            'occupation' => [
                OccupationType::ARMED_FORCES_OCCUPATION => 'B',
                OccupationType::MANAGERS => 'C',
                OccupationType::PROFESSIONALS => 'D',
                OccupationType::TECHNICAL_ASSOCIATE_PROFESSIONALS => 'E',
                OccupationType::CLERICAL_SUPPORT_WORKERS => 'F',
                OccupationType::SERVICE_AND_SALES_WORKERS => 'G',
                OccupationType::SKILLED_AGRICULTURAL_FORESTRY_AND_FISHERY_WORKERS => 'H',
                OccupationType::CRAFT_AND_RELATED_TRADES_WORKERS => 'I',
                OccupationType::PLANT_AND_MACHINES_OPERATORS_AND_ASSEMBLERS => 'J',
                OccupationType::ELEMENTARY_OCCUPATION => 'K',
                OccupationType::UNEMPLOYED => 'L'
            ]
        ];

        $total = [
            OccupationType::ARMED_FORCES_OCCUPATION => 0,
            OccupationType::MANAGERS => 0,
            OccupationType::PROFESSIONALS => 0,
            OccupationType::TECHNICAL_ASSOCIATE_PROFESSIONALS => 0,
            OccupationType::CLERICAL_SUPPORT_WORKERS => 0,
            OccupationType::SERVICE_AND_SALES_WORKERS => 0,
            OccupationType::SKILLED_AGRICULTURAL_FORESTRY_AND_FISHERY_WORKERS => 0,
            OccupationType::CRAFT_AND_RELATED_TRADES_WORKERS => 0,
            OccupationType::PLANT_AND_MACHINES_OPERATORS_AND_ASSEMBLERS => 0,
            OccupationType::ELEMENTARY_OCCUPATION => 0,
            OccupationType::UNEMPLOYED => 0,
            'Total' => 0
        ];

        foreach ($this->data['rows'] as $region => $row) {
            $this->lastFilledOutCellY++;
            $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, $region);

            $totalScore = 0;
            foreach ($row['occupation'] as $occupation => $value) {
                $cellColumn = $coordinates['occupation'][$occupation];
                $total[$occupation] += $value;

                $spreadsheet->getActiveSheet()->setCellValue($cellColumn . $this->lastFilledOutCellY, $value);
                $totalScore += $value;
            }

            $total['Total'] += $totalScore;
            $spreadsheet->getActiveSheet()->setCellValue('M' . $this->lastFilledOutCellY, $totalScore);
        }

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, 'GRAND TOTAL');
        $spreadsheet->getActiveSheet()->getStyle('A' . $this->lastFilledOutCellY)->getFont()->setBold(true);
        $spreadsheet->getActiveSheet()
            ->setCellValue('B' . $this->lastFilledOutCellY, $total[OccupationType::ARMED_FORCES_OCCUPATION]);
        $spreadsheet->getActiveSheet()
            ->setCellValue('C' . $this->lastFilledOutCellY, $total[OccupationType::MANAGERS]);
        $spreadsheet->getActiveSheet()
            ->setCellValue('D' . $this->lastFilledOutCellY, $total[OccupationType::PROFESSIONALS]);
        $spreadsheet->getActiveSheet()
            ->setCellValue('E' . $this->lastFilledOutCellY, $total[OccupationType::TECHNICAL_ASSOCIATE_PROFESSIONALS]);
        $spreadsheet->getActiveSheet()
            ->setCellValue('F' . $this->lastFilledOutCellY, $total[OccupationType::CLERICAL_SUPPORT_WORKERS]);
        $spreadsheet->getActiveSheet()
            ->setCellValue('G' . $this->lastFilledOutCellY, $total[OccupationType::SERVICE_AND_SALES_WORKERS]);
        $spreadsheet->getActiveSheet()->setCellValue(
            'H' . $this->lastFilledOutCellY,
            $total[OccupationType::SKILLED_AGRICULTURAL_FORESTRY_AND_FISHERY_WORKERS]
        );
        $spreadsheet->getActiveSheet()
            ->setCellValue('I' . $this->lastFilledOutCellY, $total[OccupationType::CRAFT_AND_RELATED_TRADES_WORKERS]);
        $spreadsheet->getActiveSheet()->setCellValue(
            'J' . $this->lastFilledOutCellY,
            $total[OccupationType::PLANT_AND_MACHINES_OPERATORS_AND_ASSEMBLERS]
        );
        $spreadsheet->getActiveSheet()
            ->setCellValue('K' . $this->lastFilledOutCellY, $total[OccupationType::ELEMENTARY_OCCUPATION]);
        $spreadsheet->getActiveSheet()
            ->setCellValue('L' . $this->lastFilledOutCellY, $total[OccupationType::UNEMPLOYED]);
        $spreadsheet->getActiveSheet()->setCellValue('M' . $this->lastFilledOutCellY, $total['Total']);

        $spreadsheet->getActiveSheet()
            ->getStyle('A10:M' . $this->lastFilledOutCellY)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()
            ->getStyle('A10:M' . $this->lastFilledOutCellY)
            ->getAlignment()->setHorizontal('center');
        $spreadsheet->getActiveSheet()
            ->getStyle('A10:M' . $this->lastFilledOutCellY)
            ->getAlignment()->setVertical('center');
        } if($sheetTitle === 'Education & Age') {
            $coordinates = [
            'education_attainment' => [
                'Post Graduate' => 'B',
                'College Graduate' => 'C',
                'College Level' => 'D',
                'Vocational' => 'E',
                'HS Graduate' => 'F',
                'HS Level' => 'G',
                'Elementary' => 'H',
                'Not Indicated' => 'I'
            ]
        ];

        $total = [
            'Post Graduate' => 0,
            'College Graduate' => 0,
            'College Level' => 0,
            'Vocational' => 0,
            'HS Graduate' => 0,
            'HS Level' => 0,
            'Elementary' => 0,
            'Not Indicated' => 0,
            '15-24' => 0,
            '25-34' => 0,
            '35-44' => 0,
            '45-54' => 0,
            '55-64' => 0,
            65 => 0,
            0 => 0,
            'Total' => 0,
            'AgeTotal' => 0,
        ];
        $ageCellsX = [
            '15-24' => 'K',
            '25-34' => 'L',
            '35-44' => 'M',
            '45-54' => 'N',
            '55-64' => 'O',
            65 => 'P',
            0 => 'Q',
        ];

        foreach ($this->data['rows'] as $region => $row) {
            $this->lastFilledOutCellY++;
            $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, $region);

            $totalScore = 0;
            $agesTotal = 0;
            foreach ($row['education_attainment'] as $educationAttainment => $value) {
                $cellColumn = $coordinates['education_attainment'][$educationAttainment];
                $total[$educationAttainment] += $value;
                $spreadsheet->getActiveSheet()->setCellValue($cellColumn . $this->lastFilledOutCellY, $value);
                $totalScore += $value;
            }

            foreach ($row['age'] as $key => $score) {
                $spreadsheet->getActiveSheet()->setCellValue(
                    $ageCellsX[$key] . $this->lastFilledOutCellY,
                    $score
                );

                $agesTotal += $score;
                $total[$key] += $score;
            }


            $total['Total'] += $totalScore;
            $total['AgeTotal'] += $agesTotal;
            $spreadsheet->getActiveSheet()->setCellValue('J' . $this->lastFilledOutCellY, $totalScore);
            $spreadsheet->getActiveSheet()->setCellValue('R' . $this->lastFilledOutCellY, $agesTotal);
        }
        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, 'GRAND TOTAL');
        $spreadsheet->getActiveSheet()->getStyle('A' . $this->lastFilledOutCellY)->getFont()->setBold(true);
        $spreadsheet->getActiveSheet()->setCellValue('B' . $this->lastFilledOutCellY, $total['Post Graduate']);
        $spreadsheet->getActiveSheet()->setCellValue('C' . $this->lastFilledOutCellY, $total['College Graduate']);
        $spreadsheet->getActiveSheet()->setCellValue('D' . $this->lastFilledOutCellY, $total['College Level']);
        $spreadsheet->getActiveSheet()->setCellValue('E' . $this->lastFilledOutCellY, $total['Vocational']);
        $spreadsheet->getActiveSheet()->setCellValue('F' . $this->lastFilledOutCellY, $total['HS Graduate']);
        $spreadsheet->getActiveSheet()->setCellValue('G' . $this->lastFilledOutCellY, $total['HS Level']);
        $spreadsheet->getActiveSheet()->setCellValue('H' . $this->lastFilledOutCellY, $total['Elementary']);
        $spreadsheet->getActiveSheet()->setCellValue('I' . $this->lastFilledOutCellY, $total['Not Indicated']);
        $spreadsheet->getActiveSheet()->setCellValue('J' . $this->lastFilledOutCellY, $total['Total']);
        $spreadsheet->getActiveSheet()->setCellValue('K' . $this->lastFilledOutCellY, $total['15-24']);
        $spreadsheet->getActiveSheet()->setCellValue('L' . $this->lastFilledOutCellY, $total['25-34']);
        $spreadsheet->getActiveSheet()->setCellValue('M' . $this->lastFilledOutCellY, $total['35-44']);
        $spreadsheet->getActiveSheet()->setCellValue('N' . $this->lastFilledOutCellY, $total['45-54']);
        $spreadsheet->getActiveSheet()->setCellValue('O' . $this->lastFilledOutCellY, $total['55-64']);
        $spreadsheet->getActiveSheet()->setCellValue('P' . $this->lastFilledOutCellY, $total[65]);
        $spreadsheet->getActiveSheet()->setCellValue('Q' . $this->lastFilledOutCellY, $total[0]);
        $spreadsheet->getActiveSheet()->setCellValue('R' . $this->lastFilledOutCellY, $total['AgeTotal']);

        $spreadsheet->getActiveSheet()->getStyle('A10:R' . $this->lastFilledOutCellY)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('A10:R' . $this->lastFilledOutCellY)
            ->getAlignment()->setHorizontal('center');
        $spreadsheet->getActiveSheet()->getStyle('A10:R' . $this->lastFilledOutCellY)
            ->getAlignment()->setVertical('center');
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);

        } else if($sheetTitle === 'Gender Civil Religion') {
           $coordinates = [
            'gender' => [
                'M' => 'B',
                'F' => 'C'
            ],
            'civil_status' => [
                'Single' => 'E',
                'Married' => 'F',
                'Widowed' => 'G',
                'Seperated' => 'H',
                'Not Indicated' => 'I'
            ],
            'religion' => [
                'Roman Catholic' => 'K',
                'INC' => 'L',
                'Islam' => 'M',
                'Others' => 'N',
                'Not Indicate' => 'O'
            ]
        ];

        $total = [
            'M' => 0,
            'F' => 0,
            'genderTotal' => 0,
            'Single' => 0,
            'Married' => 0,
            'Widowed' => 0,
            'Seperated' => 0,
            'civil_Not Indicated' => 0,
            'civilStatusTotal' => 0,
            'Roman Catholic' => 0,
            'INC' => 0,
            'Islam' => 0,
            'Others' => 0,
            'religion_Not Indicated' => 0,
            'religionTotal' => 0
        ];

        foreach ($this->data['rows'] as $region=>$row) {
            $this->lastFilledOutCellY++;
            $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, $region);

            $genderTotal = 0;
            foreach ($row['gender'] as $gender=>$value) {
                $cellColumn = $coordinates['gender'][$gender];
                $total[$gender] += $value;
                $spreadsheet->getActiveSheet()->setCellValue($cellColumn . $this->lastFilledOutCellY, $value);
                $genderTotal += $value;
            }

            $civilStatusTotal = 0;
            foreach ($row['civil_status'] as $civilStatus=>$value) {
                $cellColumn = $coordinates['civil_status'][$civilStatus];
                if ($civilStatus === 'Not Indicated') {
                    $total['civil_' . $civilStatus] += $value;
                } else {
                    $total[$civilStatus] += $value;
                }
                $spreadsheet->getActiveSheet()->setCellValue($cellColumn . $this->lastFilledOutCellY, $value);
                $civilStatusTotal += $value;
            }

            $religionTotal = 0;
            foreach ($row['religion'] as $religion=>$value) {
                $cellColumn = $coordinates['religion'][$religion];

                if ($religion === 'Not Indicated') {
                    $total['religion_' . $religion] += $value;
                } else {
                    $total[$religion] += $value;
                }
                $spreadsheet->getActiveSheet()->setCellValue($cellColumn . $this->lastFilledOutCellY, $value);
                $religionTotal += $value;
            }

            $total['genderTotal'] += $genderTotal;
            $total['civilStatusTotal'] += $civilStatusTotal;
            $total['religionTotal'] += $religionTotal;
            $spreadsheet->getActiveSheet()->setCellValue('D' . $this->lastFilledOutCellY, $genderTotal);
            $spreadsheet->getActiveSheet()->setCellValue('J' . $this->lastFilledOutCellY, $civilStatusTotal);
            $spreadsheet->getActiveSheet()->setCellValue('P' . $this->lastFilledOutCellY, $religionTotal);
        }

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, 'GRAND TOTAL');
        $spreadsheet->getActiveSheet()->getStyle('A' . $this->lastFilledOutCellY)->getFont()->setBold(true);
        $spreadsheet->getActiveSheet()->setCellValue('B' . $this->lastFilledOutCellY, $total['M']);
        $spreadsheet->getActiveSheet()->setCellValue('C' . $this->lastFilledOutCellY, $total['F']);
        $spreadsheet->getActiveSheet()->setCellValue('D' . $this->lastFilledOutCellY, $total['genderTotal']);
        $spreadsheet->getActiveSheet()->setCellValue('E' . $this->lastFilledOutCellY, $total['Single']);
        $spreadsheet->getActiveSheet()->setCellValue('F' . $this->lastFilledOutCellY, $total['Married']);
        $spreadsheet->getActiveSheet()->setCellValue('G' . $this->lastFilledOutCellY, $total['Widowed']);
        $spreadsheet->getActiveSheet()->setCellValue('H' . $this->lastFilledOutCellY, $total['Seperated']);
        $spreadsheet->getActiveSheet()->setCellValue('I' . $this->lastFilledOutCellY, $total['civil_Not Indicated']);
        $spreadsheet->getActiveSheet()->setCellValue('J' . $this->lastFilledOutCellY, $total['civilStatusTotal']);
        $spreadsheet->getActiveSheet()->setCellValue('K' . $this->lastFilledOutCellY, $total['Roman Catholic']);
        $spreadsheet->getActiveSheet()->setCellValue('L' . $this->lastFilledOutCellY, $total['INC']);
        $spreadsheet->getActiveSheet()->setCellValue('M' . $this->lastFilledOutCellY, $total['Islam']);
        $spreadsheet->getActiveSheet()->setCellValue('N' . $this->lastFilledOutCellY, $total['Others']);
        $spreadsheet->getActiveSheet()->setCellValue('O' . $this->lastFilledOutCellY, $total['religion_Not Indicated']);
        $spreadsheet->getActiveSheet()->setCellValue('P' . $this->lastFilledOutCellY, $total['religionTotal']);

        $spreadsheet->getActiveSheet()->getStyle('A10:P' . $this->lastFilledOutCellY)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('A10:P' . $this->lastFilledOutCellY)->getAlignment()->setHorizontal('center');
        $spreadsheet->getActiveSheet()->getStyle('A10:P' . $this->lastFilledOutCellY)->getAlignment()->setVertical('center');
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
 
        }
        

        return $spreadsheet;
    }

    public function header(): Spreadsheet
    {
        return $this->prepare();
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function prepare(): Spreadsheet
    {
        return $this->getSocioDemograpicPrepare();
    }


    private function getSocioDemograpicPrepare(): Spreadsheet
    {
        $spreadsheet = $this->spreadsheet;
        $sheetTitle = $spreadsheet->getActiveSheet()->getTitle();

        if($sheetTitle === 'Occupation') {
            $textAndCoordinates = [
            'M1' => 'CSD-FOR-011-002',
            'A2' => 'VPA SOCIO-DEMOGRAPHIC REPORT',
            'A3' =>  $this->regionName,
            'A4' => $this->getCurrentQuarterAndYear(),
            'A5' => "Regional Office\n" . $this->regionName,
            'B5' => 'OCCUPATION',
            'B6' => OccupationType::ARMED_FORCES_OCCUPATION,
            'C6' => OccupationType::MANAGERS,
            'D6' => OccupationType::PROFESSIONALS,
            'E6' => OccupationType::TECHNICAL_ASSOCIATE_PROFESSIONALS,
            'F6' => OccupationType::CLERICAL_SUPPORT_WORKERS,
            'G6' => OccupationType::SERVICE_AND_SALES_WORKERS,
            'H6' => OccupationType::SKILLED_AGRICULTURAL_FORESTRY_AND_FISHERY_WORKERS,
            'I6' => OccupationType::CRAFT_AND_RELATED_TRADES_WORKERS,
            'J6' => OccupationType::PLANT_AND_MACHINES_OPERATORS_AND_ASSEMBLERS,
            'K6' => OccupationType::ELEMENTARY_OCCUPATION,
            'L6' => OccupationType::UNEMPLOYED,
            'M6' => 'Total',
        ];
        $mergesCoordinates = [
            'A2:M2', 'A4:M4', 'A3:M3', 'A5:A10', 'B5:M5', 'B6:B10', 'C6:C10', 'D6:D10', 'E6:E10',
            'F6:F10', 'G6:G10', 'H6:H10', 'I6:I10', 'J6:J10', 'K6:K10', 'L6:L10', 'M6:M10'
        ];
        $boldCoordinates = ['A1:M10'];
        $verticalAlignedCoordinates = ['A1:M10' => 'center'];
        $horizontalAlignedCoordinates = ['A1:M10' => 'center'];
        $adjustedColumnWidthCoordinates = ['A' => 40];
        $outlineBorderThinCoordinates = [
            'A5:A10', 'B5:M5', 'B6:D6', 'D6:F6', 'G6:G10', 'H6:H10', 'I6:I10', 'J6:J10',
            'K6:K10', 'L6:L10', 'M6:M10', 'B7:B10', 'C7:C10', 'D7:D10', 'E7:E10', 'F7:F10', 'G7:G10'
        ];
        $rotateTextCoordinates = [
            'B6:B10', 'C6:C10', 'D6:D10', 'E6:E10', 'F6:F10', 'G6:G10',
            'H6:H10', 'I6:I10', 'J6:J10', 'K6:K10', 'L6:L10', 'M6:M10'
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

        foreach ($outlineBorderThinCoordinates as $coordinate) {
            $spreadsheet->getActiveSheet()
                ->getStyle($coordinate)
                ->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        }

        foreach ($rotateTextCoordinates as $coordinate) {
            //$spreadsheet->getActiveSheet()->getStyle($coordinate)->getAlignment()->setTextRotation(90);
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getAlignment()
                        ->setTextRotation(90) 
                        ->setWrapText(true)   
                        ->setShrinkToFit(true) 
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                        ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        }

        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getRowDimension(5)->setRowHeight(20);
        $spreadsheet->getActiveSheet()->getRowDimension(10)->setRowHeight(60);
        $spreadsheet->getActiveSheet()->getStyle('B5:P10')->getFont()->setSize(8);
        $spreadsheet->getActiveSheet()->getStyle('H6')->getAlignment()->setWrapText(true);
        }
        else if($sheetTitle === 'Education & Age') {
           $textAndCoordinates = [
            'N1' => 'CSD-FOR-011-002',
            'A2' => 'VPA SOCIO-DEMOGRAPHIC REPORT',
            'A3' =>  $this->regionName,
            'A4' => $this->getCurrentQuarterAndYear(),
            'A5' => "Regional Office\n" . $this->regionName,
            'B5' => 'EDUCATIONAL BACKGROUND',
            'K5' => 'AGE',
            'B6' => 'Post Graduate',
            'C6' => 'College Graduate',
            'D6' => 'College Level',
            'E6' => 'Vocational',
            'F6' => 'HS Graduate',
            'G6' => 'HS Level',
            'H6' => 'Elementary',
            'I6' => 'Not Indicated',
            'J6' => 'Total',
            'K6' => '15-24 years old',
            'L6' => '25-34 years old',
            'M6' => '35-44 years old',
            'N6' => '45-54 years old',
            'O6' => '55-64 years old',
            'P6' => '65 years old and Above',
            'Q6' => 'Not Indicated',
            'R6' => 'Total'
        ];
         $mergesCoordinates = [
            'A2:R2', 'A4:R4', 'A3:R3', 'A5:A10', 'B5:J5', 'K5:R5', 'B6:B10', 'C6:C10', 'D6:D10', 'E6:E10', 'F6:F10', 'G6:G10',
            'H6:H10', 'I6:I10', 'J6:J10', 'K6:K10', 'L6:L10', 'M6:M10', 'N6:N10', 'O6:O10', 'P6:P10', 'Q6:Q10', 'R6:R10'
        ];
        $boldCoordinates = ['A1:R10'];
        $verticalAlignedCoordinates = ['A1:R10' => 'center'];
        $horizontalAlignedCoordinates = ['A1:R10' => 'center'];
        $adjustedColumnWidthCoordinates = ['A' => 40, 'K' => 15, 'R' => 15];
        $outlineBorderThinCoordinates = [
            'A5:A10', 'B5:K5', 'J5:R5', 'B6:B10', 'C6:C10', 'D6:D10', 'E6:E10', 'F6:F10', 'G6:G10', 'H6:H10',
            'I6:I10', 'J6:J10', 'K6:K10', 'L6:L10', 'M6:M10', 'N6:N10', 'O6:O10', 'P6:P10', 'Q6:Q10', 'R6:R10'
        ];
        $rotateTextCoordinates = [
            'B6:B10', 'C6:C10', 'D6:D10', 'E6:E10', 'F6:F10', 'G6:G10', 'H6:H10', 'I6:I10', 'J6:J10',
            'K6:K10', 'L6:L10', 'M6:M10', 'N6:N10', 'O6:O10', 'P6:P10', 'Q6:Q10', 'R6:R10'
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

        foreach ($outlineBorderThinCoordinates as $coordinate) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)
                ->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        }

        foreach ($rotateTextCoordinates as $coordinate) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getAlignment()->setTextRotation(90);
        }

        $spreadsheet->getActiveSheet()->getRowDimension(5)->setRowHeight(20);
        $spreadsheet->getActiveSheet()->getRowDimension(10)->setRowHeight(60);
        $spreadsheet->getActiveSheet()->getStyle('B5:R10')->getFont()->setSize(8);
        } else if($sheetTitle === 'Gender Civil Religion') {
$textAndCoordinates = [
            'N1' => 'CSD-FOR-011-002',
            'A2' => 'VPA SOCIO-DEMOGRAPHIC REPORT',
            'A3' =>  $this->regionName,
            'A4' => $this->getCurrentQuarterAndYear(),
            'A5' => "Regional Office\n" . $this->regionName,
            'B5' => 'GENDER',
            'E5' => 'CIVIL STATUS',
            'K5' => 'RELIGION',
            'B6' => 'Male',
            'C6' => 'Female',
            'D6' => 'Total',
            'E6' => 'Single',
            'F6' => 'Married',
            'G6' => 'Widowed',
            'H6' => 'Seperated',
            'I6' => 'Not Indicated',
            'J6' => 'Total',
            'K6' => 'Roman Catholic',
            'L6' => 'INC',
            'M6' => 'Islam',
            'N6' => 'Others',
            'O6' => 'Not Indicated',
            'P6' => 'Total'
        ];
        $mergesCoordinates = [
            'A2:P2',  'A4:P4','A3:P3', 'A5:A10', 'B5:D5', 'E5:J5', 'K5:P5', 'B6:B10', 'C6:C10', 'D6:D10', 'E6:E10', 'F6:F10', 'G6:G10',
            'H6:H10', 'I6:I10', 'J6:J10', 'K6:K10', 'L6:L10', 'M6:M10', 'N6:N10', 'O6:O10', 'P6:P10'
        ];
        $boldCoordinates = ['A1:P10'];
        $verticalAlignedCoordinates = ['A1:P10' => 'center'];
        $horizontalAlignedCoordinates = ['A1:P10' => 'center'];
        $adjustedColumnWidthCoordinates = ['A' => 40];
        $outlineBorderThinCoordinates = [
            'A5:A10', 'B5:D5', 'E5:J5', 'K5:P5', 'B6:B10', 'C6:C10', 'D6:D10', 'E6:E10', 'F6:F10', 'G6:G10',
            'H6:H10', 'I6:I10', 'J6:J10', 'K6:K10', 'L6:L10', 'M6:M10', 'N6:N10', 'O6:O10', 'P6:P10'
        ];
        $rotateTextCoordinates = [
            'B6:B10', 'C6:C10', 'D6:D10', 'E6:E10', 'F6:F10', 'G6:G10', 'H6:H10', 'I6:I10',
            'J6:J10', 'K6:K10', 'L6:L10', 'M6:M10', 'N6:N10', 'O6:O10', 'P6:P10'
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

        foreach ($outlineBorderThinCoordinates as $coordinate) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        }

        foreach ($rotateTextCoordinates as $coordinate) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getAlignment()->setTextRotation(90);
        }

        $spreadsheet->getActiveSheet()->getRowDimension(5)->setRowHeight(20);
        $spreadsheet->getActiveSheet()->getRowDimension(10)->setRowHeight(60);
        $spreadsheet->getActiveSheet()->getStyle('B5:P10')->getFont()->setSize(8);
        }
        

        return $spreadsheet;
    }

  private function getData(array $data): array
{
    $result = $this->service->getConsolidatedSocioDemographic($data['region_id'], $data['quarter_id'] ?? 0);


    $this->regionName = $this->service->getRegionName($data['region_id']);

    return ['rows' => $result['data'] ?? []];
}
    
    private function getCurrentQuarterAndYear(): string {
 
	    $year = date('Y');
	    $quarter = ceil(date('n') / 3);
	    $qtr = $this->quarterId;
	
	    $quarters = $this->quartersRepository->find($qtr);
            if ($quarters === null) {
                return [];
            }
            $qtrNames = ["first", "second","third","fourth"];
	    $qtrIdx = array_search(strtolower($quarters->getName()),$qtrNames) + 1;
	    
	    return "As of Q{$qtrIdx} {$quarters->getYear()}";
    }
}
