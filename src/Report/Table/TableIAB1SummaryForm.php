<?php

namespace App\Report\Table;

use App\Entity\FieldOffices;
use App\Entity\Quarters;
use App\Repository\FieldOfficesRepository;
use App\Repository\QuartersRepository;
use App\Service\RestorativeJustice\RelatedActivities;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TableIAB1SummaryForm implements Form
{
    private const TABLE_NAME = "TableIAB1SummaryForm";


    public function __construct(
        private RelatedActivities      $service,
        private FieldOfficesRepository $fieldOfficesRepository,
        private QuartersRepository     $quartersRepository,
        private array                  $data = [],
        private ?FieldOffices          $fieldOffice = null,
        private ?Quarters              $quarters = null,
    ) {}

    public function supports(string $tableName): bool
    {
        return self::TABLE_NAME === $tableName;
    }

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
        $spreadsheet->getActiveSheet()->getStyle('A7:Y10')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getStyle('A13:Y16')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $spreadsheet;
    }

    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();
        $coordinateX = ['ACTIVE_SUPERVISION' => 10, 'PETITIONER' => 16];

        $coordinateY = [
            'Pre-Encounter Activities' => ['Conducted' => 'A', 'F' => 'B', 'M' => 'C', 'PWD' => 'D', 'SC' => 'E'],
            'Mediation' => ['Conducted' => 'F', 'F' => 'G', 'M' => 'H', 'PWD' => 'I', 'SC' => 'J'],
            'Conferencing' => ['Conducted' => 'K', 'F' => 'L', 'M' => 'M', 'PWD' => 'N', 'SC' => 'O'],
            'COS' => ['Conducted' => 'P', 'F' => 'Q', 'M' => 'R', 'PWD' => 'S', 'SC' => 'T'],
            'Others' => ['Conducted' => 'U', 'F' => 'V', 'M' => 'W', 'PWD' => 'X', 'SC' => 'Y']
        ];

        foreach ($this->data as $type=>$items) {
            foreach ($items as $process=>$item) {
                foreach ($item as $label=>$value) {
                    $coordinate = $coordinateY[$process][$label] . $coordinateX[$type];
                    $spreadsheet->getActiveSheet()->setCellValue($coordinate, $value);
                }
            }
        }

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
            'A4' => 'I.B.   RESTORATIVE JUSTICE',
            'A5' => "Table I.B.   Number of RJ Processes Conducted/ Clients' Involvement",
            'A6' => "TOTAL NUMBER (ACTIVE SUPERVISION)",
            'A7' => 'Pre-Encounter Activities',
            'F7' => 'Mediation',
            'K7' => 'Conferencing',
            'P7' => 'COS',
            'U7' => 'Others',
            'A8' => '# of Acts',
            'B8' => 'SEX',
            'D8' => 'PWD',
            'E8' => 'SC',
            'F8' => 'Sessions',
            'G8' => 'Sex',
            'I8' => 'PWD',
            'J8' => 'SC',
            'K8' => 'Sessions',
            'L8' => 'Sex',
            'N8' => 'PWD',
            'O8' => 'SC',
            'P8' => 'Sessions',
            'Q8' => 'Sex',
            'S8' => 'PWD',
            'T8' => 'SC',
            'U8' => 'Sessions',
            'V8' => 'Sex',
            'X8' => 'PWD',
            'Y8' => 'SC',
            'A9' => 'Conducted',
            'B9' => 'F',
            'C9' => 'M',
            'F9' => 'Conducted',
            'G9' => 'F',
            'H9' => 'M',
            'K9' => 'Conducted',
            'L9' => 'F',
            'M9' => 'M',
            'P9' => 'Conducted',
            'Q9' => 'F',
            'R9' => 'M',
            'U9' => 'Conducted',
            'V9' => 'F',
            'W9' => 'M',
            'A12' => "TOTAL NUMBER (PETITIONERS)",
            'A13' => 'Pre-Encounter Activities',
            'F13' => 'Mediation',
            'K13' => 'Conferencing',
            'P13' => 'COS',
            'U13' => 'Others',
            'A14' => '# of Acts',
            'B14' => 'SEX',
            'D14' => 'PWD',
            'E14' => 'SC',
            'F14' => 'Sessions',
            'G14' => 'Sex',
            'I14' => 'PWD',
            'J14' => 'SC',
            'K14' => 'Sessions',
            'L14' => 'Sex',
            'N14' => 'PWD',
            'O14' => 'SC',
            'P14' => 'Sessions',
            'Q14' => 'Sex',
            'S14' => 'PWD',
            'T14' => 'SC',
            'U14' => 'Sessions',
            'V14' => 'Sex',
            'X14' => 'PWD',
            'Y14' => 'SC',
            'A15' => 'Conducted',
            'B15' => 'F',
            'C15' => 'M',
            'F15' => 'Conducted',
            'G15' => 'F',
            'H15' => 'M',
            'K15' => 'Conducted',
            'L15' => 'F',
            'M15' => 'M',
            'P15' => 'Conducted',
            'Q15' => 'F',
            'R15' => 'M',
            'U15' => 'Conducted',
            'V15' => 'F',
            'W15' => 'M',
        ];

        $mergesCoordinates = [
            'A7:E7', 'F7:O7', 'P7:T7', 'U7:Y7', 'A13:E13', 'F13:O13', 'P13:T13', 'U13:Y13',
        ];

        $boldCoordinates = [
            'A1:Y7', 'A12:Y13',
        ];

        $verticalAlignedCoordinates = [
            'A1:Y15' => 'center'
        ];

        $horizontalAlignedCoordinates = [
            'A1:Y15' => 'center'
        ];

        $adjustedColumnWidthCoordinates = [
            'A' => 25, 'F' => 25, 'K' => 25, 'P' => 25,  'U' => 25,
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

        return $spreadsheet;
    }

    private function getData(array $data): array
    {
        $response = ['ACTIVE_SUPERVISION' => [], 'PETITIONER' => []];
        $this->fieldOffice = $this->fieldOfficesRepository->find($data['field_office_id']);
        $this->quarters = $this->quartersRepository->find($data['quarter_id']);
        $RJIB2Data = $this->service->getRJIB2Data(intval($data['quarter_id']), intval($data['field_office_id']));

        foreach ($RJIB2Data['data'] as $item) {
            $rjGroup = $item['rj_group'];
            $rjProcess = $item['rj_process'];
            $gender = $item['gender'];

            if (!isset($response[$rjGroup][$rjProcess])) {
                $response[$rjGroup][$rjProcess] = [];
            }

            if (!isset($response[$rjGroup][$rjProcess]['Conducted'])) {
                $response[$rjGroup][$rjProcess]['Conducted'] = 0;
            }
            $response[$rjGroup][$rjProcess]['Conducted']++;

            if (!isset($response[$rjGroup][$rjProcess][$gender])) {
                $response[$rjGroup][$rjProcess][$gender] = 0;
            }
            $response[$rjGroup][$rjProcess][$gender]++;

            if (intval($item['is_pwd'])) {
                if (!isset($response[$rjGroup][$rjProcess]['isPwd'])) {
                    $response[$rjGroup][$rjProcess]['isPwd'] = 0;
                }
                $response[$rjGroup][$rjProcess]['isPwd']++;
            }

            if (intval($item['is_senior_citizen'])) {
                if (!isset($response[$rjGroup][$rjProcess]['isSC'])) {
                    $response[$rjGroup][$rjProcess]['isSC'] = 0;
                }
                $response[$rjGroup][$rjProcess]['isSC']++;
            }
        }

        return $response;
    }
}