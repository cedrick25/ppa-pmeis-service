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

class TableIIIA1Regional implements Form
{
    private const TABLE_NAME = "TableIIIA1Regional";


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
            "A6:H" . (9 + count($this->fieldOffices))
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
            $totals = [
                'B' => 0,
                'C' => 0,
                'D' => 0,
                'E' => 0,
                'F' => 0,
                'G' => 0,
                'H' => 0,
            ];

            foreach ($this->fieldOffices as $k => $v) {
                $index = ($ctr + $k);

                $result = $this->data;

                $fora = 0;
                $tv = 0;

                if ($result['rows'][$k]) {
                    foreach ($result['rows'][$k][0] as $v1) {
                        switch($v1['social_marketing_activity_id']) {
                            case 1:
                                $fora++;
                                break;
                            case 2:
                                $tv++;
                                break;
                        }
                    }
                }

                $spreadsheet->getActiveSheet()->setCellValue('A' . $index, $v->getName());
                $spreadsheet->getActiveSheet()->setCellValue('B' . $index, $fora);
                $spreadsheet->getActiveSheet()->setCellValue('C' . $index, $fora);
                $spreadsheet->getActiveSheet()->setCellValue('D' . $index, $fora);
                $spreadsheet->getActiveSheet()->setCellValue('E' . $index, $tv);
                $spreadsheet->getActiveSheet()->setCellValue('F' . $index, $tv);
                $spreadsheet->getActiveSheet()->setCellValue('G' . $index, $tv);
                $spreadsheet->getActiveSheet()->setCellValue('H' . $index, $fora + $tv);

                $totals['B'] += $fora;
                $totals['C'] += $fora;
                $totals['D'] += $fora;
                $totals['E'] += $tv;
                $totals['F'] += $tv;
                $totals['G'] += $tv;
                $totals['H'] += ($fora + $tv);
            }

            $totalIndex = $ctr + count($this->fieldOffices);

            $spreadsheet->getActiveSheet()->setCellValue('A' . $totalIndex, 'TOTAL');
            $spreadsheet->getActiveSheet()->setCellValue('B' . $totalIndex, $totals['B']);
            $spreadsheet->getActiveSheet()->setCellValue('C' . $totalIndex, $totals['C']);
            $spreadsheet->getActiveSheet()->setCellValue('D' . $totalIndex, $totals['D']);
            $spreadsheet->getActiveSheet()->setCellValue('E' . $totalIndex, $totals['E']);
            $spreadsheet->getActiveSheet()->setCellValue('F' . $totalIndex, $totals['F']);
            $spreadsheet->getActiveSheet()->setCellValue('G' . $totalIndex, $totals['G']);
            $spreadsheet->getActiveSheet()->setCellValue('H' . $totalIndex, $totals['H']);
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

            'A5' => 'A.1. INFORMATION DISSEMINATION',

            'A6' => 'FIELD OFFICES',
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
        $this->region   = $this->regionsRepository->find($data['region_id']);
        $this->quarters = $this->quartersRepository->find($data['quarter_id']);

        $this->fieldOffices = $this->fieldOfficesRepository->findBy(['regionId' => $data['region_id']]);

        $rows = [];
        foreach ($this->fieldOffices as $v) {
            $result = $this->service->getReport(
                $data['quarter_id'],
                $v->getFieldOfficeId(),
                'INFORMATION_DISSEMINATION',
            );

            $rows[] = array_values($result['data'] ?? []);
        }


        return ['rows' => $rows];
    }

}