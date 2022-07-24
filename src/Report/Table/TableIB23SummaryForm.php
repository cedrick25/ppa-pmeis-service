<?php

declare(strict_types=1);

namespace App\Report\Table;

use App\Repository\FieldOfficesRepository;
use App\Repository\QuartersRepository;
use App\Service\RestorativeJustice\RelatedRestitutions;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TableIB23SummaryForm implements Form
{
    private const TABLE_NAME = "TableIB23SummaryForm";
    private const ACTIVE_SUPERVISION = 'ACTIVE_SUPERVISION';
    private const PETITIONERS = 'PETITIONER';
    private const CLIENTS_WITH_CL = 'CLIENTS_WITH_CL';
    private const TOTAL_CL_ORIGINAL = 'TOTAL_CL_ORIGINAL';
    private const CL_START_OF_QUARTER = 'CL_START_OF_QUARTER';
    private const CLIENTS_WHO_PAID = 'CLIENTS_WHO_PAID';
    private const TOTAL_AMOUNT_PAID = 'TOTAL_AMOUNT_PAID';
    private const BALANCE_END_OF_QUARTER = 'BALANCE_END_OF_QUARTER';
    private const TOTAL_REMITTED_AMOUNT = 'TOTAL_REMITTED_AMOUNT';

    public function __construct(
        private RelatedRestitutions    $service,
        private FieldOfficesRepository  $fieldOfficesRepository,
        private QuartersRepository     $quartersRepository,
        private array                  $data = [],
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
        $quarterId = intval($data['quarter_id']);
        $fieldOfficeId = intval($data['field_office_id']);

        $this->fieldOffice = $this->fieldOfficesRepository->find($fieldOfficeId);
        $this->quarters = $this->quartersRepository->find($quarterId);
        $this->data = $this->getData($quarterId, $fieldOfficeId);

        $spreadsheet = $this->footer();
        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");

        $filePath = $_ENV['XLSX_PATH_FILE'] . self::TABLE_NAME . "-" . time() . ".xlsx";
        $writer->save($filePath);

        return new BinaryFileResponse($filePath);
    }

    public function footer(): Spreadsheet
    {
        $spreadsheet = $this->body();

        return $spreadsheet;
    }

    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();

        $spreadsheet->getActiveSheet()->setCellValue('B8', count($this->data[self::ACTIVE_SUPERVISION][self::CLIENTS_WITH_CL]));
        $spreadsheet->getActiveSheet()->setCellValue('C8', $this->data[self::ACTIVE_SUPERVISION][self::TOTAL_CL_ORIGINAL]);
        $spreadsheet->getActiveSheet()->setCellValue('D8', $this->data[self::ACTIVE_SUPERVISION][self::CL_START_OF_QUARTER]);
        $spreadsheet->getActiveSheet()->setCellValue('E8', count($this->data[self::ACTIVE_SUPERVISION][self::CLIENTS_WHO_PAID]));
        $spreadsheet->getActiveSheet()->setCellValue('F8', $this->data[self::ACTIVE_SUPERVISION][self::TOTAL_AMOUNT_PAID]);
        $spreadsheet->getActiveSheet()->setCellValue('G8', $this->data[self::ACTIVE_SUPERVISION][self::BALANCE_END_OF_QUARTER]);
        $spreadsheet->getActiveSheet()->setCellValue('G8', $this->data[self::ACTIVE_SUPERVISION][self::TOTAL_REMITTED_AMOUNT]);
        $spreadsheet->getActiveSheet()->setCellValue('B9', count($this->data[self::PETITIONERS][self::CLIENTS_WITH_CL]));
        $spreadsheet->getActiveSheet()->setCellValue('C9', $this->data[self::PETITIONERS][self::TOTAL_CL_ORIGINAL]);
        $spreadsheet->getActiveSheet()->setCellValue('D9', $this->data[self::PETITIONERS][self::CL_START_OF_QUARTER]);
        $spreadsheet->getActiveSheet()->setCellValue('E9', count($this->data[self::PETITIONERS][self::CLIENTS_WHO_PAID]));
        $spreadsheet->getActiveSheet()->setCellValue('F9', $this->data[self::PETITIONERS][self::TOTAL_AMOUNT_PAID]);
        $spreadsheet->getActiveSheet()->setCellValue('G9', $this->data[self::PETITIONERS][self::BALANCE_END_OF_QUARTER]);
        $spreadsheet->getActiveSheet()->setCellValue('H9', $this->data[self::PETITIONERS][self::TOTAL_REMITTED_AMOUNT]);

        return $spreadsheet;
    }

    public function header(): Spreadsheet
    {
        $spreadsheet = $this->prepare();
        $spreadsheet->getActiveSheet()->getStyle('A6:H9')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $spreadsheet;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function prepare(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $textAndCoordinates = [
            'A1' => 'FIELD OFFICE ' . $this->fieldOffice->getName(),
            'A2' => 'IQPR SUMMARY FORM',
            'A3' => $this->quarters->getName() . ' QTR, ' . $this->quarters->getYear(),
            'A4' => 'I.B.3   RESTORATIVE JUSTICE',
            'A5' => 'Tables I.B.3',
            'A6' => 'CLIENTS',
            'B6' => 'CIVIL LIABILITIES',
            'B7' => 'TOTAL # OF CLIENTS W/ CL',
            'C7' => 'TOTAL CL (ORIGINAL)',
            'D7' => 'CL Start of Quarter',
            'E7' => 'TOTAL # OF CLIENTS WHO PAID',
            'F7' => 'TOTAL AMOUNT PAID',
            'G7' => 'BALANCE (End of Qtr)',
            'H7' => "TOTAL AMT. REMITTED/ RECEIVED BY VICTIMS' BENEFICIARIES",
            'A8' => 'Active Supervision',
            'A9' => 'Petitioners',
        ];

        $mergesCoordinates = ['A6:A7', 'B6:H6'];

        $verticalAlignedCoordinates = ['A6:H9' => 'center'];

        $horizontalAlignedCoordinates = ['A6:H9' => 'center'];

        $adjustedColumnWidthCoordinates = ['A' => 25, 'B' => 25, 'C' => 25, 'D' => 25, 'E' => 25, 'F' => 25, 'G' => 25, 'H' => 35];

        foreach ($textAndCoordinates as $coordinate => $text) {
            $spreadsheet->getActiveSheet()->setCellValue($coordinate, $text);
        }

        foreach ($mergesCoordinates as $coordinate) {
            $spreadsheet->getActiveSheet()->mergeCells($coordinate);
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

    private function getData(int $quarterId, int $fieldOfficeId): array
    {

        $initialData = [
            self::CLIENTS_WITH_CL => [],
            self::TOTAL_CL_ORIGINAL => 0,
            self::CL_START_OF_QUARTER => 0,
            self::CLIENTS_WHO_PAID => [],
            self::TOTAL_AMOUNT_PAID => 0,
            self::BALANCE_END_OF_QUARTER => 0,
            self::TOTAL_REMITTED_AMOUNT => 0,
        ];

        $data = [
            self::ACTIVE_SUPERVISION => $initialData,
            self::PETITIONERS => $initialData
        ];
        $restitutions = $this->service->getRJIB3Data($quarterId, $fieldOfficeId);

        foreach ($restitutions['data'] as $restitution) {
            $rjGroup = $restitution['rj_group'];

            if (! in_array($restitution['client_id'], $data[$rjGroup][self::CLIENTS_WITH_CL])) {
                $data[$rjGroup][self::CLIENTS_WITH_CL][] = $restitution['client_id'];
            }

            $data[$rjGroup][self::TOTAL_CL_ORIGINAL] += intval($restitution['original_amount']);
            $data[$rjGroup][self::CL_START_OF_QUARTER] += intval($restitution['start_of_quarter']);

            if (! in_array($restitution['client_id'], $data[$rjGroup][self::CLIENTS_WHO_PAID]) && intval($restitution['amount_paid']) > 0) {
                $data[$rjGroup][self::CLIENTS_WHO_PAID][] = $restitution['client_id'];
            }

            $data[$rjGroup][self::TOTAL_AMOUNT_PAID] += intval($restitution['payment_amount']);
            $data[$rjGroup][self::BALANCE_END_OF_QUARTER] += intval($restitution['balance']);
            $data[$rjGroup][self::TOTAL_REMITTED_AMOUNT] += intval($restitution['remitted_amount']);
        }

        return $data;
    }
}