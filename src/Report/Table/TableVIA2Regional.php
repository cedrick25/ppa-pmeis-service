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
use App\Service\Volunteerism\SpecialAssignment;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TableVIA2Regional implements Form
{
    private const TABLE_NAME = "TableVIA2Regional";


    public function __construct(
        private SpecialAssignment           $service,
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
            "A6:F" . (9 + count($this->fieldOffices))
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
                'SPECIAL_ASSIGNMENT' => [
                    'national'     => 0,
                    'regional'     => 0,
                    'field_office' => 0,
                ],
                'MISCELLANEOUS_ACTIVITIES' => 0,
                'personnelInvolved' => 0,
            ];

            foreach ($this->fieldOffices as $k => $v) {
                $index = ($ctr + $k);

                $activities = [
                    'SPECIAL_ASSIGNMENT' => [
                        'national'     => 0,
                        'regional'     => 0,
                        'field_office' => 0,
                    ],
                    'MISCELLANEOUS_ACTIVITIES' => 0,
                    'personnelInvolved' => 0,
                ];

                if ($result['rows']) {
                    if ($result['rows'][$v->getFieldOfficeId()]) {
                        foreach ($result['rows'][$v->getFieldOfficeId()]['SPECIAL_ASSIGNMENT'] as $category => $v1) {
                            foreach($v1 as $activity) {
                                $activities['SPECIAL_ASSIGNMENT'][$category] += count($activity['personnelInvolved']);
                                $total['SPECIAL_ASSIGNMENT'][$category] += count($activity['personnelInvolved']);
                                $activities['personnelInvolved'] += count($activity['personnelInvolved']);
                                $total['personnelInvolved'] += count($activity['personnelInvolved']);
                            }
                        }
                        foreach ($result['rows'][$v->getFieldOfficeId()]['MISCELLANEOUS_ACTIVITIES'] as $activity) {
                            $activities['MISCELLANEOUS_ACTIVITIES'] += count($activity['personnelInvolved']);
                            $total['MISCELLANEOUS_ACTIVITIES'] += count($activity['personnelInvolved']);
                            $activities['personnelInvolved'] += count($activity['personnelInvolved']);
                            $total['personnelInvolved'] += count($activity['personnelInvolved']);
                        }
                    }
                }

                $spreadsheet->getActiveSheet()->setCellValue('A' . $index, $v->getName());
                $spreadsheet->getActiveSheet()->setCellValue('B' . $index, $activities['SPECIAL_ASSIGNMENT']['national']);
                $spreadsheet->getActiveSheet()->setCellValue('C' . $index, $activities['SPECIAL_ASSIGNMENT']['regional']);
                $spreadsheet->getActiveSheet()->setCellValue('D' . $index, $activities['SPECIAL_ASSIGNMENT']['field_office']);
                $spreadsheet->getActiveSheet()->setCellValue('E' . $index, $activities['MISCELLANEOUS_ACTIVITIES']);
                $spreadsheet->getActiveSheet()->setCellValue('F' . $index, $activities['personnelInvolved']);
            }

            $totalIndex = $ctr + count($this->fieldOffices);

            $spreadsheet->getActiveSheet()->setCellValue('A' . $totalIndex, 'TOTAL');
            $spreadsheet->getActiveSheet()->setCellValue('B' . $totalIndex, $total['SPECIAL_ASSIGNMENT']['national']);
            $spreadsheet->getActiveSheet()->setCellValue('C' . $totalIndex, $total['SPECIAL_ASSIGNMENT']['regional']);
            $spreadsheet->getActiveSheet()->setCellValue('D' . $totalIndex, $total['SPECIAL_ASSIGNMENT']['field_office']);
            $spreadsheet->getActiveSheet()->setCellValue('E' . $totalIndex, $total['MISCELLANEOUS_ACTIVITIES']);
            $spreadsheet->getActiveSheet()->setCellValue('F' . $totalIndex, $total['personnelInvolved']);
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
            'A4' => 'VI. SUPPORT FUNCTION',

            'A5' => 'Table VI.A.2. SPECIAL ASSISGNMENTS/ MISC. ACTIVITIES',

            'A6' => 'FIELD OFFICES',
            'B6' => 'NUMBER OF',

            'B7' => 'SPECIAL ASSIGNMENT',
            'B8' => 'NATIONAL',
            'C8' => 'REGIONAL',
            'D8' => 'LOCAL',

            'E7' => 'MISCELLANEOUS',
            'E8' => 'ACTIVITIES',

            'F6' => 'NO. OF PERSONNEL',
            'F7' => 'INVOLVED',
            'F8' => '(Head count only)',
        ];

        $mergesCoordinates = [
            'A1:F1',
            'A2:F2',
            'A3:F3', 
            'A4:F4', 
            'A5:F5', 

            'A6:A8', 
            'B6:E6', 

            'B7:D7', 
        ];

        $boldCoordinates = [
            'A1:F8', 
        ];

        $verticalAlignedCoordinates = [
            'A1:F3' => 'center',
            'A6:F8' => 'center',
        ];

        $horizontalAlignedCoordinates = [
            'A1:F3' => 'center',
            'A6:F8' => 'center',
        ];

        $adjustedColumnWidthCoordinates = [
            'A' => 35, 
            'B' => 15,
            'C' => 15,
            'D' => 15,
            'E' => 15,
            'F' => 15,
        ];

        $wrappedTextCoordinates = [
            // 
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