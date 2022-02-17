<?php

declare(strict_types=1);

namespace App\Report\Table;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TCIA7 implements Form
{
    private const TABLE_NAME = "TCIA7";

    public function __construct(
        private int   $lastFilledOutCellY = 5,
        private array $data = [],
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
        $this->data = $data;

        $spreadsheet = $this->footer();
        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");

        $filePath = $_ENV['XLSX_PATH_FILE'] . self::TABLE_NAME . "-" . time() . ".xlsx";
        $writer->save($filePath);

        return new BinaryFileResponse($filePath);
    }

    public function footer(): Spreadsheet
    {
        $spreadsheet = $this->body();
//        $this->lastFilledOutCellY++;
//         $this->lastFilledOutCellY++;
//         $this->lastFilledOutCellY++;
//         $lastFilledOutCellY = $this->lastFilledOutCellY;
//
//         $spreadsheet->getActiveSheet()->setCellValue('a' . $this->lastFilledOutCellY, 'TABLE I.A.7  COMPUTATION OF THE PERCENTAGE OF TC CLIENTS VIS-À-VIS SUPERVISION CASELOAD');
//         $spreadsheet->getActiveSheet()->mergeCells('a' . $this->lastFilledOutCellY . ':f' . $this->lastFilledOutCellY);
//         $spreadsheet->getActiveSheet()->getStyle('a' . $lastFilledOutCellY . ':g' . $this->lastFilledOutCellY)->getFont()->setBold(true);
//         $this->lastFilledOutCellY++;
//         $this->lastFilledOutCellY++;
//         $this->lastFilledOutCellY++;
//
//         $spreadsheet->getActiveSheet()->setCellValue('a' . $this->lastFilledOutCellY, 'Source/s :  1.  Monthly Supervision Caseload Report of the FO (F 5, 21, 44 & 45)');
//         $this->lastFilledOutCellY++;
//         $spreadsheet->getActiveSheet()->setCellValue('a' . $this->lastFilledOutCellY, '                    2.  Tables IA.2 to Tables I.A.6 of this form (bottom part of each table)');
//         $this->lastFilledOutCellY++;
//         $this->lastFilledOutCellY++;
//
//         $spreadsheet->getActiveSheet()->setCellValue('a' . $this->lastFilledOutCellY, 'Computation :  Fill in the spaces with appropriate data from above sources and compute the percentage of clients involvement in TC program');
//         $this->lastFilledOutCellY++;
//         $spreadsheet->getActiveSheet()->setCellValue('a' . $this->lastFilledOutCellY, '                           using the formula provided in Table I.A.7');
        return $spreadsheet;

    }

    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();

        $activeSupervisions = $this->data['rows']['activeSupervisions'];
        $activeCourtesySupervision = $this->data['rows']['activeCourtesySupervision'];
        $superVisionReferrals = $this->data['rows']['superVisionReferrals'];
        $courtesySupervisionReferrals = $this->data['rows']['courtesySupervisionReferrals'];
        $supervisionCasesDropped = $this->data['rows']['supervisionCasesDropped'];
        $totalSupervisionCasesHandled = $this->data['rows']['totalSupervisionCasesHandled'];
        $less = $this->data['rows']['less'];
        $totalLess = $this->data['rows']['totalLess'];
        $totalAdjustedSupervisionCaseLoad = $this->data['rows']['totalAdjustedSupervisionCaseLoad'];
        $clientsAttendingTC = $this->data['rows']['clientsAttendingTC'];
        $percentageOfClientsAttendingTC = $this->data['rows']['percentageOfClientsAttendingTC'];

        $spreadsheet = $this->plotActiveSupervisions($activeSupervisions, $spreadsheet, 7);
        $spreadsheet = $this->plotActiveSupervisions($activeCourtesySupervision, $spreadsheet, 8);
        $spreadsheet = $this->plotSupervisionReferrals($superVisionReferrals, $spreadsheet, false);
        $spreadsheet = $this->plotSupervisionReferrals($courtesySupervisionReferrals, $spreadsheet, true);
        $spreadsheet = $this->plotSupervisionCasesDropped($supervisionCasesDropped, $spreadsheet);

