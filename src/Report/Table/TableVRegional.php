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
use App\Service\Volunteerism\ProgramMaterialsDevelopment;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TableVRegional implements Form
{
    private const TABLE_NAME = "TableVRegional";


    public function __construct(
        private ProgramMaterialsDevelopment $service,
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

        $count = 0;

        $result = $this->data;

        if ($this->fieldOffices) {
            $ctr = 9;

            $total = [
                'Materials/Session Plans Developed'   => [
                    'TC'      => 0,
                    'RJ'      => 0,
                    'VPA'     => 0,
                    'GAD'     => 0,
                    'OTHERS'  => 0,
                ],
                'Materials Reproduced/Distributed' => [
                    'TC'      => 0,
                    'RJ'      => 0,
                    'VPA'     => 0,
                    'GAD'     => 0,
                    'OTHERS'  => 0,
                ],
            ];

            foreach ($this->fieldOffices as $k => $v) {
                $index = ($ctr + $k);

                $pmd = [
                    'Materials/Session Plans Developed'   => [
                        'TC'      => 0,
                        'RJ'      => 0,
                        'VPA'     => 0,
                        'GAD'     => 0,
                        'OTHERS'  => 0,
                    ],
                    'Materials Reproduced/Distributed' => [
                        'TC'      => 0,
                        'RJ'      => 0,
                        'VPA'     => 0,
                        'GAD'     => 0,
                        'OTHERS'  => 0,
                    ],
                ];

                if ($result['rows']) {
                    if ($result['rows'][$v->getFieldOfficeId()]) {
                        foreach ($result['rows'][$v->getFieldOfficeId()] as $v1) {
                            $pmd[$v1['utilized_for']][$v1['program']] ++;
                            $total[$v1['utilized_for']][$v1['program']] ++;
                        }
                    }
                }

                $spreadsheet->getActiveSheet()->setCellValue('A' . $index, $v->getName());
                $spreadsheet->getActiveSheet()->setCellValue('B' . $index, $pmd['Materials/Session Plans Developed']['TC']);
                $spreadsheet->getActiveSheet()->setCellValue('C' . $index, $pmd['Materials/Session Plans Developed']['RJ']);
                $spreadsheet->getActiveSheet()->setCellValue('D' . $index, $pmd['Materials/Session Plans Developed']['VPA']);
                $spreadsheet->getActiveSheet()->setCellValue('E' . $index, $pmd['Materials/Session Plans Developed']['GAD']);
                $spreadsheet->getActiveSheet()->setCellValue('F' . $index, $pmd['Materials/Session Plans Developed']['OTHERS']);
                $spreadsheet->getActiveSheet()->setCellValue('G' . $index, $pmd['Materials Reproduced/Distributed']['TC']);
                $spreadsheet->getActiveSheet()->setCellValue('H' . $index, $pmd['Materials Reproduced/Distributed']['RJ']);
                $spreadsheet->getActiveSheet()->setCellValue('I' . $index, $pmd['Materials Reproduced/Distributed']['VPA']);
                $spreadsheet->getActiveSheet()->setCellValue('J' . $index, $pmd['Materials Reproduced/Distributed']['GAD']);
                $spreadsheet->getActiveSheet()->setCellValue('K' . $index, $pmd['Materials Reproduced/Distributed']['OTHERS']);
            }

            $totalIndex = $ctr + count($this->fieldOffices);

            $spreadsheet->getActiveSheet()->setCellValue('A' . $totalIndex, 'TOTAL');
            $spreadsheet->getActiveSheet()->setCellValue('B' . $totalIndex, $total['Materials/Session Plans Developed']['TC']);
            $spreadsheet->getActiveSheet()->setCellValue('C' . $totalIndex, $total['Materials/Session Plans Developed']['RJ']);
            $spreadsheet->getActiveSheet()->setCellValue('D' . $totalIndex, $total['Materials/Session Plans Developed']['VPA']);
            $spreadsheet->getActiveSheet()->setCellValue('E' . $totalIndex, $total['Materials/Session Plans Developed']['GAD']);
            $spreadsheet->getActiveSheet()->setCellValue('F' . $totalIndex, $total['Materials/Session Plans Developed']['OTHERS']);
            $spreadsheet->getActiveSheet()->setCellValue('G' . $totalIndex, $total['Materials Reproduced/Distributed']['TC']);
            $spreadsheet->getActiveSheet()->setCellValue('H' . $totalIndex, $total['Materials Reproduced/Distributed']['RJ']);
            $spreadsheet->getActiveSheet()->setCellValue('I' . $totalIndex, $total['Materials Reproduced/Distributed']['VPA']);
            $spreadsheet->getActiveSheet()->setCellValue('J' . $totalIndex, $total['Materials Reproduced/Distributed']['GAD']);
            $spreadsheet->getActiveSheet()->setCellValue('K' . $totalIndex, $total['Materials Reproduced/Distributed']['OTHERS']);
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
            'A4' => 'V. PROGRAM MATERIALS AND DEVELOPMENT',

            'A5' => '',

            'A6' => 'FIELD OFFICES',
            'B6' => 'NUMBER OF',

            'B7' => 'MATERIALS/ SESSIONS PLANS DEVELOPED',
            'B8' => 'TC',
            'C8' => 'RJ',
            'D8' => 'VPA',
            'E8' => 'GAD',
            'F8' => 'OTHERS',

            'G7' => 'MATERIALS REPRODUCED/ DISTRIBUTED',
            'G8' => 'TC',
            'H8' => 'RJ',
            'I8' => 'VPA',
            'J8' => 'GAD',
            'K8' => 'OTHERS',
        ];

        $mergesCoordinates = [
            'A1:K1',
            'A2:K2',
            'A3:K3', 
            'A4:K4', 

            'A5:K5', 
            'A6:A8', 
            'B6:K6', 
            'B7:F7', 
            'G7:K7', 
        ];

        $boldCoordinates = [
            'A1:K8', 
        ];

        $verticalAlignedCoordinates = [
            'A1:K3' => 'center',
            'A5:K8' => 'center',
        ];

        $horizontalAlignedCoordinates = [
            'A1:K3' => 'center',
            'A5:K8' => 'center',
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
            // 'K6',
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
            $res = $this->service->getIdSupportReport(
                $data['quarter_id'],
                $fieldOffice->getFieldOfficeId(),
            );

            $result[$fieldOffice->getFieldOfficeId()] = $res['data'] ?? [];
        }

        return ['rows' => $result];
    }
}