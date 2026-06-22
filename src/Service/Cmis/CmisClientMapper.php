<?php

declare(strict_types=1);

namespace App\Service\Cmis;

use App\Entity\FieldOffices;
use App\Model\Clients as ClientModel;
use App\Repository\FieldOfficesRepository;

class CmisClientMapper
{
    public function __construct(
        private CmisClientDefaults $defaults,
        private FieldOfficesRepository $fieldOfficesRepository,
    ){}

    /**
     * @param array<string, mixed> $row
     * @return array{client: ClientModel|null, errors: array<string, string>, source: array<string, mixed>}
     */
    public function map(array $row, string $cmisSource): array
    {
        $errors = [];
        $defaults = $this->defaults->resolveForSource($cmisSource);
        $errors = array_merge($errors, $defaults['errors']);

        $cmisId = (int) ($row['id'] ?? 0);
        if ($cmisId <= 0) {
            $errors['cmisId'] = 'CMIS row is missing a valid id.';
        }

        $names = $this->mapNames($row, $cmisSource);
        if ($names['fullName'] === null && ($names['firstName'] === '' || $names['lastName'] === '')) {
            $errors['name'] = 'CMIS row does not contain a usable first and last name or probationer full name.';
        }

        $fieldOffice = $this->resolveFieldOffice($row);
        if ($fieldOffice === null) {
            $errors['fieldOfficeId'] = 'CMIS row field office does not match a PMEIS field office.';
        }

        [$supervisionStart, $supervisionEnd] = $this->mapSupervisionDates($row, $cmisSource);
        if ($supervisionStart === null) {
            $errors['supervisionStart'] = 'CMIS row is missing a valid supervision start date.';
        }

        if ($supervisionEnd === null) {
            $errors['supervisionEnd'] = 'CMIS row is missing a valid supervision end date.';
        }

        $caseClassification = $this->mapCaseClassification($row, $cmisSource);

        if ($errors !== []) {
            return [
                'client' => null,
                'errors' => $errors,
                'source' => $this->source($row, $cmisSource, $names, $caseClassification, $supervisionStart, $supervisionEnd),
            ];
        }

        $values = $defaults['values'];

        return [
            'client' => new ClientModel(
                $cmisId,
                (int) $values['clientTypeId'],
                $names['firstName'],
                $names['lastName'],
                (string) $values['gender'],
                (string) $values['dateOfBirth'],
                (string) $values['offenseCategory'],
                (bool) $values['isSeniorCitizen'],
                (bool) $values['isPwd'],
                $supervisionStart,
                $supervisionEnd,
                (int) $fieldOffice->getFieldOfficeId(),
                $names['middleName'],
                $names['fullName'],
                $names['suffix'],
                $this->cleanString($row['alias'] ?? null) ?: null,
                $values['clientRemarksId'] !== null ? (int) $values['clientRemarksId'] : null,
                $this->cleanString($row['docket_no'] ?? null) ?: null,
                $caseClassification,
                $this->cleanString($row['Y_M'] ?? null) ?: null,
                $cmisSource,
            ),
            'errors' => [],
            'source' => $this->source($row, $cmisSource, $names, $caseClassification, $supervisionStart, $supervisionEnd),
        ];
    }

    /**
     * @param array<string, mixed> $row
     * @return array{firstName: string, middleName: ?string, lastName: string, suffix: ?string, fullName: ?string}
     */
    private function mapNames(array $row, string $cmisSource): array
    {
        $firstName = $this->cleanString($row['fname'] ?? null);
        $middleName = $this->cleanString($row['mname'] ?? null);
        $lastName = $this->cleanString($row['lname'] ?? null);
        $suffix = $this->cleanString($row['suffixname'] ?? null);
        $probationer = $this->cleanString($row['probationer'] ?? null);

        if ($cmisSource === CmisSource::F5T7 && $firstName !== '' && $lastName !== '') {
            return [
                'firstName' => $firstName,
                'middleName' => $middleName !== '' ? $middleName : null,
                'lastName' => $lastName,
                'suffix' => $suffix !== '' ? $suffix : null,
                'fullName' => null,
            ];
        }

        if ($probationer !== '') {
            return [
                'firstName' => '',
                'middleName' => null,
                'lastName' => '',
                'suffix' => $suffix !== '' ? $suffix : null,
                'fullName' => $probationer,
            ];
        }

        if ($firstName !== '' && $lastName !== '') {
            return [
                'firstName' => $firstName,
                'middleName' => $middleName !== '' ? $middleName : null,
                'lastName' => $lastName,
                'suffix' => $suffix !== '' ? $suffix : null,
                'fullName' => null,
            ];
        }

        return [
            'firstName' => '',
            'middleName' => null,
            'lastName' => '',
            'suffix' => null,
            'fullName' => null,
        ];
    }