        $spreadsheet = $this->plotTotalSupervisionCasesHandled($totalSupervisionCasesHandled, $spreadsheet);
        $spreadsheet = $this->plotLess($less, $spreadsheet);
        $spreadsheet = $this->plotTotalLess($totalLess, $spreadsheet);
        $spreadsheet = $this->plotTotalAdjustedSupervisionCaseload($totalAdjustedSupervisionCaseLoad, $spreadsheet);
        $spreadsheet = $this->plotClientsAttendingTC($clientsAttendingTC, $spreadsheet);
        $spreadsheet = $this->plotPercentageOfClientsAttendingTC($percentageOfClientsAttendingTC, $spreadsheet);

        $spreadsheet->getActiveSheet()->getStyle('A5:F45')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('A5:F5')->getFont()->setBold(true);
        $spreadsheet->getActiveSheet()->getStyle('B5:F45' . $this->lastFilledOutCellY)->getAlignment()->setHorizontal('center');
        $spreadsheet->getActiveSheet()->getStyle('B5:F45' . $this->lastFilledOutCellY)->getAlignment()->setVertical('center');

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
            'f1' => 'PPA- PLD-FR-004',
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
            'a11' => '                         Month 1   JANUARY',
            'a12' => '                         Month 2  FEBRUARY',
            'a13' => '                         Month 3   MARCH',
            'a14' => ' ',
            'a15' => '             b.   New Courtesy Supervision Referrals',
            'a16' => '                         Month 1   JANUARY',
            'a17' => '                         Month 2  FEBRUARY',
            'a18' => '                          Month 3   MARCH',
            'a19' => ' ',
            'a20' => '3.  LESS:   Supervision cases dropped (Terminated, Revoked, Transferred)',
            'a21' => '                         Month 1   JANUARY',
            'a22' => '                         Month 2  FEBRUARY',
            'a23' => '                         Month 3   MARCH',
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
     * @param array $activeSupervisions
     * @param Spreadsheet $spreadsheet
     * @param int $rowNumber
     * @return Spreadsheet
     */
    private function plotActiveSupervisions(array $activeSupervisions, Spreadsheet $spreadsheet, int $rowNumber): Spreadsheet
    {
        $total = 0;
        if (isset($activeSupervisions[1])) {
            $spreadsheet->getActiveSheet()->setCellValue('B' . $rowNumber, $activeSupervisions[1]);
            $total += $activeSupervisions[1];
        }

        $form21 = 0;
        if (isset($activeSupervisions[2])) {
            $form21 += $activeSupervisions[2];
        }

        if (isset($activeSupervisions[3])) {
            $form21 += $activeSupervisions[3];
        }

        $total += $form21;
        $spreadsheet->getActiveSheet()->setCellValue('C' . $rowNumber, $form21);

        if (isset($activeSupervisions[4])) {
            $spreadsheet->getActiveSheet()->setCellValue('D' . $rowNumber, $activeSupervisions[4]);
            $total += $activeSupervisions[4];
        }

        if (isset($activeSupervisions[5])) {
            $spreadsheet->getActiveSheet()->setCellValue('E' . $rowNumber, $activeSupervisions[5]);
            $total += $activeSupervisions[5];
        }
        $spreadsheet->getActiveSheet()->setCellValue('F' . $rowNumber, $total);

        return $spreadsheet;
    }

