<?php

declare(strict_types=1);

namespace App\Report\Table;

use App\Common\AppDateHelper;
use App\Service\RestorativeJustice\RelatedRestitutions;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RJIB3 implements Form
{
    private const TABLE_NAME = "RJIB3";
    
    public function __construct(
        private AppDateHelper       $appDateHelper,
        private RelatedRestitutions $service,
        private int                 $lastFilledOutCellY = 11,
        private array               $data = [],
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

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function footer(): Spreadsheet
    {
        $spreadsheet = $this->body();
        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, "NOTE: No. of victims/ offended parties who received CL payments will be reflected in the FO's IQPR Summary Form.");

        $this->lastFilledOutCellY++;
        $this->lastFilledOutCellY++;
        $savedLastFilledOutCellY = $this->lastFilledOutCellY;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, 'Reference: PPA Service Manual');

        $this->lastFilledOutCellY++;
        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, 'Forms of Payment');
        $spreadsheet->getActiveSheet()->setCellValue('F' . $this->lastFilledOutCellY, 'Mode of Payment');

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, '              1.   Cash');
        $spreadsheet->getActiveSheet()->setCellValue('F' . $this->lastFilledOutCellY, '          1.   Direct Payment to Offended Party');

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, '              2.   Check, money order or demand draft');
        $spreadsheet->getActiveSheet()->setCellValue('F' . $this->lastFilledOutCellY, "          2.   Payment thru deposit in Bank or Clients' Cooperative");

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, '              3.   Transfer of real/ personal property');
        $spreadsheet->getActiveSheet()->setCellValue('F' . $this->lastFilledOutCellY, '          3.   Payment By Consignation to Court');

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, '              4.   Services (depending upon agreement');
        $spreadsheet->getActiveSheet()->setCellValue('F' . $this->lastFilledOutCellY, '          4.   Payment thru proper office');

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, '                      between client/ offended party) ');

        $spreadsheet->getActiveSheet()->getStyle('A' . $savedLastFilledOutCellY . ':R' . $this->lastFilledOutCellY)->getFont()->setBold(true);

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
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, 'II. PETITIONERS');
        $spreadsheet->getActiveSheet()->getStyle('A' . $this->lastFilledOutCellY)->getFont()->setBold(true);
        $spreadsheet->getActiveSheet()
            ->getStyle("A" . $this->lastFilledOutCellY . ":R" . $this->lastFilledOutCellY)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()
            ->getStyle("A" . $this->lastFilledOutCellY . ":R" . $this->lastFilledOutCellY)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $this->buildBody($spreadsheet, $rows['PETITIONER'], 'PETITIONER');
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function header(): Spreadsheet
    {
        return $this->prepare();
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function prepare(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $textAndCoordinates = [
            'R1' => 'PPA-PLD-FR-004', 'A2' => 'Table I.B.3   RESTITUTION - CIVIL LIABILITY INDEMNIFICATION', 'A5' => "CLIENT'S NAME",
            'B5' => 'SEX', 'E5' => 'TOTAL AMOUNT (4)', 'I5' => 'PAYMENT (5)', 'R5' => 'REMARKS', 'D6' => 'OFFENSE ', 'E6' => 'CIVIL',
            'G6' => 'AMOUNT PAID THIS QTR.', 'H6' => ' BALANCE END OF QTR.', 'I6' => 'FORM', 'J6' => 'MODE', 'K6' => 'DATE', 'L6' => 'AMOUNT',
            'M6' => 'Received by', 'N6' => ' REMITTED', 'B7' => '(2)', 'D7' => '(Please Specify)', 'E7' => 'LIABILITY',
            'N7' => ' To Offended Party (Indicate name if thru Representative)', 'P7' => 'AMOUNT', 'Q7' => 'DATE', 'B8' => 'F', 'C8' => 'M',
            'E8' => 'ORIGINAL', 'F8' => 'START OF', 'A9' => '(1)', 'D9' => '(3)', 'E9' => 'AMOUNT', 'F9' => 'QUARTER', 'R9' => '(6)',
            'A10' => 'I.  ACTIVE SUPERVISION', 'A11' => '(ALL clients  with CL))'
        ];

        $wrapTextCoordinates = ["A5:R8"];

        $mergesCoordinates = [
            'A5:A8', 'B5:C6', 'B7:C7', 'B8:B9', 'C8:C9', 'E5:H5', 'I5:Q5', 'G6:G9', 'H6:H9', 'I6:I9',
            'J6:J9', 'K6:K9', 'L6:L9', 'M6:M9', 'N6:Q6', 'N7:O9', 'P7:P9', 'Q7:Q9', 'R5:R8'
        ];

        $boldCoordinates = ['R1', 'A2', 'R9', 'D9', 'A10', 'A11'];

        $verticalAlignedCoordinates = ['A5:R9' => 'center'];
        $horizontalAlignedCoordinates = ['A5:R9' => 'center'];

        $adjustedColumnWidthCoordinates = [
            'A' => 35, 'B' => 4, 'C' => 4, 'D' => 35, 'E' => 15, 'F' => 15, 'G' => 15, 'H' => 15, 'I' => 15, 'J' => 15, 'K' => 15,
            'L' => 15, 'M' => 15, 'P' => 12,'R' => 35
        ];

        $outlineBorderThinCoordinates = [
            'A5:A9','B5:C7','B8:B9','C8:C9','D5:D9', 'E5:H5', 'E6:F7', 'E8:E9', 'F8:F9', 'G6:G9', 'H6:H9',
            'I5:Q5', 'I6:I9', 'J6:J9', 'K6:K9', 'L6:L9', 'M6:M9', 'N6:Q6', 'N7:O9', 'P7:P9', 'Q7:Q9', 'R5:R9'
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

        foreach ($outlineBorderThinCoordinates as $coordinate) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        }

        $spreadsheet->getActiveSheet()->getStyle("A10:R11")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('N7:O9')->getFont()->setSize(10);

        return $spreadsheet;
    }

    /**
     * @param Spreadsheet $spreadsheet
     * @param array<string, mixed> $rows
     * @param string $groupType
     * @return Spreadsheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Exception
     */
    private function buildBody(Spreadsheet $spreadsheet, array $rows, string $groupType): Spreadsheet
    {
        $totalData[$groupType] = [
            'originalAmount' => 0,
            'startOfQuarter' => 0,
            'amountPaid' => 0,
            'balance' => 0,
            'formAmount' => 0,
            'remittedAmount' => 0
        ];

        foreach ($rows as $row) {
            $this->lastFilledOutCellY++;
            $fullName = $row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name'];

            $spreadsheet->getActiveSheet()->getRowDimension($this->lastFilledOutCellY)->setRowHeight(50);
            $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, $fullName);

            if ($row['gender'] === 'F') {
                $spreadsheet->getActiveSheet()->setCellValue("B" . $this->lastFilledOutCellY, '∕');
            } else {
                $spreadsheet->getActiveSheet()->setCellValue('C' . $this->lastFilledOutCellY, '∕');
            }

            $spreadsheet->getActiveSheet()->setCellValue('D' . $this->lastFilledOutCellY, $row['offense']);
            $spreadsheet->getActiveSheet()->setCellValue('E' . $this->lastFilledOutCellY, $row['original_amount']);
            $totalData[$row['rj_group']]['originalAmount'] += floatval($row['original_amount']);
            $spreadsheet->getActiveSheet()->setCellValue('F' . $this->lastFilledOutCellY, $row['start_of_quarter']);
            $totalData[$row['rj_group']]['startOfQuarter'] += floatval($row['start_of_quarter']);
            $spreadsheet->getActiveSheet()->setCellValue('G' . $this->lastFilledOutCellY, $row['amount_paid']);
            $totalData[$row['rj_group']]['amountPaid'] += floatval($row['amount_paid']);
            $spreadsheet->getActiveSheet()->setCellValue('H' . $this->lastFilledOutCellY, $row['balance']);
            $totalData[$row['rj_group']]['balance'] += floatval($row['balance']);
            $spreadsheet->getActiveSheet()->setCellValue('I' . $this->lastFilledOutCellY, $row['payment_form']);
            $spreadsheet->getActiveSheet()->setCellValue('J' . $this->lastFilledOutCellY, $row['payment_mode']);
            $spreadsheet->getActiveSheet()->setCellValue(
                'K' . $this->lastFilledOutCellY,
                $this->appDateHelper->convertStringToImmutableDate($row['payment_date'])->format('Y-m-d'));
            $spreadsheet->getActiveSheet()->setCellValue('L' . $this->lastFilledOutCellY, $row['payment_amount']);
            $totalData[$row['rj_group']]['formAmount'] += floatval($row['balance']);
            $spreadsheet->getActiveSheet()->setCellValue('M' . $this->lastFilledOutCellY, $row['payment_recipient']);
            $spreadsheet->getActiveSheet()->setCellValue('O' . $this->lastFilledOutCellY, $row['remitted_to']);
            $spreadsheet->getActiveSheet()->setCellValue('P' . $this->lastFilledOutCellY, $row['remitted_amount']);
            $totalData[$row['rj_group']]['remittedAmount'] += floatval($row['remitted_amount']);
            // TODO: Add remitted date
            $spreadsheet->getActiveSheet()->setCellValue('Q' . $this->lastFilledOutCellY, 'Remitted Date');
            $spreadsheet->getActiveSheet()->setCellValue('R' . $this->lastFilledOutCellY, $row['remarks']);
            $spreadsheet->getActiveSheet()
                ->getStyle("A" . $this->lastFilledOutCellY . ":R" . $this->lastFilledOutCellY)
                ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        $spreadsheet->getActiveSheet()->getStyle('A12:R' . $this->lastFilledOutCellY)->getAlignment()->setWrapText(true);
        $spreadsheet->getActiveSheet()->getStyle('A12:R' . $this->lastFilledOutCellY)->getAlignment()->setVertical('center');
        $spreadsheet->getActiveSheet()->getStyle('A12:R' . $this->lastFilledOutCellY)->getAlignment()->setHorizontal('center');

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()
            ->getStyle("A" . $this->lastFilledOutCellY . ":R" . $this->lastFilledOutCellY)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $this->lastFilledOutCellY++;
        $spreadsheet->getActiveSheet()->setCellValue('D' . $this->lastFilledOutCellY, 'TOTAL');
        $spreadsheet->getActiveSheet()->setCellValue('K' . $this->lastFilledOutCellY, 'TOTAL');
        $spreadsheet->getActiveSheet()->setCellValue('O' . $this->lastFilledOutCellY, 'TOTAL');
        $spreadsheet->getActiveSheet()->setCellValue('E' . $this->lastFilledOutCellY, number_format($totalData[$groupType]['originalAmount'], 2));
        $spreadsheet->getActiveSheet()->setCellValue('F' . $this->lastFilledOutCellY, number_format($totalData[$groupType]['startOfQuarter'], 2));
        $spreadsheet->getActiveSheet()->setCellValue('G' . $this->lastFilledOutCellY, number_format($totalData[$groupType]['amountPaid'], 2));
        $spreadsheet->getActiveSheet()->setCellValue('H' . $this->lastFilledOutCellY, number_format($totalData[$groupType]['balance'], 2));
        $spreadsheet->getActiveSheet()->setCellValue('L' . $this->lastFilledOutCellY, number_format($totalData[$groupType]['formAmount'], 2));
        $spreadsheet->getActiveSheet()->setCellValue('P' . $this->lastFilledOutCellY, number_format($totalData[$groupType]['remittedAmount'], 2));

        $spreadsheet->getActiveSheet()
            ->getStyle("A" . $this->lastFilledOutCellY . ":R" . $this->lastFilledOutCellY)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()
            ->getStyle('I' . $this->lastFilledOutCellY . ':J' . $this->lastFilledOutCellY)
            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('80808080');
        $spreadsheet->getActiveSheet()
            ->getStyle('M' . $this->lastFilledOutCellY . ':N'  . $this->lastFilledOutCellY)
            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('80808080');
        $spreadsheet->getActiveSheet()
            ->getStyle('P' . $this->lastFilledOutCellY . ':R'  . $this->lastFilledOutCellY)
            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('80808080');
        $spreadsheet->getActiveSheet()->getStyle('A' . $this->lastFilledOutCellY . ':R' . $this->lastFilledOutCellY)->getAlignment()->setVertical('center');
        $spreadsheet->getActiveSheet()->getStyle('A' . $this->lastFilledOutCellY . ':R' . $this->lastFilledOutCellY)->getAlignment()->setHorizontal('center');


        return $spreadsheet;
    }

    private function getData(array $data): array
    {
        $result = $this->service->getRJIB3Data(
            $data['quarter_id'],
            $data['field_office_id']
        );

        return ['rows' => array_values($result['data'] ?? [])];
    }
}