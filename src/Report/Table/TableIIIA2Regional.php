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

class TableIIIA2Regional implements Form
{
    private const TABLE_NAME = "TableIIIA2Regional";


    public function __construct(
        private SocialMarketing             $service,
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
            "A6:E" . (9 + count($this->fieldOffices))
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

            $totalPao = 0;
            $totalPaoParticipants = 0;
            $totalOthers = 0;
            $totalOthersParticipants = 0;

            foreach ($this->fieldOffices as $k => $v) {
                $index = ($ctr + $k);

                $pao = 0;
                $paoParticipants = 0;
                $others = 0;
                $othersParticipants = 0;
        
                if ($result['rows']) {
                    if ($result['rows'][$v->getFieldOfficeId()]) {
                        foreach ($result['rows'][$v->getFieldOfficeId()] as $v1) {
                            switch($v1['social_marketing_activity_id']) {
                                case 5:
                                    $pao++;
                                    $totalPao++;
            
                                    foreach ($v1['participants'] as $participant) {
                                        $paoParticipants += $participant['no'];
                                        $totalPaoParticipants += $participant['no'];
                                    }
            
                                    break;
                                case 6:
                                    $others++;
                                    $totalOthers++;
            
                                    foreach ($v1['participants'] as $participant) {
                                        $othersParticipants += $participant['no'];
                                        $totalOthersParticipants += $participant['no'];
                                    }
            
                                    break;
                            }
                        }
                    }
                }

                $spreadsheet->getActiveSheet()->setCellValue('A' . $index, $v->getName());
                $spreadsheet->getActiveSheet()->setCellValue('B' . $index, $pao);
                $spreadsheet->getActiveSheet()->setCellValue('C' . $index, $paoParticipants);
                $spreadsheet->getActiveSheet()->setCellValue('D' . $index, $others);
                $spreadsheet->getActiveSheet()->setCellValue('E' . $index, $othersParticipants);
            }

            $totalIndex = $ctr + count($this->fieldOffices);

            $spreadsheet->getActiveSheet()->setCellValue('A' . $totalIndex, 'TOTAL');
            $spreadsheet->getActiveSheet()->setCellValue('B' . $totalIndex, $totalPao);
            $spreadsheet->getActiveSheet()->setCellValue('C' . $totalIndex, $totalPaoParticipants);
            $spreadsheet->getActiveSheet()->setCellValue('D' . $totalIndex, $totalOthers);
            $spreadsheet->getActiveSheet()->setCellValue('E' . $totalIndex, $totalOthersParticipants);
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

            'A5' => 'III.A.2. Meetings/ Participation in POC, etc.',

            'A6' => 'Field Offices',
            'B6' => 'NUMBER OF',

            'B7' => 'POC, CADAC, MSEC, DDB, etc.',
            'B8' => 'Activities Conducted / Attended',
            'C8' => 'Participants',

            'D7' => 'Other Significant Events',
            'D8' => 'Activities Conducted / Attended',
            'E8' => 'Participants',
        ];

        $mergesCoordinates = [
            'A1:E1',
            'A2:E2',
            'A3:E3', 
            'A4:E4', 

            'A5:E5', 
            'A6:A8', 
            'B6:E6', 
            'B7:C7', 
            'D7:E7', 
        ];

        $boldCoordinates = [
            'A1:A8', 

            'A5:J5', 
            'A6:E8', 
        ];

        $verticalAlignedCoordinates = [
            'A1:E1'  => 'center', 
            'A2:E2'  => 'center', 
            'A3:E3'  => 'center', 

            'A6:E8' => 'center',
        ];

        $horizontalAlignedCoordinates = [
            'A1:E1'  => 'center', 
            'A2:E2'  => 'center', 
            'A3:E3'  => 'center', 

            'A6:E8' => 'center',
        ];

        $adjustedColumnWidthCoordinates = [
            'A' => 35, 
            'B' => 15,
            'C' => 15,
            'D' => 15,
            'E' => 15,
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

        $result = [];

        foreach ($this->fieldOffices as $fieldOffice) {
            $res = $this->service->getReport(
                $data['quarter_id'],
                $fieldOffice->getFieldOfficeId(),
                'MEETINGS_PARTICIPATIONS',
            );

            $result[$fieldOffice->getFieldOfficeId()] = $res['data'] ?? [];
        }

        return ['rows' => $result];
    }
}