    /**
     * @param int[][] $supervisionReferrals
     * @param Spreadsheet $spreadsheet
     * @param bool $isCourtesy
     * @return Spreadsheet
     */
    private function plotSupervisionReferrals(array $supervisionReferrals, Spreadsheet $spreadsheet, bool $isCourtesy): Spreadsheet
    {
        $xCoordinate = [
            1 => 10, 2 => 11, 3 => 12, 4 => 10, 5 => 11, 6 => 12, 7 => 10, 8 => 11, 9 => 12, 10 => 10, 11 => 11, 12 => 12
        ];
        $courtesyXCoordinate = [
            1 => 16, 2 => 17, 3 => 18, 4 => 16, 5 => 17, 6 => 18, 7 => 16, 8 => 17, 9 => 18, 10 => 16, 11 => 17, 12 => 18
        ];

        foreach ($supervisionReferrals as $month=>$supervisionReferral) {
            $total = 0;
            $coordinateX = $isCourtesy ? $courtesyXCoordinate : $xCoordinate;

            if (isset($supervisionReferral[1])) {
                $spreadsheet->getActiveSheet()->setCellValue('B' . $coordinateX[$month], $supervisionReferral[1]);
                $total += $supervisionReferral[1];
            }

            $form21 = 0;
            if (isset($supervisionReferral[2])) {
                $form21 += $supervisionReferral[2];
            }

            if (isset($supervisionReferral[3])) {
                $form21 += $supervisionReferral[3];
            }

            $total += $form21;
            $spreadsheet->getActiveSheet()->setCellValue('C' . $coordinateX[$month], $form21);

            if (isset($supervisionReferral[4])) {
                $spreadsheet->getActiveSheet()->setCellValue('D' . $coordinateX[$month], $supervisionReferral[4]);
                $total += $supervisionReferral[4];
            }

            if (isset($supervisionReferral[5])) {
                $spreadsheet->getActiveSheet()->setCellValue('E' . $coordinateX[$month], $supervisionReferral[5]);
                $total += $supervisionReferral[5];
            }
            $spreadsheet->getActiveSheet()->setCellValue('F' . $coordinateX[$month], $total);
        }

        return $spreadsheet;
    }

    /**
     * @param int[][] $supervisionDroppedCases
     * @param Spreadsheet $spreadsheet
     * @return Spreadsheet
     */
    private function plotSupervisionCasesDropped(array $supervisionDroppedCases, Spreadsheet $spreadsheet): Spreadsheet
    {
        $xCoordinate = [
            1 => 21, 2 => 22, 3 => 23, 4 => 21, 5 => 22, 6 => 23, 7 => 21, 8 => 22, 9 => 23, 10 => 21, 11 => 22, 12 => 23
        ];

        foreach ($supervisionDroppedCases as $month=>$supervisionDroppedCase) {
            $total = 0;

            if (isset($supervisionDroppedCase[1])) {
                $spreadsheet->getActiveSheet()->setCellValue('B' . $xCoordinate[$month], $supervisionDroppedCase[1]);
                $total += $supervisionDroppedCase[1];
            }

            $form21 = 0;
            if (isset($supervisionDroppedCase[2])) {
                $form21 += $supervisionDroppedCase[2];
            }

            if (isset($supervisionDroppedCase[3])) {
                $form21 += $supervisionDroppedCase[3];
            }

            $total += $form21;
            $spreadsheet->getActiveSheet()->setCellValue('C' . $xCoordinate[$month], $form21);

            if (isset($supervisionDroppedCase[4])) {
                $spreadsheet->getActiveSheet()->setCellValue('D' . $xCoordinate[$month], $supervisionDroppedCase[4]);
                $total += $supervisionDroppedCase[4];
            }

            if (isset($supervisionDroppedCase[5])) {
                $spreadsheet->getActiveSheet()->setCellValue('E' . $xCoordinate[$month], $supervisionDroppedCase[5]);
                $total += $supervisionDroppedCase[5];
            }
            $spreadsheet->getActiveSheet()->setCellValue('F' . $xCoordinate[$month], $total);
        }

        return $spreadsheet;
    }

