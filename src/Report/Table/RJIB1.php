<?php

declare(strict_types=1);

namespace App\Report\Table;

use App\Enum\SystemSettingNames;
use App\Service\RestorativeJustice\ConductProcesses;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RJIB1 implements Form
{
    private const TABLE_NAME = "RJIB1";

    public function __construct(
        private ConductProcesses    $service,
        private int                 $lastFilledOutCellY = 11,
        private array               $data = [],
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
        $this->data[SystemSettingNames::GENERATED_REPORTS_CODE] = $data[SystemSettingNames::GENERATED_REPORTS_CODE];

        $spreadsheet = $this->footer();
        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");

        $filePath = $_ENV['XLSX_PATH_FILE'] . self::TABLE_NAME . "-" . time() . ".xlsx";
        $writer->save($filePath);

        return new BinaryFileResponse($filePath);
    }

    public function footer(): Spreadsheet
    {
        $spreadsheet = $this->body();

        $this->lastFilledOutCellY++;
        $footerHeadRowNumber = $this->lastFilledOutCellY;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY,'a.  TOTAL ADJUSTED SUPERVISION CASELOAD (Refer to Table I.A.7 Supervision Caseload)');
        $spreadsheet->getActiveSheet()->setCellValue('K' . $this->lastFilledOutCellY,$this->data['footer']['adjusted_supervision_caseload']);
        $spreadsheet->getActiveSheet()->getStyle('K' . $this->lastFilledOutCellY)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('K' . $this->lastFilledOutCellY)->getAlignment()->setHorizontal('center');

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY,'b.  TOTAL NUMBER OF CLIENTS WHO HAVE UNDERGONE RJ PROCESS  (Col. 7)');
        $spreadsheet->getActiveSheet()->setCellValue('J' . $this->lastFilledOutCellY,$this->data['footer']['clients_undergone_rj_process']);
        $spreadsheet->getActiveSheet()->getStyle('J' . $this->lastFilledOutCellY)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('J' . $this->lastFilledOutCellY)->getAlignment()->setHorizontal('center');

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('C' . $this->lastFilledOutCellY,'- Active Supervision');
        $spreadsheet->getActiveSheet()->setCellValue('H' . $this->lastFilledOutCellY,$this->data['footer']['active_supervision']);
        $spreadsheet->getActiveSheet()->getStyle('H' . $this->lastFilledOutCellY)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('H' . $this->lastFilledOutCellY)->getAlignment()->setHorizontal('center');

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('C' . $this->lastFilledOutCellY,'- Petitioners');
        $spreadsheet->getActiveSheet()->setCellValue('H' . $this->lastFilledOutCellY,$this->data['footer']['petitioners']);
        $spreadsheet->getActiveSheet()->getStyle('H' . $this->lastFilledOutCellY)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('H' . $this->lastFilledOutCellY)->getAlignment()->setHorizontal('center');

        $this->lastFilledOutCellY++;
        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue(
            'C' . $this->lastFilledOutCellY,
            "Indicate before client's  name if PS, PR, PD, JICL, FTMDO. (Ex.  PS-Juan dela Cruz); PI  (in accordance with Memo on RJ Guidelines)");

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('C' . $this->lastFilledOutCellY,'RJ PROCESSES:');

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('D' . $this->lastFilledOutCellY,'1. Mediation');
        $spreadsheet->getActiveSheet()->setCellValue('L' . $this->lastFilledOutCellY,'3.  Circle of Support');

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('D' . $this->lastFilledOutCellY,'2. Conferencing');
        $spreadsheet->getActiveSheet()->setCellValue('L' . $this->lastFilledOutCellY,'4. Others (Indigenous Practices, etc.)');

        $this->lastFilledOutCellY++;
        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('B' . $this->lastFilledOutCellY,'NOTE:');
        $spreadsheet->getActiveSheet()->setCellValue('D' . $this->lastFilledOutCellY,'ATTACH ONE (1) COPY OF ATTENDANCE SHEETS OF ALL ACTIVITIES');

        $spreadsheet->getActiveSheet()->getStyle('A' . $footerHeadRowNumber . ':Q' . $this->lastFilledOutCellY)->getFont()->setBold(true);

        return $spreadsheet;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();
        $rows = ['PETITIONER' => [], 'ACTIVE_SUPERVISION' => []];

        foreach ($this->data['rows'] as $row) {
            $rows[$row['rj_group']][] = $row;
        }

        $spreadsheet = $this->buildBody($spreadsheet, $rows['ACTIVE_SUPERVISION'], 'ACTIVE_SUPERVISION');

        $this->lastFilledOutCellY++;
        $savedAsLastFilledOutCellY = $this->lastFilledOutCellY;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, 'II. PETITIONERS');
        $spreadsheet->getActiveSheet()->getStyle('A' . $this->lastFilledOutCellY)->getFont()->setBold(true);
        $spreadsheet->getActiveSheet()
            ->getStyle("A" . $this->lastFilledOutCellY . ":Q" . $this->lastFilledOutCellY)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $spreadsheet = $this->buildBody($spreadsheet, $rows['PETITIONER'], 'PETITIONER');

        $spreadsheet->getActiveSheet()->getStyle('A' . $savedAsLastFilledOutCellY .':Q' . $savedAsLastFilledOutCellY)->getAlignment()->setWrapText(false);
        $spreadsheet->getActiveSheet()->getStyle('A' . $savedAsLastFilledOutCellY .':Q' . $savedAsLastFilledOutCellY)->getAlignment()->setHorizontal('left');

        return $spreadsheet;
    }

    public function header(): Spreadsheet
    {
        $spreadsheet = $this->prepare();

        return $spreadsheet;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function prepare(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $textAndCoordinates = [
            'A1' => 'B.  RESTORATIVE JUSTICE',
            'Q1' => $this->data[SystemSettingNames::GENERATED_REPORTS_CODE],
            'A3' => 'Table I.B.1   RESTORATIVE JUSTICE (RJ) PROCESSES CONDUCTED / CLIENTS INVOLVED',
            'A5' => "CLIENT'S NAME",
            'C5' => 'SEX',
            'F5' => 'Sr. Citizen (4)',
            'G5' => 'OFFENSE',
            'H5' => 'Pre-Encounter RJ Activities',
            'K5' => 'RJ PROCESS',
            'N5' => 'RJ PLANNER (8)',
            'O5' => 'PERSONS/ INSTITUTION / STAKEHOLDERS INVOLVED (9)',
            'P5' => 'STATUS OF RJ PROCESS',
            'Q5' => 'RJ OUTCOME of the',
            'E6' => 'P',
            'P6' => '(10)',
            'Q6' => 'RESOLVED PROCESS (11)',
            'C7' => '(2)',
            'E7' => 'W',
            'H7' => '(6)',
            'K7' => '(7)',
            'O7' => '(Include  Offended Party,',
            'P7' => '(Shelved, Deferred,On-Going,',
            'Q7' => '(Restitution(R), Community Work Services (CWS), Restored Relationships (RR) and Others(O))',
            'C8' => 'F',
            'D8' => 'M',
            'E8' => 'D',
            'G8' => '(5)',
            'H8' => 'Date',
            'I8' => 'Venue',
            'J8' => 'Activity',
            'K8' => 'Date',
            'L8' => 'Venue',
            'M8' => 'Type',
            'O8' => 'VPA and other Individuals/ Groups.  Indicate before',
            'P8' => 'Completed, Agreement Reached)',
            'A9' => '(1)',
            'E9' => '(3)',
            'O9' => 'each name if OP/VPA/FM/CM).',
            'A10' => 'I.  ACTIVE SUPERVISION',
        ];

        $wrapTextCoordinates = [
            "F5:F9", "N5:N9", "O7:O9", "P7:P9", "Q7:Q9"
        ];

        $mergesCoordinates = [
            "A5:B8", "C5:D6", "F5:F9", "G5:G7", "H5:J6", "K5:M6", "N5:N9", "O5:O6", "C7:D7", "H7:J7", "K7:M7", "Q7:Q9",
            "C8:C9", "D8:D9", "G8:G9", "H8:H9", "I8:I9", "J8:J9", "K8:K9", "L8:L9", "M8:M9", "A9:B9"
        ];

        $boldCoordinates = [
            "A1:N10","Q1","O5:O6","P5:P6","Q5:Q9"
        ];

        $verticalAlignedCoordinates = ["A5:Q9" => "center"];
        $horizontalAlignedCoordinates = ["A5:Q9" => "center"];

        $adjustedColumnWidthCoordinates = [
            'C' => 3, 'D' => 3, 'E' => 3, 'F' => 7, 'G' => 10, 'H' => 8, 'I' => 20, 'J' => 12, 'K' => 8, 'L' => 20, 'M' => 10, 'N' => 9, 'O' => 35, 'P' => 35, 'Q' => 35
        ];

        $outlineBorderThinCoordinates = [
            "A5:B8","C5:D6","E5:E8","F5:F9","G5:G7","H5:J6","K5:M6","N5:N9","O5:O6","P5:P6","Q5:Q6","C7:D7","H7:J7","K7:M7","C8:C9",
            "D8:D9","G8:G9","H8:H9","I8:I9","J8:J9","K8:K9","L8:L9","M8:M9","O7:O9","P7:P9","Q7:Q9","A9:B9", "A10:C10"
        ];

        foreach ($textAndCoordinates as $coordinate => $text) {
            $spreadsheet->getActiveSheet()->setCellValue($coordinate, $text);
        }

        foreach ($wrapTextCoordinates as $coordinate) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getAlignment()->setWrapText(true);
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

        $spreadsheet->getActiveSheet()->getRowDimension(8)->setRowHeight(30);
        $spreadsheet->getActiveSheet()->getRowDimension(9)->setRowHeight(30);

        foreach ($outlineBorderThinCoordinates as $coordinate) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        }

        $spreadsheet->getActiveSheet()->getStyle("D10:Q10")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle("A11:Q11")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $spreadsheet;
    }

    /**
     * @param Spreadsheet $spreadsheet
     * @param array<string, mixed> $rows
     * @param string $groupType
     * @return Spreadsheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function buildBody(Spreadsheet $spreadsheet, array $rows, string $groupType): Spreadsheet
    {
        $rjpStatusResolvedCriteria = ['Completed', 'Agreement Reached'];

        $totalData[$groupType] = [
            'female' => 0,
            'male' => 0,
            'pwd' => 0,
            'senior_citizen' => 0,
            'rjp_type' => 0,
            'rjp_status' => [
                'resolved' => 0,
                'unresolved' => 0
            ],
            'rj_outcome' => [
                'R' => 0,
                'CWS' => 0,
                'RR' => 0,
                'O' => 0
            ]
        ];

        $rowCount = \count($rows);

        $this->data['footer']['adjusted_supervision_caseload'] += $rowCount;
        $this->data['footer']['clients_undergone_rj_process'] += $rowCount;
        

        $this->lastFilledOutCellY++;
        foreach ($rows as $row) {
            if ($groupType === 'ACTIVE_SUPERVISION') {
                $this->data['footer']['active_supervision']++;
            } else {
                $this->data['footer']['petitioners']++;
            }

            $spreadsheet->getActiveSheet()->setCellValue('G' . $this->lastFilledOutCellY, $row['offense']);
            $spreadsheet->getActiveSheet()->setCellValue('H' . $this->lastFilledOutCellY, $row['pe_date']);
            $spreadsheet->getActiveSheet()->setCellValue('I' . $this->lastFilledOutCellY, $row['pe_venue']);
            $spreadsheet->getActiveSheet()->setCellValue('J' . $this->lastFilledOutCellY, $row['pe_activity']);
            $spreadsheet->getActiveSheet()->setCellValue('K' . $this->lastFilledOutCellY, $row['rjp_date']);
            $spreadsheet->getActiveSheet()->setCellValue('L' . $this->lastFilledOutCellY, $row['rjp_venue']);
            $spreadsheet->getActiveSheet()->setCellValue('M' . $this->lastFilledOutCellY, $row['rjp_type']);
            $totalData[$row['rj_group']]['rjp_type']++;

            $rjpFullName = $row['planner_fn'] . ' ' . $row['planner_mn'] . ' ' . $row['planner_ln'];
            $spreadsheet->getActiveSheet()->setCellValue('N' . $this->lastFilledOutCellY, $rjpFullName);
            $spreadsheet->getActiveSheet()->setCellValue('O' . $this->lastFilledOutCellY, $this->formatPersonsInvolve($row['personsInvolved']));
            $spreadsheet->getActiveSheet()->setCellValue('P' . $this->lastFilledOutCellY, $row['rjp_status']);
            if (\in_array($row['rjp_status'], $rjpStatusResolvedCriteria)) {
                $totalData[$row['rj_group']]['rjp_status']['resolved']++;
            } else {
                $totalData[$row['rj_group']]['rjp_status']['unresolved']++;
            }

            $spreadsheet->getActiveSheet()->setCellValue('Q' . $this->lastFilledOutCellY, $row['rj_outcome_name']);
            $totalData[$row['rj_group']]['rj_outcome'][$row['rj_outcome_code']]++;

            foreach($row['clients'] as $client) {
                $fullName = $client['first_name'] . ' ' . $client['middle_name'] . ' ' . $client['last_name'];
    
                $spreadsheet->getActiveSheet()->getRowDimension($this->lastFilledOutCellY)->setRowHeight(70);
    
                $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, $fullName);
                $spreadsheet->getActiveSheet()->mergeCells("A" . $this->lastFilledOutCellY . ':B' . $this->lastFilledOutCellY);
    
                if ($client['gender'] === 'F') {
                    $spreadsheet->getActiveSheet()->setCellValue("C" . $this->lastFilledOutCellY, '∕');
                    $totalData[$row['rj_group']]['female']++;
                } else {
                    $spreadsheet->getActiveSheet()->setCellValue('D' . $this->lastFilledOutCellY, '∕');
                    $totalData[$row['rj_group']]['male']++;
                }
    
                if ($client['is_pwd'] !== '0') {
                    $spreadsheet->getActiveSheet()->setCellValue('E' . $this->lastFilledOutCellY, '∕');
                    $totalData[$row['rj_group']]['pwd']++;
                }
    
                if ($client['is_senior_citizen'] !== '0') {
                    $spreadsheet->getActiveSheet()->setCellValue('F' . $this->lastFilledOutCellY, '∕');
                    $totalData[$row['rj_group']]['senior_citizen']++;
                }
                $this->lastFilledOutCellY++;
            }

            $spreadsheet->getActiveSheet()
                ->getStyle("A" . $this->lastFilledOutCellY . ":Q" . $this->lastFilledOutCellY)
                ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        $spreadsheet->getActiveSheet()->getStyle('A12:Q' . $this->lastFilledOutCellY)->getAlignment()->setWrapText(true);
        $spreadsheet->getActiveSheet()->getStyle('A12:Q' . $this->lastFilledOutCellY)->getAlignment()->setVertical('center');
        $spreadsheet->getActiveSheet()->getStyle('A12:Q' . $this->lastFilledOutCellY)->getAlignment()->setHorizontal('center');

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, 'TOTAL');
        $spreadsheet->getActiveSheet()->setCellValue('L' . $this->lastFilledOutCellY, 'TOTAL');
        $spreadsheet->getActiveSheet()->setCellValue('C' . $this->lastFilledOutCellY, $totalData[$groupType]['female']);
        $spreadsheet->getActiveSheet()->setCellValue('D' . $this->lastFilledOutCellY, $totalData[$groupType]['male']);
        $spreadsheet->getActiveSheet()->setCellValue('E' . $this->lastFilledOutCellY, $totalData[$groupType]['pwd']);
        $spreadsheet->getActiveSheet()->setCellValue('F' . $this->lastFilledOutCellY, $totalData[$groupType]['senior_citizen']);
        $spreadsheet->getActiveSheet()->setCellValue('M' . $this->lastFilledOutCellY, $totalData[$groupType]['rjp_type']);
        $spreadsheet->getActiveSheet()->setCellValue(
            'P' . $this->lastFilledOutCellY,
            'Resolved:' . $totalData[$groupType]['rjp_status']['resolved'] .
            ' Unresolved:' . $totalData[$groupType]['rjp_status']['unresolved']
        );
        $spreadsheet->getActiveSheet()->setCellValue(
            'Q' . $this->lastFilledOutCellY,
            'R:' . $totalData[$groupType]['rj_outcome']['R'] .
            ' CWS:' . $totalData[$groupType]['rj_outcome']['CWS'] .
            ' RR:' . $totalData[$groupType]['rj_outcome']['RR'] .
            ' O:' . $totalData[$groupType]['rj_outcome']['O']
        );
        $spreadsheet->getActiveSheet()->getStyle('A' . $this->lastFilledOutCellY . ':Q' . $this->lastFilledOutCellY)->getFont()->setBold(true);
        $spreadsheet->getActiveSheet()
            ->getStyle("A" . $this->lastFilledOutCellY . ":Q" . $this->lastFilledOutCellY)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()
            ->getStyle("G" . $this->lastFilledOutCellY . ":K" . $this->lastFilledOutCellY)
            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('80808080');
        $spreadsheet->getActiveSheet()
            ->getStyle("N" . $this->lastFilledOutCellY . ":O" . $this->lastFilledOutCellY)
            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('80808080');

        $spreadsheet->getActiveSheet()->getStyle('A' . $this->lastFilledOutCellY . ':Q' . $this->lastFilledOutCellY)->getAlignment()->setVertical('center');
        $spreadsheet->getActiveSheet()->getStyle('A' . $this->lastFilledOutCellY . ':Q' . $this->lastFilledOutCellY)->getAlignment()->setHorizontal('center');

        if ($groupType === 'ACTIVE_SUPERVISION') {
            $this->lastFilledOutCellY++;
            $spreadsheet->getActiveSheet()
                ->getStyle("A" . $this->lastFilledOutCellY . ":Q" . $this->lastFilledOutCellY)
                ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()
                ->getStyle("A" . $this->lastFilledOutCellY . ":Q" . $this->lastFilledOutCellY)
                ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('80808080');
        }

        return $spreadsheet;
    }

    private function getData(array $data): array
    {
        $conductedProcesses = $this->service->getRJIB1(
            $data['quarter_id'],
            $data['field_office_id'],
        );

        $result['rows'] = $conductedProcesses['data'] ?? [];
        $result['footer'] = $data['footer'];

        return $result;
    }

    private function formatPersonsInvolve(array $personsInvolved): string
    {
        $names = [];
        foreach ($personsInvolved as $personInvolved) {
            if (strlen($personInvolved['othersName']) > 0) {
                $names[] = $personInvolved['othersName'];

                continue;
            }

            $names[] = $personInvolved['id']['label'];
        }

        return implode(', ', $names);
    }
}