<?php

namespace App\Service\System;

use App\Enum\AuditTrailActions;
use App\Repository\FieldOfficesRepository;
use App\Repository\QuartersRepository;
use App\Repository\RegionsRepository;

class AuditTrailActionDetailsTransformer
{
    public function __construct(
        private RegionsRepository $regionsRepository,
        private QuartersRepository $quartersRepository,
        private FieldOfficesRepository $fieldOfficesRepository,
    ) {

    }

    /**
     * @param string $action
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function transform(string $action, array $data): array
    {
        return match ($action) {
            AuditTrailActions::GENERATE_REPORT => $this->transformGenerateReport($data),
            AuditTrailActions::CREATE => $this->transformCreate($data),
        };
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function transformGenerateReport(array $data): array
    {
        $build = ['tableName' => $data['table_name']];

        return $this->transformDefaults($data, $build);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function transformCreate(array $data): array
    {
        $build = [
            'createdId' => $data['createdId'],
            'module' => $data['module'],
        ];

        return $this->transformDefaults($data, $build);
    }

    private function transformDefaults(array $data, array $build): array
    {
        if (isset($data['region_id']) || isset($data['regionId'])) {
            $regionKey = isset($data['regionId']) ? 'regionId' : 'region_id';
            $region = $this->regionsRepository->find($data[$regionKey]);

            $build['region'] = $region->getName();
        }

        if (isset($data['quarter_id']) || isset($data['quarterId'])) {
            $quarterKey = isset($data['quarterId']) ? 'quarterId' : 'quarter_id';
            $quarter = $this->quartersRepository->find($data[$quarterKey]);

            $build['quarter'] = $quarter->getName();
        }

        if (isset($data['field_office_id']) || isset($data['fieldOfficeId'])) {
            $fieldOfficeKey = isset($data['fieldOfficeId']) ? 'fieldOfficeId' : 'field_office_id';
            $fieldOffice = $this->fieldOfficesRepository->find($data[$fieldOfficeKey]);

            $build['fieldOffice'] = $fieldOffice->getName();
        }

        return $build;
    }
}