    private function plotTotalLess(array $totalLess, Spreadsheet $spreadsheet): Spreadsheet
    {
        $total = 0;
        if (isset($totalLess[1])) {
            $spreadsheet->getActiveSheet()->setCellValue('B24', $totalLess[1]);
            $total += $totalLess[1];
        }

        $form21 = 0;
        if (isset($totalLess[2])) {
            $form21 += $totalLess[2];
        }

        if (isset($totalLess[3])) {
            $form21 += $totalLess[3];
        }

        $total += $form21;
        $spreadsheet->getActiveSheet()->setCellValue('C24', $form21);

        if (isset($totalLess[4])) {
            $spreadsheet->getActiveSheet()->setCellValue('D24', $totalLess[4]);
            $total += $totalLess[4];
        }

        if (isset($totalLess[5])) {
            $spreadsheet->getActiveSheet()->setCellValue('E24', $totalLess[5]);
            $total += $totalLess[5];
        }
        $spreadsheet->getActiveSheet()->setCellValue('F24', $total);

        return $spreadsheet;
    }

    /**
     * @param int[][] $less
     * @param Spreadsheet $spreadsheet
     * @return Spreadsheet
     */
    private function plotLess(array $less, Spreadsheet $spreadsheet): Spreadsheet
    {
        $xCoordinate = [13 => 30, 7 => 31, 6 => 32, 8 => 33, 9 => 34, 10 => 35, 11 => 37, 12 => 38];

        foreach ($less as $remarksId=>$data) {
            $total = 0;

            if (isset($data[1])) {
                $spreadsheet->getActiveSheet()->setCellValue('B' . $xCoordinate[$remarksId], $data[1]);
                $total += $data[1];
            }

            $form21 = 0;
            if (isset($data[2])) {
                $form21 += $data[2];
            }

            if (isset($data[3])) {
                $form21 += $data[3];
            }

            $total += $form21;
            $spreadsheet->getActiveSheet()->setCellValue('C' . $xCoordinate[$remarksId], $form21);

            if (isset($data[4])) {
                $spreadsheet->getActiveSheet()->setCellValue('D' . $xCoordinate[$remarksId], $data[4]);
                $total += $data[4];
            }

            if (isset($data[5])) {
                $spreadsheet->getActiveSheet()->setCellValue('E' . $xCoordinate[$remarksId], $data[5]);
                $total += $data[5];
            }
            $spreadsheet->getActiveSheet()->setCellValue('F' . $xCoordinate[$remarksId], $total);
        }

        return $spreadsheet;
    }

    private function plotTotalSupervisionCasesHandled(array $totalSupervisionCasesHandled, Spreadsheet $spreadsheet): Spreadsheet
    {
        $total = 0;
        if (isset($totalSupervisionCasesHandled[1])) {
            $spreadsheet->getActiveSheet()->setCellValue('B24', $totalSupervisionCasesHandled[1]);
            $total += $totalSupervisionCasesHandled[1];
        }

        $form21 = 0;
        if (isset($totalSupervisionCasesHandled[2])) {
            $form21 += $totalSupervisionCasesHandled[2];
        }

        if (isset($totalSupervisionCasesHandled[3])) {
            $form21 += $totalSupervisionCasesHandled[3];
        }

        $total += $form21;
        $spreadsheet->getActiveSheet()->setCellValue('C24', $form21);

        if (isset($totalSupervisionCasesHandled[4])) {
            $spreadsheet->getActiveSheet()->setCellValue('D24', $totalSupervisionCasesHandled[4]);
            $total += $totalSupervisionCasesHandled[4];
        }

        if (isset($totalSupervisionCasesHandled[5])) {
            $spreadsheet->getActiveSheet()->setCellValue('E24', $totalSupervisionCasesHandled[5]);
            $total += $totalSupervisionCasesHandled[5];
        }
        $spreadsheet->getActiveSheet()->setCellValue('F24', $total);

        return $spreadsheet;
    }

