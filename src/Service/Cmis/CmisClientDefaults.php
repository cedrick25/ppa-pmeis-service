<?php

declare(strict_types=1);

namespace App\Service\Cmis;

use App\Repository\ClientRemarksRepository;
use App\Repository\ClientTypesRepository;
use App\Repository\SystemCodeSettingsRepository;

class CmisClientDefaults
{
    private const CLIENT_TYPE_CODE = 'CMIS Default Client Type Code';
    private const GENDER = 'CMIS Default Gender';
    private const DATE_OF_BIRTH = 'CMIS Default Date Of Birth';
    private const IS_PWD = 'CMIS Default PWD';
    private const IS_SENIOR_CITIZEN = 'CMIS Default Senior Citizen';
    private const OFFENSE_CATEGORY = 'CMIS Default Offense Category';
    private const CLIENT_REMARKS_ID = 'CMIS Default Client Remarks ID';

    public function __construct(
        private SystemCodeSettingsRepository $settingsRepository,
        private ClientTypesRepository $clientTypesRepository,
        private ClientRemarksRepository $clientRemarksRepository,
    ){}

    /**
     * @return array{values: array<string, mixed>, errors: array<string, string>}
     */
    public function resolveForSource(string $cmisSource): array
    {
        $resolved = $this->resolve();
        $clientTypeCode = \App\Service\Cmis\CmisSource::defaultClientTypeCode($cmisSource);
        $clientType = $this->clientTypesRepository->findOneByCode($clientTypeCode);

        if ($clientType === null) {
            $resolved['errors']['clientTypeId'] = sprintf(
                'CMIS client type "%s" for source "%s" was not found.',
                $clientTypeCode,
                $cmisSource
            );
        } else {
            $resolved['values']['clientTypeId'] = $clientType->getClientTypeId();
            unset($resolved['errors']['clientTypeId']);
        }

        return $resolved;
    }

    /**
     * @return array{values: array<string, mixed>, errors: array<string, string>}
     */
    public function resolve(): array
    {
        $errors = [];
        $clientTypeCode = $this->getSetting(self::CLIENT_TYPE_CODE, 'PS');
        $clientType = $this->clientTypesRepository->findOneByCode($clientTypeCode);
        if ($clientType === null) {
            $errors['clientTypeId'] = sprintf('Configured CMIS default client type "%s" was not found.', $clientTypeCode);
        }

        $gender = strtoupper($this->getSetting(self::GENDER, 'M'));
        if (!in_array($gender, ['M', 'F'], true)) {
            $errors['gender'] = 'Configured CMIS default gender must be M or F.';
        }

        $dateOfBirth = $this->getSetting(self::DATE_OF_BIRTH, '1900-01-01');
        if (!$this->isValidDate($dateOfBirth)) {
            $errors['dateOfBirth'] = 'Configured CMIS default date of birth must be a valid YYYY-MM-DD date.';
        }

        $offenseCategory = strtoupper($this->getSetting(self::OFFENSE_CATEGORY, 'NDO'));
        if (!in_array($offenseCategory, ['DO', 'NDO'], true)) {
            $errors['offenseCategory'] = 'Configured CMIS default offense category must be DO or NDO.';
        }

        $clientRemarksId = (int) $this->getSetting(self::CLIENT_REMARKS_ID, '1');
        if ($clientRemarksId > 0 && $this->clientRemarksRepository->find($clientRemarksId) === null) {
            $errors['clientRemarksId'] = sprintf('Configured CMIS default client remarks ID "%d" was not found.', $clientRemarksId);
        }

        return [
            'values' => [
                'clientTypeId' => $clientType?->getClientTypeId(),
                'gender' => $gender,
                'dateOfBirth' => $dateOfBirth,
                'offenseCategory' => $offenseCategory,
                'isPwd' => $this->toBool($this->getSetting(self::IS_PWD, 'false')),
                'isSeniorCitizen' => $this->toBool($this->getSetting(self::IS_SENIOR_CITIZEN, 'false')),
                'clientRemarksId' => $clientRemarksId > 0 ? $clientRemarksId : null,
            ],
            'errors' => $errors,
        ];
    }

    private function getSetting(string $name, string $fallback): string
    {
        $setting = $this->settingsRepository->getByName($name);

        if ($setting === false || $setting->getValue() === null || $setting->getValue() === '') {
            return $fallback;
        }

        return $setting->getValue();
    }

    private function toBool(string $value): bool
    {
        return in_array(strtolower($value), ['1', 'true', 'yes', 'y'], true);
    }

    private function isValidDate(string $value): bool
    {
        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value;
    }
}
