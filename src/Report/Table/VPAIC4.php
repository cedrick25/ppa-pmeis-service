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
        private int   $lastFilledOutCellY = 20,
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
                        // ['', '', 'Pedro Pandacan', '', 'X', 'Second Speaker', 'N/A', 'N/A', 'N/A',],
                        // ['', '', 'Marie Sumapay', 'X', '', 'Third Speaker', 'N/A', 'N/A', 'N/A',],
                        // ['', '', 'Renzo Melodez', '', 'X', 'Assistant', 'N/A', 'N/A', 'N/A',],
                        // ['', '', '', '', '', '', '', '', '',],
                        // ['', '', '', '', '1', '3', '', '', '',],
                    ]
                ],
                '2' => [
                    'association' => [
                        ['name', 'cas', 'date of birth'],
                        ['name', 'M', 'date of birth'],
                        ['name', 'gender', 'date of birth'],
                    ]
                ]
            ],
            'SECOND' => [
                '1' => [
                    'association' => [
                        ['name', 'gender', 'date of birth'],
                        ['name', 'M', 'date of birth'],
                        ['name', 'gender', 'date of birth'],
                    ]
                ]
            ],
        ];

        $rows = $data[$quarter][$fieldOffice][$association];

        $lastFilledOutCell = 3;
        foreach ($rows as $row) {
            // dd($row);
            $spreadsheet->getActiveSheet()->setCellValue("A9", $row[0]);
            $spreadsheet->getActiveSheet()->setCellValue("B9", $row[1]);
            $spreadsheet->getActiveSheet()->setCellValue("C9", $row[2]);
            // $spreadsheet->getActiveSheet()->setCellValue("A11" . $lastFilledOutCell, $row[3]);
            $lastFilledOutCell++;
        }

        $spreadsheet->getActiveSheet()->getStyle('A9:P18')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('J18:P18')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('80808080');

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
            'a8'=>'(1)', 'D8'=>'(2)', 'e8'=>'(3)', 'h6'=>'F', 'i6'=>'M', 'j8'=>'(5)', 'a18' => 'TOTAL  HEADCOUNT', 'b20'=> 'NOTE:    ATTACH PROGRAM/ INVITATION / ACTIVITY REPORT OR DOCUMENTATION/ PICTURES', 'L7' => 'Name of Resources',  'n7' => 'Assistance ',  'L8' => 'Tapped',  'n8' => 'Received', 
        ];
        $mergesCoordinates = [
            'H4:I4', 'L4:O4',
            'A4:C7', 'D4:D7', 'E4:G7', 'J4:K7', 'L5:O5', 'L6:O6', 'H5:I5',
            'A8:C8', 'E8:G8', 'H6:H8', 'I6:I8', 'J8:K8', 'A18:G18','L7:M7', 'L8:M8', 'N7:O7', 'N8:O8', 'J18:K18', 'L18:M18', 'N18:O18',
        ];
        $boldCoordinates = ['A1','Q1','a3','h5', 'L6', 'a8', 'd8', 'e8', 'j8', 'p8', 'b20', 'A18'];
        $verticalAlignedCoordinates = ['A4:P18' => 'center'];
        $horizontalAlignedCoordinates = ['A4:P18' => 'center'];
        $adjustedColumnWidthCoordinates = [
            'A' => 10, 'B' => 20, 'C' => 10, 'D' => 15, 'E' => 10, 'F' => 10, 'G' => 10, 'H' => 10, 'I' => 10, 'J' => 10, 'K' => 10, 'L' => 10,
            'M' => 10, 'N' => 10, 'O' => 10, 'P' => 30, 'Q' => 10, 'R' => 10, 'S' => 10, 'T' => 10
        ];
        $outlineBorderThinCoordinates = [
            'A4:C8', 'D4:D8', 'E4:G8', 'H4:I5', 'J4:K8', 'L4:O6', 'P4:P8', 'H6:H8', 'I6:I8', 'L7:M8', 'N7:O8', 'A18:G18',
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