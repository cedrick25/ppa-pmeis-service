<?php

namespace App\Service\Summary\FieldOffice;

use App\Repository\QuartersRepository;
use App\Repository\SessionsRepository;

class TherapeuticCommunity
{
    public function __construct(
        private SessionsRepository $sessionsRepository,
        private QuartersRepository $quartersRepository,
    ){}

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getTC1(int $quarterId, int $fieldOfficeId): array
    {
        $quarterData = $this->quartersRepository->find($quarterId);

        if ($quarterData === null) {
            return [];
        }
        $minMaxDate = $this->quartersRepository->getQuarterMinMaxDate($quarterData);

        $result = [];
        $items = $this->sessionsRepository->fetchTC1FieldOfficeSummary($minMaxDate['min'], $minMaxDate['max'], $fieldOfficeId);

        foreach ($items as $item) {
            $treatmentCategory = explode('-', $item['treatment_category']);
            if (! isset($result[$treatmentCategory[0]][$treatmentCategory[1]])) {
                $result[$treatmentCategory[0]][$treatmentCategory[1]] = 0;
            }

            $result[$treatmentCategory[0]][$treatmentCategory[1]]++;
        }

        return $result;
    }
}