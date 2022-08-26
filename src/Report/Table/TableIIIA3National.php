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

class TableIIIA3National implements Form
{
    private const TABLE_NAME = "TableIIIA3National";


    public function __construct(
        private SocialMarketing             $service,
        private RegionsRepository           $regionsRepository,
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
            "A6:I" . (9 + count($this->regions))
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

        if ($this->regions) {
            $ctr = 9;
            $totals = [
                'B' => 0,
                'C' => 0,
                'D' => 0,
                'E' => 0,
            ];

            foreach ($this->regions as $k => $v) {
                $index = ($ctr + $k);

                $result = $this->data;

                $pao = 0;
                $others = 0;
        
                if ($result['rows'][$k]) {
                    foreach ($result['rows'][$k][0] as $v1) {
                        switch($v1['social_marketing_activity_id']) {
                            case 3:
                                $pao++;
                                break;
                            case 4:
                                $others++;
                                break;
                        }
                    }
                }

                $spreadsheet->getActiveSheet()->setCellValue('A' . $index, $v->getName());
                $spreadsheet->getActiveSheet()->setCellValue('B' . $index, $pao);
                $spreadsheet->getActiveSheet()->setCellValue('C' . $index, $pao);
                $spreadsheet->getActiveSheet()->setCellValue('D' . $index, $pao);
                $spreadsheet->getActiveSheet()->setCellValue('E' . $index, $pao);

                $totals['B'] += $pao;
                $totals['C'] += $pao;
                $totals['D'] += $pao;
                $totals['E'] += $pao;
            }

            $totalIndex = $ctr + count($this->regions);

            $spreadsheet->getActiveSheet()->setCellValue('A' . $totalIndex, 'TOTAL');
            $spreadsheet->getActiveSheet()->setCellValue('B' . $totalIndex, $totals['B']);
            $spreadsheet->getActiveSheet()->setCellValue('C' . $totalIndex, $totals['C']);
            $spreadsheet->getActiveSheet()->setCellValue('D' . $totalIndex, $totals['D']);
            $spreadsheet->getActiveSheet()->setCellValue('E' . $totalIndex, $totals['E']);
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

            'A5' => 'III.A.3. Technical Assistance/ Outreach Program To Other Agencies/ Public Assistance',

            'A6' => 'REGIONAL OFFICES',
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
        $this->quarters = $this->quartersRepository->find($data['quarter_id']);
        $this->regions  = $this->regionsRepository->list();

        $rows = [];
        foreach ($this->regions as $v) {
            $result = $this->service->getReport(
                $data['quarter_id'],
                1,
                'MEETINGS_PARTICIPATIONS',
            );

            $rows[] = array_values($result['data'] ?? []);
        }


        return ['rows' => $rows];
    }

}