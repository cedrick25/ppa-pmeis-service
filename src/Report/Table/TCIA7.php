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
        $this->lastFilledOutCellY++;
        // $this->lastFilledOutCellY++;
        // $this->lastFilledOutCellY++;
        // $lastFilledOutCellY = $this->lastFilledOutCellY;

        // $spreadsheet->getActiveSheet()->setCellValue('a' . $this->lastFilledOutCellY, 'TABLE I.A.7  COMPUTATION OF THE PERCENTAGE OF TC CLIENTS VIS-À-VIS SUPERVISION CASELOAD');
        // $spreadsheet->getActiveSheet()->mergeCells('a' . $this->lastFilledOutCellY . ':f' . $this->lastFilledOutCellY);
        // $spreadsheet->getActiveSheet()->getStyle('a' . $lastFilledOutCellY . ':g' . $this->lastFilledOutCellY)->getFont()->setBold(true);
        // $this->lastFilledOutCellY++;
        // $this->lastFilledOutCellY++;
        // $this->lastFilledOutCellY++;

        // $spreadsheet->getActiveSheet()->setCellValue('a' . $this->lastFilledOutCellY, 'Source/s :  1.  Monthly Supervision Caseload Report of the FO (F 5, 21, 44 & 45)');
        // $this->lastFilledOutCellY++;
        // $spreadsheet->getActiveSheet()->setCellValue('a' . $this->lastFilledOutCellY, '                    2.  Tables IA.2 to Tables I.A.6 of this form (bottom part of each table)');
        // $this->lastFilledOutCellY++;
        // $this->lastFilledOutCellY++;
        
        // $spreadsheet->getActiveSheet()->setCellValue('a' . $this->lastFilledOutCellY, 'Computation :  Fill in the spaces with appropriate data from above sources and compute the percentage of clients involvement in TC program');
        // $this->lastFilledOutCellY++;
        // $spreadsheet->getActiveSheet()->setCellValue('a' . $this->lastFilledOutCellY, '                           using the formula provided in Table I.A.7');
        return $spreadsheet;

    }

    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();

        $spreadsheet->getActiveSheet()->getStyle('a5:f45')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('a5:f5')->getFont()->setBold(true);

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
        $mergesCoordinates = [
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
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        }

        return $spreadsheet;
    }
}