<?php

namespace App\Report\Table;

use App\Service\Volunteerism\ProgramMaterialsDevelopment;
use App\Entity\FieldOffices;
use App\Entity\Quarters;
use App\Repository\FieldOfficesRepository;
use App\Repository\QuartersRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TableVSummaryForm implements Form
{
    private const TABLE_NAME = "TableVSummaryForm";


    public function __construct(
        private ProgramMaterialsDevelopment $programMaterialsDevelopmentService,
        private FieldOfficesRepository      $fieldOfficesRepository,
        private QuartersRepository          $quartersRepository,
        private array                       $data = [],
        private ?FieldOffices               $fieldOffice = null,
        private ?Quarters                   $quarters = null,
    ) {}

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

    public function header(): Spreadsheet
    {
        $spreadsheet = $this->prepare();

        $thinBorders = [
            "A7:C15"
        ];

        foreach ($thinBorders as $coordinate) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)
                ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }
        return $spreadsheet;
    }

    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();

        $count = [
            'TC' => 0,
            'RJ' => 0,
            'VPA' => 0,
            'GAD' => 0,
            'OTHERS' => 0,
        ];

        $result = $this->data;

        if ($result['rows']) {
            foreach ($result['rows'] as $v) {
                $count[$v['program']] ++;
            }
        }

        $spreadsheet->getActiveSheet()->setCellValue('B9', $count['TC']);
        $spreadsheet->getActiveSheet()->setCellValue('B10', $count['RJ']);
        $spreadsheet->getActiveSheet()->setCellValue('B11', $count['VPA']);
        $spreadsheet->getActiveSheet()->setCellValue('B12', $count['GAD']);
        $spreadsheet->getActiveSheet()->setCellValue('B13', $count['OTHERS']);
        $spreadsheet->getActiveSheet()->setCellValue('B15', array_sum($count));

        $spreadsheet->getActiveSheet()->setCellValue('C9', 0);
        $spreadsheet->getActiveSheet()->setCellValue('C10', 0);
        $spreadsheet->getActiveSheet()->setCellValue('C11', 0);
        $spreadsheet->getActiveSheet()->setCellValue('C12', 0);
        $spreadsheet->getActiveSheet()->setCellValue('C13', 0);
        $spreadsheet->getActiveSheet()->setCellValue('C15', 0);

        return $spreadsheet;
    }

    public function footer(): Spreadsheet
    {
        return $this->body();
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
            'A4' => 'V. PROGRAM AND MATERIALS DEVELOPMENT',
            'A5' => 'Table V. Materials/ Session Plans Developed and Used for Agency Program',
            'A7' => 'Program',
            'A9' => '1. Therapeutic Community',
            'A10' => '2. Restorative Justice',
            'A11' => '3. Volunteerism',
            'A12' => '4. Gender and Development',
            'A13' => '5. Others',
            'A15' => 'T O T A L',
            'B7' => 'NUMBER OF',
            'B8' => 'Materials/ Session Plans Developed',
            'C8' => 'Materials Reproduced/Distributed (If applicable)',
        ];

        $mergesCoordinates = [
            'A1:C1', 'A2:C2', 'A3:C3', 'A4:C4', 'A5:C5', 'A7:A8', 'B7:C7',
        ];

        $boldCoordinates = [
            'A1:C8',
        ];

        $verticalAlignedCoordinates = [
            'A1:C1' => 'center',
            'A2:C2' => 'center',
            'A3:C3' => 'center',
            'A7:A8' => 'center',
            'B7:C7' => 'center',
            'B8:C15' => 'center',
        ];

        $horizontalAlignedCoordinates = [
            'A1:C1' => 'center',
            'A2:C2' => 'center',
            'A3:C3' => 'center',
            'A7:A8' => 'center',
            'B7:C7' => 'center',
            'B8:C15' => 'center',
        ];

        $adjustedColumnWidthCoordinates = [
            'A' => 35, 
            'B' => 25,
            'C' => 25,
        ];

        $wrappedTextCoordinates = [
            'A1:C10'
        ];

        foreach ($textAndCoordinates as $coordinate => $text) {
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

        foreach ($wrappedTextCoordinates as $coordinate => $width) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)
                ->getAlignment()->setWrapText(true);
        }

        return $spreadsheet;
    }

    private function getData(array $data): array
    {
        $this->fieldOffice = $this->fieldOfficesRepository->find($data['field_office_id']);
        $this->quarters = $this->quartersRepository->find($data['quarter_id']);

        $results = $this->programMaterialsDevelopmentService
            ->getIdSupportReport($data['quarter_id'], $data['field_office_id']);
    
        return ['rows' => array_values($results['data'] ?? [])];
    }
}