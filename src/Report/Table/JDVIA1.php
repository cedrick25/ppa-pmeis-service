<?php

declare(strict_types=1);

namespace App\Report\Table;

use App\Enum\SystemSettingNames;
use App\Service\Volunteerism\JailDecongestion;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class JDVIA1 implements Form
{
    private const TABLE_NAME = "JDVIA1";
    
    public function __construct(
        private JailDecongestion    $jailDecongestionService,
        private int                 $lastFilledOutCellY = 8,
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

        return $spreadsheet;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();

        $total = [
            'probation' => 0,
            'clemency' => 0,
            'referral_pao' => 0,
            'referral_prosecution' => 0,
            'referral_others' => 0,
            'gcta' => 0,
            'recognizance' => 0,
        ];

        foreach ($this->data['rows'] as $row) {
            $this->lastFilledOutCellY++;

            $spreadsheet->getActiveSheet()->setCellValue("a" . $this->lastFilledOutCellY, $row['date']);
            $spreadsheet->getActiveSheet()->setCellValue("b" . $this->lastFilledOutCellY, $row['name_address']);
            if (intval($row['jail_venue'])) {
                $spreadsheet->getActiveSheet()->setCellValue("c" . $this->lastFilledOutCellY, '/');
            }
            if (intval($row['jail_office'])) {
                $spreadsheet->getActiveSheet()->setCellValue("D" . $this->lastFilledOutCellY, '/');
            }
            $spreadsheet->getActiveSheet()->setCellValue("e" . $this->lastFilledOutCellY, $row['probation']);
            $spreadsheet->getActiveSheet()->setCellValue("f" . $this->lastFilledOutCellY, intval($row['clemency']) < 0 ? 0 : $row['clemency']);
            $spreadsheet->getActiveSheet()->setCellValue("g" . $this->lastFilledOutCellY, $row['referral_pao']);
            $spreadsheet->getActiveSheet()->setCellValue("h" . $this->lastFilledOutCellY, $row['referral_prosecution']);
            $spreadsheet->getActiveSheet()->setCellValue("i" . $this->lastFilledOutCellY, $row['referral_others']);
            $spreadsheet->getActiveSheet()->setCellValue("j" . $this->lastFilledOutCellY, $row['gcta']);
            $spreadsheet->getActiveSheet()->setCellValue("k" . $this->lastFilledOutCellY, $row['recognizance']);
            $spreadsheet->getActiveSheet()->setCellValue("m" . $this->lastFilledOutCellY, $row['remarks']);

            foreach ($row['personsResponsible'] as $personsResponsible) {
                $name = (strlen($personsResponsible['othersName']) > 0)
                    ? $personsResponsible['othersName']
                    : $personsResponsible['id']['label'];

                $spreadsheet->getActiveSheet()->setCellValue('L' . $this->lastFilledOutCellY, $name);

                $this->lastFilledOutCellY++;
            }

            $total['probation'] += intval($row['probation']);
            $total['clemency'] += intval($row['clemency']);
            $total['referral_pao'] += intval($row['referral_pao']);
            $total['referral_prosecution'] += intval($row['referral_prosecution']);
            $total['referral_others'] += intval($row['referral_others']);
            $total['gcta'] += intval($row['gcta']);
            $total['recognizance'] += intval($row['recognizance']);
        }

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('B' . $this->lastFilledOutCellY, 'TOTAL');
        $spreadsheet->getActiveSheet()->setCellValue('E' . $this->lastFilledOutCellY, $total['probation']);
        $spreadsheet->getActiveSheet()->setCellValue('F' . $this->lastFilledOutCellY, $total['clemency']);
        $spreadsheet->getActiveSheet()->setCellValue('G' . $this->lastFilledOutCellY, $total['referral_pao']);
        $spreadsheet->getActiveSheet()->setCellValue('H' . $this->lastFilledOutCellY, $total['referral_prosecution']);
        $spreadsheet->getActiveSheet()->setCellValue('I' . $this->lastFilledOutCellY, $total['referral_others']);
        $spreadsheet->getActiveSheet()->setCellValue('J' . $this->lastFilledOutCellY, $total['gcta']);
        $spreadsheet->getActiveSheet()->setCellValue('K' . $this->lastFilledOutCellY, $total['recognizance']);

        $spreadsheet->getActiveSheet()->getStyle('A9:M' . $this->lastFilledOutCellY)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('A9:M' . $this->lastFilledOutCellY)
            ->getAlignment()->setHorizontal('center');
        $spreadsheet->getActiveSheet()->getStyle('A9:M' . $this->lastFilledOutCellY)
            ->getAlignment()->setVertical('center');


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
            'a1' => 'VI.  SUPPORT FUNCTION', 'm1' => $this->data[SystemSettingNames::GENERATED_REPORTS_CODE],
            'a3' => 'Table VI.A.1  JAIL DECONGESTION SERVICES/ ACTIVITIES ',
            'a5' => 'Date',
            'a6' => '(1)',
            'b4' => 'Name and Address of Jail/Office Assisted',
            'b7' => '(2)',
            'c4' => 'Venue (3)',
            'c6' => 'Jail',
            'd6' => 'Office',
            'e4' => 'Activities/Number of Inmates or Detainees Assisted (4)',
            'e5' => 'No. of Intake Interview',
            'e6' => 'Probation',
            'f6' => 'Pre-Parole',
            'f7' => 'Executive',
            'f8' => 'Clemency',
            'g5' => 'No. of Referrals',
            'g6' => 'PAO',
            'h6' => 'Prosecution',
            'i6' => 'Others',
            'j5' => 'MSEC / GCTA',
            'k5' => 'Release on Recognizance',
            'l4' => 'Person Responsible (5)',
            'm4' => '(6)',
            'm5' => 'REMARKS',
            'm6' => 'To include issues and problems ',
            'm7' => 'encountered and other ',
            'm8' => 'relevant information',

        ];
        $mergesCoordinates = [
            'C4:D5', 'D7:D8', 'E4:K4', 'E5:F5', 'G5:I5', 'L4:L8', 'J5:J8', 'K5:K8', 'B4:B6', 'C6:C8',
            'D6:D8', 'E6:E8', 'G6:G8', 'H6:H8', 'I6:I8', 'J5:J8',

        ];
        $boldCoordinates = ['a1','a3','m1',];
        $verticalAlignedCoordinates = ['B3:T30' => 'center', 'A3:A8' => 'center'];
        $horizontalAlignedCoordinates = ['B3:T30' => 'center', 'A3:A8' => 'center'];
        $adjustedColumnWidthCoordinates = [
            'A' => 20, 'B' => 40, 'C' => 20, 'D' => 20, 'E' => 15, 'F' => 15, 'G' => 15, 'H' => 15, 'I' => 15,
            'J' => 15, 'K' => 27, 'L' => 20, 'M' => 30, 'N' => 30, 'O' => 20, 'P' => 20, 'Q' => 5, 'R' => 5,
            'S' => 5, 'T' => 20,
        ];
        $outlineBorderThinCoordinates = [
            'A4:A8', 'B4:B8', 'C6:C8', 'D6:D8', 'D7:D8', 'E6:E8', 'F6:F8', 'G6:G8', 'H6:H8', 'I6:I8', 'J5:J8',
            'K5:K8', 'M4:M8', 'E4:K4', 'E5:F5', 'G5:I5', 'c4:d5', 'L4:L8'
        ];

        foreach ($textAndCoordinates as $coordinate=>$text) {
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

        return $spreadsheet;
    }

    private function getData(array $data): array
    {
        $results = $this->jailDecongestionService->getReport($data['quarter_id'], $data['field_office_id']);

        return ['rows' => array_values($results['data'] ?? [])];
    }
}