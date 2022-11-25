<?php

namespace App\Report\Table;

use App\Entity\Quarters;
use App\Entity\Regions;
use App\Repository\ClientSessionsRepository;
use App\Repository\FieldOfficesRepository;
use App\Repository\QuartersRepository;
use App\Repository\RegionsRepository;
use App\Service\TherapeuticCommunity\SessionsInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TableIA8SummaryFormRegional implements Form
{
    private const TABLE_NAME = "TableIA8SummaryFormRegional";


    public function __construct(
        private FieldOfficesRepository       $fieldOfficesRepository,
        private RegionsRepository           $regionsRepository,
        private QuartersRepository          $quartersRepository,
        private ClientSessionsRepository    $clientSessionsRepository,
        private SessionsInterface           $service,
        private array                       $data = [],
        private ?Regions                    $region = null,
        private ?Quarters                   $quarter = null,
        private int                         $lastFilledOutCellY = 10,
    ){}

    public function supports(string $tableName): bool
    {
        return self::TABLE_NAME === $tableName;
    }

    public function generate(array $data): BinaryFileResponse
    {

        $regionId = intval($data['region_id']);
        $quarterId = intval($data['quarter_id']);

        $this->region = $this->regionsRepository->find($regionId);
        $this->quarter = $this->quartersRepository->find($quarterId);

        $this->data = $this->getData($regionId);

        $spreadsheet = $this->footer();
        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");

        $filePath = $_ENV['XLSX_PATH_FILE'] . self::TABLE_NAME . "-" . time() . ".xlsx";
        $writer->save($filePath);

        return new BinaryFileResponse($filePath);
    }

    public function header(): Spreadsheet
    {
        $spreadsheet = $this->prepare();

        $spreadsheet->getActiveSheet()->getStyle('A8:U10')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $spreadsheet;
    }

    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();

        foreach ($this->data as $fieldOffice=>$data) {
            $this->lastFilledOutCellY++;

            $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, $fieldOffice);
            $spreadsheet->getActiveSheet()->setCellValue('B' . $this->lastFilledOutCellY, $data['gender']['F']);
            $spreadsheet->getActiveSheet()->setCellValue('C' . $this->lastFilledOutCellY, $data['gender']['M']);
            $spreadsheet->getActiveSheet()->setCellValue('D' . $this->lastFilledOutCellY, $data['gender']['total']);
            $spreadsheet->getActiveSheet()->setCellValue('E' . $this->lastFilledOutCellY, $data['offense_category']['DO']);
            $spreadsheet->getActiveSheet()->setCellValue('F' . $this->lastFilledOutCellY, $data['offense_category']['NDO']);
            $spreadsheet->getActiveSheet()->setCellValue('G' . $this->lastFilledOutCellY, $data['offense_category']['total']);
            $spreadsheet->getActiveSheet()->setCellValue('H' . $this->lastFilledOutCellY, $data['is_pwd']);
            $spreadsheet->getActiveSheet()->setCellValue('I' . $this->lastFilledOutCellY, $data['is_senior_citizen']);
            $spreadsheet->getActiveSheet()->setCellValue('J' . $this->lastFilledOutCellY, $data['Petitioners']['prep']);
            $spreadsheet->getActiveSheet()->setCellValue('K' . $this->lastFilledOutCellY, $data['Petitioners']['I']);
            $spreadsheet->getActiveSheet()->setCellValue('L' . $this->lastFilledOutCellY, $data['Petitioners']['II']);
            $spreadsheet->getActiveSheet()->setCellValue('M' . $this->lastFilledOutCellY, $data['Petitioners']['III']);
            $spreadsheet->getActiveSheet()->setCellValue('N' . $this->lastFilledOutCellY, $data['Petitioners']['IV']);
            $spreadsheet->getActiveSheet()->setCellValue('O' . $this->lastFilledOutCellY, $data['Petitioners']['total']);
            $spreadsheet->getActiveSheet()->setCellValue('P' . $this->lastFilledOutCellY, $data['Terminated']['prep']);
            $spreadsheet->getActiveSheet()->setCellValue('Q' . $this->lastFilledOutCellY, $data['Terminated']['I']);
            $spreadsheet->getActiveSheet()->setCellValue('R' . $this->lastFilledOutCellY, $data['Terminated']['II']);
            $spreadsheet->getActiveSheet()->setCellValue('S' . $this->lastFilledOutCellY, $data['Terminated']['III']);
            $spreadsheet->getActiveSheet()->setCellValue('T' . $this->lastFilledOutCellY, $data['Terminated']['IV']);
            $spreadsheet->getActiveSheet()->setCellValue('U' . $this->lastFilledOutCellY, $data['Terminated']['total']);
            $spreadsheet->getActiveSheet()
                ->getStyle('A' . $this->lastFilledOutCellY . ':U' . $this->lastFilledOutCellY)
                ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
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
        $spreadsheet = new Spreadsheet();
        $textAndCoordinates = [
            'S1' => 'REGIONAL OFFICE IQPR CONSOLIDATION FORM - PPA-PLD-FR-002',
            'A2' => 'FIELD OFFICE ' . $this->region->getName(),
            'A3' => 'IQPR SUMMARY FORM',
            'A4' => $this->quarter->getName() . ' QTR, ' . $this->quarter->getYear(),
            'A5' => 'I.    PROGRAM IMPLEMENTATION',
            'A6' => 'A.1   Therapeutic Community Ladderized Program (TCLP)',
            'A7' => "Table I.A.2-6  NUMBER OF CLIENTS' INVOLVEMENT BY PHASE",
            'A8' => 'FIELD OFFICES',
            'B8' => 'T    O    T    A    L          N    U    M    B    E    R',
            'B9' => 'SEX',
            'B10' => 'F',
            'C10' => 'M',
            'D10' => 'Total',
            'D9' => 'OFFENSE CATEGORY',
            'E10' => 'DO',
            'F10' => 'NDO',
            'G10' => 'Total',
            'H9' => 'PWD',
            'I9' => 'SC',
            'J9' => 'PETITIONERS',
            'P9' => 'TERMINATED',
            'J10' => 'PREP',
            'K10' => 'PH I',
            'L10' => 'PH II',
            'M10' => 'PH III',
            'N10' => 'PH IV',
            'O10' => 'TOTAL',
            'P10' => 'PREP',
            'Q10' => 'PH I',
            'R10' => 'PH II',
            'S10' => 'PH III',
            'T10' => 'PH IV',
            'U10' => 'TOTAL',
        ];

        $mergesCoordinates = [
            'A8:A10', 'B8:U8', 'B9:D9', 'E9:G9', 'H9:H10', 'I9:I10', 'J9:O9', 'P9:U9'
        ];

        $boldCoordinates = ['A8:U11'];

        $verticalAlignedCoordinates = ['A8:U11' => 'center'];
        $horizontalAlignedCoordinates = ['A8:U11' => 'center'];

        $adjustedColumnWidthCoordinates = ['A' => 30];

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

        return $spreadsheet;
    }

    private function getData(int $regionId): array
    {
        $data = [];
        $fieldOffices = $this->fieldOfficesRepository->findBy(['regionId' => $regionId]);

        foreach ($fieldOffices as $fieldOffice) {
            $part1s = $this->service->getTCA1Part1($this->quarter, $fieldOffice->getFieldOfficeId());

            if (empty($part1s)) {
                continue;
            }

            $initialPhaseValues = ['prep' => 0, 'I' => 0, 'II' => 0, 'III' => 0, 'IV' => 0, 'total' => 0];
            $initialValues = [
                'gender' => [
                    'F' => 0,
                    'M' => 0,
                    'total' => 0,
                ],
                'offense_category' => [
                    'DO' => 0,
                    'NDO' => 0,
                    'total' => 0,
                ],
                'is_pwd' => 0,
                'is_senior_citizen' => 0,
                'Petitioners' => $initialPhaseValues,
                'Terminated' => $initialPhaseValues,
            ];

            $sessionIds = [];

            foreach ($part1s as $part1) {
                $sessionIds[] = $part1['session_id'];
            }

            $clientSessions = $this->clientSessionsRepository->findBySessionIds($sessionIds);

            foreach ($clientSessions as $clientSession) {
                $clientType = $clientSession['client_type'];

                if ('Petitioners' !== $clientType && 'Terminated' !== $clientType) {
                    continue;
                }

                $initialValues['gender'][$clientSession['gender']]++;
                $initialValues['offense_category'][$clientSession['offense_category']]++;
                $initialValues[$clientType][$clientSession['phase']]++;
                $initialValues[$clientType]['total']++;

                if (intval($clientSession['is_pwd'])) {
                    $initialValues['is_pwd']++;
                }

                if (intval($clientSession['is_senior_citizen'])) {
                    $initialValues['is_senior_citizen']++;
                }
            }

            $initialValues['gender']['total'] = $initialValues['gender']['M'] + $initialValues['gender']['F' .
                ''];
            $initialValues['offense_category']['total'] = $initialValues['offense_category']['DO'] + $initialValues['offense_category']['NDO'];

            if ($initialValues['gender']['total'] == 0) {
                continue;
            }

            $data[$fieldOffice->getName()] = $initialValues;
        }


        return $data;
    }
}