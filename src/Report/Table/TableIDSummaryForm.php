<?php

declare(strict_types=1);

namespace App\Report\Table;

use App\Entity\FieldOffices;
use App\Entity\Quarters;
use App\Enum\SystemSettingNames;
use App\Repository\FieldOfficesRepository;
use App\Repository\QuartersRepository;
use App\Service\Volunteerism\IdSupportInterface;
use App\Service\Volunteerism\SupportOfRegionToFieldOfficeInterface;
use App\Service\Volunteerism\Volunteer;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TableIDSummaryForm implements Form
{
    private const TABLE_NAME = "TableIDSummaryForm";

    public function __construct(
        private IdSupportInterface                      $service,
        private FieldOfficesRepository                  $fieldOfficesRepository,
        private QuartersRepository                     $quartersRepository,
        private ?FieldOffices                           $fieldOffice = null,
        private ?Quarters                              $quarters = null,
        private array                                  $data = [],
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
        $this->data['results'] = $this->service->getIdSupportReport($quarterId, $fieldOfficeId)['data'] ?? [];
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
        $supportedFieldOffices = ['Personnel' => [], 'VPA' => []];
        $programs = [];
        $cellsY = [
            'TC'=> 7,
            'RJ'=> 8,
            'VPAs'=> 9,
            'GAD'=> 10,
            'OTHERS'=> 11,
        ];
        $cellsX = ['Personnel' => 'B', 'VPA' => 'C'];
        $typesTotal = ['Personnel' => 0, 'VPA' => 0];

        $spreadsheet = $this->header();

        foreach ($this->data['results'] as $support) {
            $program = $support['program'];
            $type = $support['type'];

            if (! isset($programs[$program][$type])) {
                $programs[$program][$type] = 0;
            }

            $programs[$program][$type]++;
            $typesTotal[$type]++;

            if (! in_array($support['field_office'], $supportedFieldOffices[$type])) {
                $supportedFieldOffices[$type][] = $support['field_office'];
            }
        }

        foreach ($programs as $program => $item) {
            foreach ($item as $type => $score) {
                $cellY = $cellsY[$program];
                $cellX = $cellsX[$type];

                $spreadsheet->getActiveSheet()->setCellValue($cellX . $cellY, $score);
            }
        }

        $spreadsheet->getActiveSheet()->setCellValue('B12', $typesTotal['Personnel'] ?? 0);
        $spreadsheet->getActiveSheet()->setCellValue('C12', $typesTotal['VPA'] ?? 0);

        $spreadsheet->getActiveSheet()->setCellValue('B14', \count($supportedFieldOffices['Personnel']));
        $spreadsheet->getActiveSheet()->setCellValue('C14', \count($supportedFieldOffices['VPA']));

        return $spreadsheet;
    }

    public function header(): Spreadsheet
    {
        $spreadsheet = $this->prepare();
        $spreadsheet->getActiveSheet()->getStyle('A5:C12')->getBorders()
            ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('A14:C14')->getBorders()
            ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $spreadsheet;
    }


    private function prepare(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $textAndCoordinates = [
            'C1' => 'Field Office IQPR FORM' . $this->data[SystemSettingNames::GENERATED_REPORTS_CODE],
            'A1' => 'FIELD OFFICE ' . $this->fieldOffice->getName(),
            'A2' => 'IQPR SUMMARY FORM',
            'A3' => $this->quarters->getName() . ' QTR, ' . $this->quarters->getYear(),
            'A4' => 'I.D   SUPPORT TO OTHER FOs ON TC, RJ, VPAs and GAD IMPLEMENTATION',
            'A5' => 'PARTICULARS',
            'B5' => 'TOTAL NUMBER OF',
            'B6' => 'PERSONNEL',
            'C6' => 'VPAs',
            'A7' => 'TCLP',
            'A8' => 'RJ',
            'A9' => 'VPAs',
            'A10' => 'GAD',
            'A11' => 'OTHERS (PWD / SC)',
            'A12' => 'TOTAL',
            'A14' => 'Number of Field Offices Assisted',
        ];

        $verticalAlignedCoordinates = ['A5:C14' => 'center'];

        $horizontalAlignedCoordinates = ['A5:C14' => 'center'];

        $adjustedColumnWidthCoordinates = [
            'A' => 30, 'B' => 30, 'C' => 30
        ];

        foreach ($textAndCoordinates as $coordinate => $text) {
            $spreadsheet->getActiveSheet()->setCellValue($coordinate, $text);
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
}