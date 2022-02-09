<?php

declare(strict_types=1);

namespace App\Report\Table;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VPAIC4 implements Form
{
    private const TABLE_NAME = "VPAIC4";
    
    public function __construct(
        private int   $lastFilledOutCellY = 8,
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
        $this->lastFilledOutCellY++;
        $lastFilledOutCellY = $this->lastFilledOutCellY;

        $spreadsheet->getActiveSheet()->setCellValue('b' . $this->lastFilledOutCellY, 'NOTE:    ATTACH PROGRAM/ INVITATION / ACTIVITY REPORT OR DOCUMENTATION/ PICTURES');
        $spreadsheet->getActiveSheet()->mergeCells('b' . $this->lastFilledOutCellY . ':j' . $this->lastFilledOutCellY);

        $spreadsheet->getActiveSheet()->getStyle('B' . $lastFilledOutCellY . ':O' . $this->lastFilledOutCellY)->getFont()->setBold(true);
        
        return $spreadsheet;
    }

    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();

        $quarter = $this->data['quarter'];
        $fieldOffice = $this->data['field_office_id'];
        $association =  $this->data['association'];
        $data = [
            'FIRST_2022' => [
                '1' => [
                    'association' => [
                        ['Seminar', 'January 5, 2022 / Brgy. Dela Paz, Pasig', 'Luke Skylar', '', 'X', 'First Speaker', 'N/A', 'N/A', 'N/A',],
                        ['', '', 'Pedro Pandacan', '', 'X', 'Second Speaker', 'N/A', 'N/A', 'N/A',],
                        ['', '', 'Marie Sumapay', 'X', '', 'Third Speaker', 'N/A', 'N/A', 'N/A',],
                        ['', '', 'Renzo Melodez', '', 'X', 'Assistant', 'N/A', 'N/A', 'N/A',],
                        ['', '', '', '', '', '', '', '', '',],
                        ['', '', '', '1', '3', '', '', '', '',],
                    ]
                ],
            ],
            'FOURTH_2021' => [
                '1' => [
                    'association' => [
                        ['Livelihood Training', 'December 10, 2021 / Brgy. Dela Paz, Pasig', 'Danzo Malaypay', '', 'X', 'Master Speaker', 'N/A', 'N/A', 'N/A',],
                        ['', '', 'Marichu Balonzo', 'X', '', 'Trainer 1', 'N/A', 'N/A', 'N/A',],
                        ['', '', 'Benjo Relaza', 'X', '', 'Trainer 2', 'N/A', 'N/A', 'N/A',],
                        ['', '', 'Aliya Gumara', '', 'X', 'Trainer 3', 'N/A', 'N/A', 'N/A',],
                        ['', '', '', '', '', '', '', '', '',],
                        ['', '', '', '2', '2', '', '', '', '',],
                    ]
                ]
            ],
        ];

        $rows = $data[$quarter][$fieldOffice][$association];
        foreach ($rows as $row) {
            $this->lastFilledOutCellY++;
            $spreadsheet->getActiveSheet()->setCellValue("A" . $this->lastFilledOutCellY, $row[0]);
            $spreadsheet->getActiveSheet()->setCellValue("D" . $this->lastFilledOutCellY, $row[1]);
            $spreadsheet->getActiveSheet()->setCellValue("E" . $this->lastFilledOutCellY, $row[2]);
            $spreadsheet->getActiveSheet()->setCellValue("H" . $this->lastFilledOutCellY, $row[3]);
            $spreadsheet->getActiveSheet()->setCellValue("I" . $this->lastFilledOutCellY, $row[4]);
            $spreadsheet->getActiveSheet()->setCellValue("J" . $this->lastFilledOutCellY, $row[5]);

            $spreadsheet->getActiveSheet()->mergeCells('A' . $this->lastFilledOutCellY . ':C' . $this->lastFilledOutCellY);
            $spreadsheet->getActiveSheet()->mergeCells('E' . $this->lastFilledOutCellY . ':G' . $this->lastFilledOutCellY);
            $spreadsheet->getActiveSheet()->mergeCells('J' . $this->lastFilledOutCellY . ':K' . $this->lastFilledOutCellY);
            $spreadsheet->getActiveSheet()->mergeCells('l' . $this->lastFilledOutCellY . ':m' . $this->lastFilledOutCellY);
            $spreadsheet->getActiveSheet()->mergeCells('n' . $this->lastFilledOutCellY . ':o' . $this->lastFilledOutCellY);

        }
        $spreadsheet->getActiveSheet()->setCellValue('A' . $this->lastFilledOutCellY, 'TOTAL (Headcount)');
        $spreadsheet->getActiveSheet()->mergeCells('A' . $this->lastFilledOutCellY . ':g' . $this->lastFilledOutCellY);
        $spreadsheet->getActiveSheet()->getStyle('A' . $this->lastFilledOutCellY . ':G' . $this->lastFilledOutCellY)->getFont()->setBold(true);

