<?php

namespace App\Report\Table;

use App\Entity\FieldOffices;
use App\Entity\Quarters;
use App\Entity\Regions;
use App\Repository\ClientSessionsRepository;
use App\Repository\FieldOfficesRepository;
use App\Repository\QuartersRepository;
use App\Repository\RegionsRepository;
use App\Repository\SessionsRepository;
use App\Service\TherapeuticCommunity\QuartersInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TableIA26SummaryFormRegional implements Form
{
    private const TABLE_NAME = "TableIA26SummaryFormRegional";


    public function __construct(
        private FieldOfficesRepository       $fieldOfficesRepository,
        private RegionsRepository           $regionsRepository,
        private QuartersRepository          $quartersRepository,
        private ClientSessionsRepository    $clientSessionsRepository,
        private QuartersInterface           $service,
        private array                       $data = [],
        private array                       $sessionIds = [],
        private ?Regions                    $region = null,
        private ?Quarters                   $quarter = null,
        private int                         $lastFilledOutCellY = 11,
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

        $this->data = $this->getData($quarterId, $regionId);

        $spreadsheet = $this->footer();
        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");

        $filePath = $_ENV['XLSX_PATH_FILE'] . self::TABLE_NAME . "-" . time() . ".xlsx";
        $writer->save($filePath);

        return new BinaryFileResponse($filePath);
    }

    public function header(): Spreadsheet
    {
        $spreadsheet = $this->prepare();

        $spreadsheet->getActiveSheet()->getStyle('A8:AW11')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $spreadsheet;
    }

    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();
        $cells = [];

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
            $spreadsheet->getActiveSheet()->setCellValue('J' . $this->lastFilledOutCellY,0);
            $spreadsheet->getActiveSheet()->setCellValue('K' . $this->lastFilledOutCellY,0);
            $spreadsheet->getActiveSheet()->setCellValue('L' . $this->lastFilledOutCellY,0);
            $spreadsheet->getActiveSheet()->setCellValue('M' . $this->lastFilledOutCellY,0);
            $spreadsheet->getActiveSheet()->setCellValue('N' . $this->lastFilledOutCellY,0);
            $spreadsheet->getActiveSheet()->setCellValue('O' . $this->lastFilledOutCellY,0);
            $spreadsheet->getActiveSheet()->setCellValue('P' . $this->lastFilledOutCellY,0);
            $spreadsheet->getActiveSheet()->setCellValue('Q' . $this->lastFilledOutCellY, $data['I']['probationers']);
            $spreadsheet->getActiveSheet()->setCellValue('R' . $this->lastFilledOutCellY, $data['I']['parolees']);
            $spreadsheet->getActiveSheet()->setCellValue('S' . $this->lastFilledOutCellY, $data['I']['pardonees']);
            $spreadsheet->getActiveSheet()->setCellValue('T' . $this->lastFilledOutCellY, $data['I']['jicl']);
            $spreadsheet->getActiveSheet()->setCellValue('U' . $this->lastFilledOutCellY, $data['I']['ftmdo']);
            $spreadsheet->getActiveSheet()->setCellValue('V' . $this->lastFilledOutCellY, $data['I']['total']);
            $spreadsheet->getActiveSheet()->setCellValue('W' . $this->lastFilledOutCellY, $data['I']['fsi']);
            $spreadsheet->getActiveSheet()->setCellValue('X' . $this->lastFilledOutCellY, $data['II']['probationers']);
            $spreadsheet->getActiveSheet()->setCellValue('Y' . $this->lastFilledOutCellY, $data['II']['parolees']);
            $spreadsheet->getActiveSheet()->setCellValue('Z' . $this->lastFilledOutCellY, $data['II']['pardonees']);
            $spreadsheet->getActiveSheet()->setCellValue('AA' . $this->lastFilledOutCellY, $data['II']['jicl']);
            $spreadsheet->getActiveSheet()->setCellValue('AB' . $this->lastFilledOutCellY, $data['II']['ftmdo']);
            $spreadsheet->getActiveSheet()->setCellValue('AC' . $this->lastFilledOutCellY, $data['II']['total']);
            $spreadsheet->getActiveSheet()->setCellValue('AD' . $this->lastFilledOutCellY, $data['II']['fsi']);
            $spreadsheet->getActiveSheet()->setCellValue('AE' . $this->lastFilledOutCellY, $data['II']['probationers']);
            $spreadsheet->getActiveSheet()->setCellValue('AF' . $this->lastFilledOutCellY, $data['II']['parolees']);
            $spreadsheet->getActiveSheet()->setCellValue('AG' . $this->lastFilledOutCellY, $data['II']['pardonees']);
            $spreadsheet->getActiveSheet()->setCellValue('AH' . $this->lastFilledOutCellY, $data['II']['jicl']);
            $spreadsheet->getActiveSheet()->setCellValue('AI' . $this->lastFilledOutCellY, $data['II']['ftmdo']);
            $spreadsheet->getActiveSheet()->setCellValue('AJ' . $this->lastFilledOutCellY, $data['II']['total']);
            $spreadsheet->getActiveSheet()->setCellValue('AK' . $this->lastFilledOutCellY, $data['II']['fsi']);
            $spreadsheet->getActiveSheet()->setCellValue('AL' . $this->lastFilledOutCellY, $data['IV']['probationers']);
            $spreadsheet->getActiveSheet()->setCellValue('AN' . $this->lastFilledOutCellY, $data['IV']['parolees']);
            $spreadsheet->getActiveSheet()->setCellValue('AP' . $this->lastFilledOutCellY, $data['IV']['pardonees']);
            $spreadsheet->getActiveSheet()->setCellValue('AR' . $this->lastFilledOutCellY, $data['IV']['jicl']);
            $spreadsheet->getActiveSheet()->setCellValue('AT' . $this->lastFilledOutCellY, $data['IV']['ftmdo']);
            $spreadsheet->getActiveSheet()->setCellValue('AV' . $this->lastFilledOutCellY, $data['IV']['total']);
            $spreadsheet->getActiveSheet()->setCellValue('AW' . $this->lastFilledOutCellY, $data['IV']['fsi']);
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
            'AW1' => 'REGIONAL OFFICE IQPR CONSOLIDATION FORM - PPA-PLD-FR-002',
            'A2' => 'FIELD OFFICE ' . $this->region->getName(),
            'A3' => 'IQPR SUMMARY FORM',
            'A4' => $this->quarter->getName() . ' QTR, ' . $this->quarter->getYear(),
            'A5' => 'I.    PROGRAM IMPLEMENTATION',
            'A6' => 'A.1   Therapeutic Community Ladderized Program (TCLP)',
            'A7' => "Table I.A.2-6  NUMBER OF CLIENTS' INVOLVEMENT BY PHASE",
            'A8' => 'FIELD OFFICES',
            'B8' => 'T    O    T    A    L          N    U    M    B    E    R',
            'B9' => 'SEX', 'D9' => 'Total', 'E9' => 'OFFENSE CATEGORY', 'G9' => 'Total', 'H9' => 'PWD', 'I9' => 'SC',
            'J9' => 'PREPARATORY PHASE', 'Q9' => 'PHASE I', 'X9' => 'PHASE II', 'AE9' => 'PHASE III', 'AL9' => 'PHASE IV', 'B10' => 'F', 'C10' => 'M',
            'E10' => 'DO', 'F10' => 'NDO', 'J10' => 'PS', 'K10' => 'PR', 'L10' => 'PD', 'M10' => 'JICL', 'N10' => 'FTMDO', 'O10' => 'TOTAL', 'P10' => 'FSI',
            'Q10' => 'PS', 'R10' => 'PR', 'S10' => 'PD', 'T10' => 'JICL', 'U10' => 'FTMDO', 'V10' => 'TOTAL', 'W10' => 'FSI', 'X10' => 'PS', 'Y10' => 'PR',
            'Z10' => 'PD', 'AA10' => 'JICL', 'AB10' => 'FTMDO', 'AC10' => 'TOTAL', 'AD10' => 'FSI', 'AE10' => 'PS', 'AF10' => 'PR', 'AG10' => 'PD',
            'AH10' => 'JICL', 'AI10' => 'FTMDO', 'AJ10' => 'TOTAL', 'AK10' => 'FSI', 'AL10' => 'PS', 'AN10' => 'PR', 'AP10' => 'PD', 'AR10' => 'JICL',
            'AT10' => 'FTMDO', 'AV10' => 'TOTAL', 'AW10' => 'FSI', 'AL11' => 'On-going', 'AM11' => 'Completed', 'AN11' => 'On-going', 'AO11' => 'Completed',
            'AP11' => 'On-going', 'AQ11' => 'Completed', 'AR11' => 'On-going', 'AS11' => 'Completed', 'AT11' => 'On-going', 'AU11' => 'Completed',
        ];

        $mergesCoordinates = [
            'B8:AW8', 'B10:B11', 'C10:C11', 'D9:D11', 'E10:E11', 'F10:F11', 'J10:J11', 'K10:K11', 'L10:L11', 'M10:M11', 'N10:N11', 'O10:O11', 'P10:P11',
            'Q10:Q11', 'R10:R11', 'S10:S11', 'T10:T11', 'U10:U11', 'V10:V11', 'W10:W11', 'X10:X11', 'Y10:Y11', 'Z10:Z11', 'AA10:AA11', 'AB10:AB11',
            'AD10:AD11', 'AE10:AE11', 'AF10:AF11', 'AG10:AG11', 'AH10:AH11', 'AI10:AI11', 'AJ10:AJ11', 'AK10:AK11',
            'AL10:AM10', 'AN10:AO10', 'AP10:AQ10', 'AR10:AS10', 'AT10:AU10', 'G9:G11', 'H9:H11', 'I9:I11', 'AC10:AC11',
            'J9:P9', 'Q9:W9', 'X9:AC9', 'AE9:AK9', 'AL9:AU9', 'AV10:AV11', 'AW10:AW11', 'E9:F9'
        ];

        $boldCoordinates = ['A8:AW11'];

        $verticalAlignedCoordinates = ['A8:AW11' => 'center'];
        $horizontalAlignedCoordinates = ['A8:AW11' => 'center'];

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

    private function getData(int $quarterId, int $regionId): array
    {
        $data = [];
        $fieldOffices = $this->fieldOfficesRepository->findBy(['regionId' => $regionId]);

        foreach ($fieldOffices as $fieldOffice) {
            $part1s = $this->service->getTCA1Part1($quarterId, $fieldOffice->getFieldOfficeId());
            $part2s = $this->service->getTCA1Part2($quarterId, $fieldOffice->getFieldOfficeId());

            if (! isset($part2s['data'])) {
                continue;
            }

            $treatmentCategoryInitialValues = ['parolees'=> 0, 'probationers'=> 0, 'pardonees'=> 0, 'jicl'=> 0, 'ftmdo'=> 0, 'total'=> 0, 'fsi'=> 0];
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
                'I' => $treatmentCategoryInitialValues,
                'II' => $treatmentCategoryInitialValues,
                'III' => $treatmentCategoryInitialValues,
                'IV' => $treatmentCategoryInitialValues,
            ];

            $part2s = $part2s['data'];
            $sessionIds = [];

            foreach ($part1s['data'] as $index=>$part1) {
                $part2 = $part2s[$index + 1];
                $sessionIds[] = $part1['session_id'];

                $parolees = intval($part2['count']['parolees']);
                $probationers = intval($part2['count']['probationers']);
                $pardonees = intval($part2['count']['pardonees']);
                $jicl = intval($part2['count']['jicl']);
                $ftmdo = intval($part2['count']['ftmdo']);
                $total = $pardonees + $probationers + $pardonees + $jicl + $ftmdo;

                $initialValues[$part1['phase_name']]['parolees'] += $parolees;
                $initialValues[$part1['phase_name']]['probationers'] += $probationers;
                $initialValues[$part1['phase_name']]['pardonees'] += $pardonees;
                $initialValues[$part1['phase_name']]['jicl'] += $jicl;
                $initialValues[$part1['phase_name']]['ftmdo'] += $ftmdo;
                $initialValues[$part1['phase_name']]['total'] += $total;
                $initialValues[$part1['phase_name']]['fsi'] += intval($part1['fsg']);
            }


            $clientSessions = $this->clientSessionsRepository->findBySessionIds($sessionIds);

            foreach ($clientSessions as $clientSession) {
                $initialValues['gender'][$clientSession['gender']]++;
                $initialValues['offense_category'][$clientSession['offense_category']]++;

                if (intval($clientSession['is_pwd'])) {
                    $initialValues['is_pwd']++;
                }

                if (intval($clientSession['is_senior_citizen'])) {
                    $initialValues['is_senior_citizen']++;
                }
            }

            $initialValues['gender']['total'] = $initialValues['gender']['M'] + $initialValues['gender']['F'];
            $initialValues['offense_category']['total'] = $initialValues['offense_category']['DO'] + $initialValues['offense_category']['NDO'];

            $data[$fieldOffice->getName()] = $initialValues;
        }


        return $data;
    }
}