    private function plotTotalAdjustedSupervisionCaseload(array $totalAdjustedSupervisionCaseLoad, Spreadsheet $spreadsheet): Spreadsheet
    {
        $total = 0;
        if (isset($totalAdjustedSupervisionCaseLoad[1])) {
            $spreadsheet->getActiveSheet()->setCellValue('B40', $totalAdjustedSupervisionCaseLoad[1]);
            $total += $totalAdjustedSupervisionCaseLoad[1];
        }

        $form21 = 0;
        if (isset($totalAdjustedSupervisionCaseLoad[2])) {
            $form21 += $totalAdjustedSupervisionCaseLoad[2];
        }

        if (isset($totalAdjustedSupervisionCaseLoad[3])) {
            $form21 += $totalAdjustedSupervisionCaseLoad[3];
        }

        $total += $form21;
        $spreadsheet->getActiveSheet()->setCellValue('C40', $form21);

        if (isset($totalAdjustedSupervisionCaseLoad[4])) {
            $spreadsheet->getActiveSheet()->setCellValue('D40', $totalAdjustedSupervisionCaseLoad[4]);
            $total += $totalAdjustedSupervisionCaseLoad[4];
        }

        if (isset($totalAdjustedSupervisionCaseLoad[5])) {
            $spreadsheet->getActiveSheet()->setCellValue('E40', $totalAdjustedSupervisionCaseLoad[5]);
            $total += $totalAdjustedSupervisionCaseLoad[5];
        }
        $spreadsheet->getActiveSheet()->setCellValue('F40', $total);

        return $spreadsheet;
    }

    private function plotClientsAttendingTC(array $clientsAttendingTC, Spreadsheet $spreadsheet): Spreadsheet
    {
        $total = 0;
        if (isset($clientsAttendingTC[1])) {
            $spreadsheet->getActiveSheet()->setCellValue('B42', $clientsAttendingTC[1]);
            $total += $clientsAttendingTC[1];
        }

        $form21 = 0;
        if (isset($clientsAttendingTC[2])) {
            $form21 += $clientsAttendingTC[2];
        }

        if (isset($clientsAttendingTC[3])) {
            $form21 += $clientsAttendingTC[3];
        }

        $total += $form21;
        $spreadsheet->getActiveSheet()->setCellValue('C42', $form21);

        if (isset($clientsAttendingTC[4])) {
            $spreadsheet->getActiveSheet()->setCellValue('D42', $clientsAttendingTC[4]);
            $total += $clientsAttendingTC[4];
        }

        if (isset($clientsAttendingTC[5])) {
            $spreadsheet->getActiveSheet()->setCellValue('E42', $clientsAttendingTC[5]);
            $total += $clientsAttendingTC[5];
        }
        $spreadsheet->getActiveSheet()->setCellValue('F42', $total);

        return $spreadsheet;
    }

    private function plotPercentageOfClientsAttendingTC(array $percentageOfClientsAttendingTC, Spreadsheet $spreadsheet): Spreadsheet
    {
        $total = 0;
        $count = 0;
        if (isset($percentageOfClientsAttendingTC[1])) {
            $spreadsheet->getActiveSheet()->setCellValue('B44', $percentageOfClientsAttendingTC[1] . '%');
            $total += $percentageOfClientsAttendingTC[1];
            $count++;
        }

        $form21 = 0;
        if (isset($percentageOfClientsAttendingTC[2])) {
            $form21 += $percentageOfClientsAttendingTC[2];
        }

        if (isset($percentageOfClientsAttendingTC[3])) {
            $form21 += $percentageOfClientsAttendingTC[3];
        }

        $total += $form21;
        if ($form21 > 0) {
            $count++;
        }
        $spreadsheet->getActiveSheet()->setCellValue('C44', $form21 . '%');

        if (isset($percentageOfClientsAttendingTC[4])) {
            $spreadsheet->getActiveSheet()->setCellValue('D44', $percentageOfClientsAttendingTC[4] . '%');
            $total += $percentageOfClientsAttendingTC[4];
            $count++;
        }

        if (isset($percentageOfClientsAttendingTC[5])) {
            $spreadsheet->getActiveSheet()->setCellValue('E44', $percentageOfClientsAttendingTC[5] . '%');
            $total += $percentageOfClientsAttendingTC[5];
            $count++;
        }
        $overallTotal = $total > 0 && $count > 0 ? ($total / $count) : 0;
        $spreadsheet->getActiveSheet()->setCellValue('F44', $overallTotal . '%');

        return $spreadsheet;
    }
}