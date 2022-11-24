<?php

declare(strict_types=1);

namespace App\Report\Table;

use App\Enum\SystemSettingNames;
use App\Repository\QuartersRepository;
use App\Service\TherapeuticCommunity\Sessions;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TCIA7 implements Form
{
    private const TABLE_NAME = "TCIA7";

    public function __construct(
        private QuartersRepository  $quartersRepository,
        private int                 $lastFilledOutCellY = 5,
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
        $quarter = $this->quartersRepository->find($data['quarter_id']);
        $this->data['rows'] = $data['rows'];
        $this->data['quarter'] = $quarter->getName();
        $this->data[SystemSettingNames::GENERATED_REPORTS_CODE] = $data[SystemSettingNames::GENERATED_REPORTS_CODE];

        $spreadsheet = $this->footer();
        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");

        $filePath = $_ENV['XLSX_PATH_FILE'] . self::TABLE_NAME . "-" . time() . ".xlsx";
        $writer->save($filePath);

        return new BinaryFileResponse($filePath);
    }

    public function footer(): Spreadsheet
    {
        return $this->body();
    }

    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();

        $months = array_keys($this->data['rows']['superVisionReferrals']);
        $xCoordinates = [
            'totalSupervisionCaseloadEndOfQuarter' => 6,
            'activeSupervisions' => 7,
            'activeCourtesySupervision' => 8,
            'totalNewSuperVisionReferrals' => 10,
            'superVisionReferrals' => [$months[0] => 11, $months[1] => 12, $months[2] => 13],
            'totalNewCourtesySupervisionReferrals' => 15,
            'courtesySupervisionReferrals' => [$months[0] => 16, $months[1] => 17, $months[2] => 18],
            'totalSupervisionCasesDropped' => 20,
            'supervisionCasesDropped' => [$months[0] => 21, $months[1] => 22, $months[2] => 23],
            'totalSupervisionCasesHandled' => 24,
            'totalLess' => 39,
            'totalAdjustedSupervisionCaseLoad' => 40,
            'clientsAttendingTC' => 42,
            'percentageOfClientsAttendingTC' => 44,
        ];

        foreach ($this->data['rows'] as $name=>$row) {
            if ('less' === $name) {
                $spreadsheet = $this->plotLess($row, $spreadsheet);

                continue;
            }

            if (
                'superVisionReferrals' === $name ||
                'courtesySupervisionReferrals' === $name ||
                'supervisionCasesDropped' === $name
            ) {
                foreach ($row as $month=>$form) {
                    $total = 0;
                    $coordinates = $this->buildFormCoordinates($xCoordinates[$name][$month]);

                    foreach ($form as $formName=>$value) {
                        $total += $value;
                        $spreadsheet->getActiveSheet()->setCellValue($coordinates[$formName], $value);
                    }
                    $spreadsheet->getActiveSheet()->setCellValue($coordinates['total'], $total);
                }

                continue;
            }

            $total = 0;
            $coordinates = $this->buildFormCoordinates($xCoordinates[$name]);

            foreach ($row as $formName=>$value) {
                $total += $value;
                $spreadsheet->getActiveSheet()->setCellValue($coordinates[$formName], $value);
            }
            $spreadsheet->getActiveSheet()->setCellValue($coordinates['total'], $total);
        }


        $spreadsheet->getActiveSheet()->getStyle('A5:F45')
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('A5:F5')
            ->getFont()->setBold(true);
        $spreadsheet->getActiveSheet()->getStyle('B5:F45' . $this->lastFilledOutCellY)
            ->getAlignment()->setHorizontal('center');
        $spreadsheet->getActiveSheet()->getStyle('B5:F45' . $this->lastFilledOutCellY)
            ->getAlignment()->setVertical('center');

        return $spreadsheet;
    }

    public function header(): Spreadsheet
    {
        return  $this->prepare();
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function prepare(): Spreadsheet
    {
        $months = array_keys($this->data['rows']['superVisionReferrals']);
        $spreadsheet = new Spreadsheet();
        $textAndCoordinates = [
            'f1' => $this->data[SystemSettingNames::GENERATED_REPORTS_CODE],
            'a4' => 'Table I.A.7    COMPUTATION',
            'a5' => 'PARTICULARS',
            'b5' => 'Form 5',
            'c5' => 'Form 21',
            'd5' => 'Form 44',
            'e5' => 'Form 45',
            'f5' => 'TOTAL',
            'a6' => '1.  Total Supervision Caseload  End of Previous Quarter',
            'a7' => '              a.   Active Supervision',
            'a8' => '              b.   Active Courtesy Supervision',
            'a9' => '2.   ADD',
            'a10' => '             a.   New Supervision Referrals ',
            'a11' => '                         Month 1   ' . $months[0],
            'a12' => '                         Month 2   ' . $months[1],
            'a13' => '                         Month 3   ' . $months[2],
            'a14' => ' ',
            'a15' => '             b.   New Courtesy Supervision Referrals',
            'a16' => '                         Month 1   ' . $months[0],
            'a17' => '                         Month 2   ' . $months[1],
            'a18' => '                         Month 3  ' . $months[2],
            'a19' => ' ',
            'a20' => '3.  LESS:   Supervision cases dropped (Terminated, Revoked, Transferred)',
            'a21' => '                         Month 1   ' . $months[0],
            'a22' => '                         Month 2   ' . $months[1],
            'a23' => '                         Month 3   ' . $months[2],
            'a24' => '3.   Total Supervision Cases Handled',
            'a25' => '  ',
            'a26' => '4.     LESS:   Clients under the following circumstances  ',
            'a27' => '                     AND HAVE NOT attended any TCLP session/ ',
            'a28' => '                     activity for the ENTIRE quarter.  ',
            'a29' => '                     (Refer to bottom part of Tables I.A.2 to I.A.6)',
            'a30' => '       a.   On CS to other FOs',
            'a31' => '       b.   Died with no report submitted to Court/ BPP',
            'a32' => '       c.    Absconded with no report submitted to Court/ BPP',
            'a33' => '       d.   In jail with no report submitted to court/ BPP',
            'a34' => '       e.   With serious ailment',
            'a35' => '       f.    On travel abroad ( with permit)',
            'a36' => '       g.   Supervision cases dropped (Terminated, Revoked, Transferred)',
            'a37' => '       h.   Cases Pending in Court/ BPP',
            'a38' => '       i.    Others (specify):  No initial report ',
            'a39' => ' ',
            'a40' => '5.   Total Adjusted Supervision Caseload This Quarter',
            'a41' => ' ',
            'a42' => '6.   Total Number of Clients Attending TC',
            'a43' => ' ',
            'a44' => '7.   Percentage of Clients Attending TC',
            'a45' => ' ',

        ];
        $boldCoordinates = ['f1', 'a4',];
        $verticalAlignedCoordinates = ['a5:f5' => 'center',];
        $horizontalAlignedCoordinates = ['a5:f5' => 'center',];
        $adjustedColumnWidthCoordinates = [
            'A' => 70, 'f' => 20,
        ];
        $outlineBorderThinCoordinates = [
            "A6:A23",
        ];

        foreach ($textAndCoordinates as $coordinate=>$text) {
            $spreadsheet->getActiveSheet()->setCellValue($coordinate, $text);
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

        return $spreadsheet;
    }

    /**
     * @param array<string, <string, int>> $less
     * @param Spreadsheet $spreadsheet
     * @return Spreadsheet
     */
    private function plotLess(array $less, Spreadsheet $spreadsheet): Spreadsheet
    {
        $xCoordinateByRemarksId = [
            15 => 30, 7 => 31, 6 => 32, 8 => 33, 9 => 34,
            10 => 35, 3 => 36, 4 => 36, 5 => 36, 11 => 37, 12 => 38
        ];

        foreach ($less as $remarksId=>$forms) {
            if (! isset($xCoordinateByRemarksId[$remarksId])) {
                // NOTE: add logs that says: mismatch remarks id
                continue;
            }
            $total = 0;
            $xCoordinate = $xCoordinateByRemarksId[$remarksId];
            $coordinates = $this->buildFormCoordinates($xCoordinate);

            foreach ($forms as $formName=>$value) {
                $total += $value;
                $spreadsheet->getActiveSheet()->setCellValue($coordinates[$formName], $value);
            }

            $spreadsheet->getActiveSheet()->setCellValue($coordinates['total'], $total);
        }

        return $spreadsheet;
    }

    private function buildFormCoordinates(int $x): array {
        return ['form5' => 'B' . $x, 'form21' => 'C' . $x, 'form44' => 'D' . $x, 'form45' => 'E' . $x, 'total' => 'F' . $x];
    }
}