    /**
     * @param array<string, mixed> $row
     * @return array{0: ?string, 1: ?string}
     */
    private function mapSupervisionDates(array $row, string $cmisSource): array
    {
        if ($cmisSource === CmisSource::F5T11) {
            $disposedDate = $this->normalizeDate($row['disposed_date'] ?? null);

            return [$disposedDate, $disposedDate];
        }

        $supervisionStart = $this->normalizeDate($row['probation_start'] ?? null)
            ?? $this->normalizeDate($row['received_date'] ?? null);
        $supervisionEnd = $this->normalizeDate($row['probation_end'] ?? null)
            ?? $this->normalizeDate($row['received_date'] ?? null)
            ?? $supervisionStart;

        return [$supervisionStart, $supervisionEnd];
    }

    /**
     * @param array<string, mixed> $row
     */
    private function mapCaseClassification(array $row, string $cmisSource): ?string
    {
        if ($cmisSource === CmisSource::F5T11) {
            $decision = $this->cleanString($row['disposed_decision'] ?? null);

            return $decision !== '' ? substr($decision, 0, 50) : null;
        }

        $classification = $this->cleanString($row['case_classification'] ?? null);

        return $classification !== '' ? $classification : null;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function resolveFieldOffice(array $row): ?FieldOffices
    {
        $fieldOfficeId = (int) ($row['field_office_id'] ?? 0);
        if ($fieldOfficeId > 0) {
            $fieldOffice = $this->fieldOfficesRepository->isExistingById($fieldOfficeId);
            if ($fieldOffice instanceof FieldOffices) {
                return $fieldOffice;
            }
        }

        $fieldOfficeName = $this->cleanString($row['field_office'] ?? null);
        if ($fieldOfficeName === '' || strtoupper($fieldOfficeName) === 'ALL') {
            return null;
        }

        foreach ($this->fieldOfficesRepository->findBy(['deletedAt' => null]) as $fieldOffice) {
            if ($this->normalize($fieldOffice->getName()) === $this->normalize($fieldOfficeName)) {
                return $fieldOffice;
            }
        }

        return null;
    }

    private function normalizeDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = (string) $value;
        if (str_starts_with($value, '0000-00-00')) {
            return null;
        }

        try {
            $date = new \DateTimeImmutable($value);
            if ((int) $date->format('Y') < 1900) {
                return null;
            }

            return $date->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }

    private function cleanString(mixed $value): string
    {
        return CmisRowSanitizer::sanitizeString(trim(preg_replace('/\s+/', ' ', (string) ($value ?? ''))));
    }

    private function normalize(?string $value): string
    {
        return strtolower($this->cleanString($value));
    }

    /**
     * @param array{firstName: string, middleName: ?string, lastName: string, suffix: ?string, fullName: ?string} $names
     * @return array<string, mixed>
     */
    private function source(
        array $row,
        string $cmisSource,
        array $names,
        ?string $caseClassification,
        ?string $supervisionStart,
        ?string $supervisionEnd,
    ): array {
        $source = [
            'cmisSource' => $cmisSource,
            'cmisId' => isset($row['id']) ? (int) $row['id'] : null,
            'docketNo' => $this->cleanString($row['docket_no'] ?? null) ?: null,
            'probationer' => $this->cleanString($row['probationer'] ?? null) ?: null,
            'fieldOffice' => $this->cleanString($row['field_office'] ?? null) ?: null,
            'fieldOfficeId' => isset($row['field_office_id']) ? (int) $row['field_office_id'] : null,
            'yearMonth' => $this->cleanString($row['Y_M'] ?? null) ?: null,
            'caseClassification' => $caseClassification,
            'firstName' => $names['firstName'],
            'middleName' => $names['middleName'],
            'lastName' => $names['lastName'],
            'fullName' => $names['fullName'],
            'supervisionStart' => $supervisionStart,
            'supervisionEnd' => $supervisionEnd,
        ];

        if ($cmisSource === CmisSource::F5T11) {
            $source['disposedDecision'] = $this->cleanString($row['disposed_decision'] ?? null) ?: null;
            $source['disposedDate'] = $this->normalizeDate($row['disposed_date'] ?? null);
            $source['transfer'] = $this->cleanString($row['transfer'] ?? null) ?: null;
        }

        return $source;
    }
}