        $spreadsheet->getActiveSheet()->getStyle('A9:P' . $this->lastFilledOutCellY)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()
        ->getStyle("j" . $this->lastFilledOutCellY . ":p" . $this->lastFilledOutCellY)
        ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('80808080');
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
            'a1' => 'Table I.C.4 – VPA ASSOCIATION INITIATED ACTIVITIES AND OTHER SERVICES FOR CLIENTS', 'q1' => 'PPA-PLD-FR-004',
            'a3' => ' NAME OF VPA ASSOCIATION  : Federated VPAs of PBRC',
            'H4' => 'Sex', 'L4' => 'Community Resource', 'P4' => 'Remarks',
            'a4' => 'Activities  /  Services Rendered', 'd4' => 'Date / Venue', 'E4' => 'Name of VPAs Involved', 'h5' => '(4)', 'j4' => 'Role / Accomplishment',
            'L4' => 'Community Resource', 'L5' => 'Development (CRD)', 'L6' => '(6)',
            'P5'=>'(To include date of CRD, issues/ ', 'P6'=>'problems encountered & other', 'P7'=> 'relevant information)', 'p8'=> '(7)',
            'a8'=>'(1)', 'D8'=>'(2)', 'e8'=>'(3)', 'h6'=>'F', 'i6'=>'M', 'j8'=>'(5)', 'L7' => 'Name of Resources',  'n7' => 'Assistance ',  'L8' => 'Tapped',  'n8' => 'Received', 
        ];
        $mergesCoordinates = [
            'H4:I4', 'L4:O4',
            'A4:C7', 'D4:D7', 'E4:G7', 'J4:K7', 'L5:O5', 'L6:O6', 'H5:I5',
            'A8:C8', 'E8:G8', 'H6:H8', 'I6:I8', 'J8:K8', 'L7:M7', 'L8:M8', 'N7:O7', 'N8:O8', 'J18:K18', 'L18:M18', 'N18:O18',
        ];
        $boldCoordinates = ['A1','Q1','a3','h5', 'L6', 'a8', 'd8', 'e8', 'j8', 'p8', 'A18'];
        $verticalAlignedCoordinates = ['A4:P18' => 'center'];
        $horizontalAlignedCoordinates = ['A4:P18' => 'center'];
        $adjustedColumnWidthCoordinates = [
            'A' => 10, 'B' => 10, 'C' => 10, 'D' => 40, 'E' => 10, 'F' => 10, 'G' => 10, 'H' => 5, 'I' => 5, 'J' => 10, 'K' => 10, 'L' => 10,
            'M' => 10, 'N' => 10, 'O' => 10, 'P' => 30, 'Q' => 10, 'R' => 10, 'S' => 10, 'T' => 10
        ];
        $outlineBorderThinCoordinates = [
            'A4:C8', 'D4:D8', 'E4:G8', 'H4:I5', 'J4:K8', 'L4:O6', 'P4:P8', 'H6:H8', 'I6:I8', 'L7:M8', 'N7:O8',
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