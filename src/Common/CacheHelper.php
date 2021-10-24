<?php

namespace App\Common;

class CacheHelper
{
    public function getExpirationDateTime(int $hour): \DateTime
    {
        $dateTimeExpiration = new \DateTime();
        $dateTimeExpiration->add(new \DateInterval("PT{$hour}H"));

        return $dateTimeExpiration;
    }

    public function getAccountWithDetailsKey(int $id): string
    {
        return "account_with_details_id:" . $id;
    }

    public function getAccountKey(int $id): string
    {
        return "account_id:" . $id;
    }

    public function getQuarterByNameAndYearKey(string $name, string $year): string
    {
        return 'quarter_' . $name . '_' . $year;
    }

    public function getAllQuartersKey(): string
    {
        return 'quarter_all';
    }

    public function getAllPhasesKey(): string
    {
        return 'phases_all';
    }

    public function getAllFieldOfficesKey(): string
    {
        return 'field_offices_all';
    }

    public function getAllSessionActivitiesKey(): string
    {
        return 'session_activities_all';
    }

    public function getAllVenuesKey(): string
    {
        return 'venues_all';
    }
}