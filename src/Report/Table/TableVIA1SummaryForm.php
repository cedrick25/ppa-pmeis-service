<?php

namespace App\Report\Table;

use App\Service\Volunteerism\JailDecongestion;
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

class TableVIA1SummaryForm implements Form
{
    private const TABLE_NAME = "TableVIA1SummaryForm";


    public function __construct(
        private JailDecongestion            $service,
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
            "A7:I12"
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

        $jailDecongestion = [
            'jail'        => 0,
            'office'      => 0,
            'probation'   => 0,
            'clemency'    => 0,
            'pao'         => 0,
            'prosecution' => 0,
            'others'      => 0,
            'msec'        => 0,
            'release'     => 0,
        ];

        if ($result['rows']) {
            foreach ($result['rows'] as $v) {

                if ($v['jail_venue']) {
                    $jailDecongestion['jail'] += 1;
                }  

                if ($v['jail_office']) {
                    $jailDecongestion['office'] += 1;
                } 

                $jailDecongestion['probation']   += $v['probation'];
                $jailDecongestion['clemency']    += $v['clemency'];
                $jailDecongestion['pao']         += $v['referral_pao'];
                $jailDecongestion['prosecution'] += $v['referral_prosecution'];
                $jailDecongestion['others']      += $v['referral_others'];
                $jailDecongestion['msec']        += $v['gcta'];
                $jailDecongestion['release']     += $v['recognizance'];
            }
        }

        $spreadsheet->getActiveSheet()->setCellValue('A10', $jailDecongestion['jail']);
        $spreadsheet->getActiveSheet()->setCellValue('B10', $jailDecongestion['office']);
        $spreadsheet->getActiveSheet()->setCellValue('C10', $jailDecongestion['probation']);
        $spreadsheet->getActiveSheet()->setCellValue('D10', $jailDecongestion['clemency']);
        $spreadsheet->getActiveSheet()->setCellValue('E10', $jailDecongestion['pao']);
        $spreadsheet->getActiveSheet()->setCellValue('F10', $jailDecongestion['prosecution']);
        $spreadsheet->getActiveSheet()->setCellValue('G10', $jailDecongestion['others']);
        $spreadsheet->getActiveSheet()->setCellValue('H10', $jailDecongestion['msec']);
        $spreadsheet->getActiveSheet()->setCellValue('I10', $jailDecongestion['release']);

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
            'A4' => 'VI. SUPPORT FUNCTION',
            'A5' => 'Table VI.A.1. Jail Decongestion Services/ Activities',

            'A7' => 'No.of Jail Decongestion Services/Activities Conducted ',
            'A9' => 'Jail',

            'B9' => 'Office',

            'C7' => 'No. of Inmates Assisted for',
            'C8' => 'Intake Interview',
            'C9' => 'Probation',
            'D9' => 'Pre-Parole Executive Clemency',

            'E8' => 'Referrals',
            'E9' => 'PAO',
            'F9' => 'Prosecution',
            'G9' => 'Others',

            'H8' => 'MSEC / GCTA',
            'I8' => 'Release on Recognizance',
        ];

        $mergesCoordinates = [
            'A1:I1',
            'A2:I2',
            'A3:I3',
            'A4:I4',
            'A5:I5',

            'A7:B8',
            'C7:I7',
            'C8:D8',
            'E8:G8',
            'H8:H9',
            'I8:I9',
        ];

        $boldCoordinates = [
            'A1:I9',
        ];

        $verticalAlignedCoordinates = [
            'A1:I9' => 'center',
        ];

        $horizontalAlignedCoordinates = [
            'A1:I9' => 'center',
        ];

        $adjustedColumnWidthCoordinates = [
            'A' => 10, 
            'B' => 12, 
            'C' => 12, 
            'D' => 12, 
            'E' => 15, 
            'F' => 15, 
            'G' => 15, 
            'H' => 10, 
            'I' => 15, 
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
            $spreadsheet->getActiveSheet()->getStyle($coordinate)->getAlignment()->setWrapText(true); 
        }

        return $spreadsheet;
    }

    private function getData(array $data): array
    {
        $this->fieldOffice = $this->fieldOfficesRepository->find($data['field_office_id']);
        $this->quarters = $this->quartersRepository->find($data['quarter_id']);

        $results = $this->service
            ->getReport($data['quarter_id'], $data['field_office_id']);
    
        return ['rows' => array_values($results['data'] ?? [])];
    }
}