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

    public function getAllUsersKey(): string
    {
        return 'users_all';
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

    public function getAllSessionsKey(): string
    {
        return 'sessions_all';
    }

    public function getAllClientTypesKey(): string
    {
        return 'client_types_all';
    }

    public function getAllTreatmentCategoriesKey(): string
    {
        return 'treatment_categories_all';
    }

    public function getAllClientsKey(): string
    {
        return 'clients_all';
    }

    public function getAllClientSessionsKey(): string
    {
        return 'client_sessions_all';
    }

    public function getAllVolunteersKey(): string
    {
        return 'volunteers_all';
    }

    public function getAllResourceFacilitatorSessionsKey(): string
    {
        return 'resource_facilitator_session_all';
    }
}