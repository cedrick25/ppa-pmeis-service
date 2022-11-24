<?php

namespace App\Report\Table;

use App\Service\Volunteerism\SocialMarketing;
use App\Entity\FieldOffices;
use App\Entity\Quarters;
use App\Repository\ClientSessionsRepository;
use App\Repository\ClientsRepository;
use App\Repository\FieldOfficesRepository;
use App\Repository\QuartersRepository;
use App\Repository\SessionsRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TableIIIA1SummaryForm implements Form
{
    private const TABLE_NAME = "TableIIIA1SummaryForm";


    public function __construct(
        private SocialMarketing $service,
        private FieldOfficesRepository      $fieldOfficesRepository,
        private QuartersRepository          $quartersRepository,
        private SessionsRepository          $sessionsRepository,
        private ClientSessionsRepository    $clientSessionsRepository,
        private ClientsRepository           $clientsRepository,
        private array                       $data = [],
        private array                       $sessionIds = [],
        private ?FieldOffices               $fieldOffice = null,
        private ?Quarters                   $quarters = null,
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

    public function header(): Spreadsheet
    {
        $spreadsheet = $this->prepare();

        $thinBorders = [
            "A7:D15"
        ];

        foreach ($thinBorders as $coordinate) {
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }
        return $spreadsheet;
    }

    public function body(): Spreadsheet
    {
        $spreadsheet = $this->header();

        $result = $this->data;

        $smCategories = [
            'fora' => [
                'activities'   => 0,
                'participants' => 0,
                'primers'      => 0,
            ],
            'press' => [
                'activities'   => 0,
                'primers'      => 0,
            ],
            'radio' => [
                'activities'   => 0,
                'primers'      => 0,
            ],
            'tv' => [
                'activities'   => 0,
                'primers'      => 0,
            ],
        ];

        // print_r($this->data);

        if ($result['rows']) {
            foreach ($result['rows'] as $v) {
                switch($v['social_marketing_activity_id']) {
                    case 1:
                        $smCategories['fora']['activities']++;

                        foreach ($v['participants'] as $participant) {
                            $smCategories['fora']['participants']+= $participant['no'];
                        }

                        $smCategories['fora']['primers']+= $v['primers'];
                        break;
                    case 2:
                        $smCategories['press']['activities']++;
                        $smCategories['press']['primers']+= $v['primers'];
                        break;
                    case 3:
                        $smCategories['radio']['activities']++;
                        $smCategories['radio']['primers']+= $v['primers'];
                        break;
                    case 4:
                        $smCategories['tv']['activities']++;
                        $smCategories['tv']['primers']+= $v['primers'];
                        break;
                }
            }
        }

        $spreadsheet->getActiveSheet()->setCellValue('B9', $smCategories['fora']['activities']);
        $spreadsheet->getActiveSheet()->setCellValue('C9', $smCategories['fora']['participants']);
        $spreadsheet->getActiveSheet()->setCellValue('D9', $smCategories['fora']['primers']);
        
        $spreadsheet->getActiveSheet()->setCellValue('B12', $smCategories['press']['activities']);
        $spreadsheet->getActiveSheet()->setCellValue('B13', $smCategories['radio']['activities']);
        $spreadsheet->getActiveSheet()->setCellValue('B14', $smCategories['tv']['activities']);
        $spreadsheet->getActiveSheet()->setCellValue('B15', $smCategories['fora']['activities'] + $smCategories['press']['activities'] + $smCategories['radio']['activities'] + $smCategories['tv']['activities']);

        $spreadsheet->getActiveSheet()->setCellValue('D12', $smCategories['press']['primers']);
        $spreadsheet->getActiveSheet()->setCellValue('D13', $smCategories['radio']['primers']);
        $spreadsheet->getActiveSheet()->setCellValue('D14', $smCategories['tv']['primers']);
        $spreadsheet->getActiveSheet()->setCellValue('D15', $smCategories['fora']['primers'] + $smCategories['press']['primers'] + $smCategories['radio']['primers'] + $smCategories['tv']['primers']);

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
            'A4' => 'III. SOCIAL MARKETING',
            'A5' => 'Table III.A.1 - INFORMATION DISSEMINATION',
            'A7' => 'Activity',
            'A9' => '1. Fora/Symposia',
            'A10' => '2. Media Exposure',
            'A12' => '2.1 Print (Publication/Press Releases)',
            'A13' => '2.2 Radio Interviews/Guestings',
            'A14' => '2.3 TV Guestings',
            'A15' => 'TOTAL',

            'B7' => 'Total Number',
            'B8' => 'Activities Conducted',
            'C8' => 'Participants',
            'D8' => 'Primers Distributed',

            'B10' => 'Total Number',
            'B11' => 'Activities Conducted',
            'D11' => 'Primers Distributed',
        ];

        $mergesCoordinates = [
            'A1:D1',
            'A2:D2',
            'A3:D3',
            'A4:D4',
            'A5:D5',
            'A7:A8',
            'A10:A11',
            'B7:D7',
            'B10:D10',
            'B11:C11',
            'B12:C12',
            'B13:C13',
            'B14:C14',
            'B15:C15',
        ];

        $boldCoordinates = [
            'A1:D8',
            'B10:D11',
        ];

        $verticalAlignedCoordinates = [
            'A1:D1' => 'center',
            'A2:D2' => 'center',
            'A3:D3' => 'center',
            'A4:D4' => 'center',
            'A5:D5' => 'center',
            'A7:A8' => 'center',
            'B7:D7' => 'center',
            'B10:D10' => 'center',
            'B11:C11' => 'center',
            'A15' => 'center',
        ];

        $horizontalAlignedCoordinates = [
            'A1:D1' => 'center',
            'A2:D2' => 'center',
            'A3:D3' => 'center',
            'A4:D4' => 'center',
            'A5:D5' => 'center',
            'A7:A8' => 'center',
            'B7:D7' => 'center',
            'B8:D8' => 'center',
            'B10:D10' => 'center',
            'B11:C11' => 'center',
            'A15' => 'center',
        ];

        $adjustedColumnWidthCoordinates = [
            'A' => 35, 
            'B' => 15,
            'C' => 15,
            'D' => 20,
            // 'G' => 15
        ];

        $wrappedTextCoordinates = [
            // 'A1:D15'
            'B8'
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
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getAlignment()->setWrapText(true); 
        }

        return $spreadsheet;
    }

    private function getData(array $data): array
    {
        $this->fieldOffice = $this->fieldOfficesRepository->find($data['field_office_id']);
        $this->quarters = $this->quartersRepository->find($data['quarter_id']);

        $result = $this->service->getReport(
            $data['quarter_id'],
            $data['field_office_id'],
            'INFORMATION_DISSEMINATION',
        );

        return ['rows' => array_values($result['data'] ?? [])];
    }
}