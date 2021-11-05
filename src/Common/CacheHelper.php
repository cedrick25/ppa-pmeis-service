<?php

namespace App\Common;

class CacheHelper
{
    public function getExpirationDateTime(int $hour = 24): \DateTime
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
        return 'quarters_all';
    }

    public function getQuartersPaginatedKey(int $page, int $size): string
    {
        return 'quarters_' . $page . '_' . $size;
    }

    public function getAllPhasesKey(): string
    {
        return 'phases_all';
    }

    public function getPhasesPaginatedKey(int $page, int $size): string
    {
        return 'phases_' . $page . '_' . $size;
    }

    public function getAllFieldOfficesKey(): string
    {
        return 'field_offices_all';
    }

    public function getFieldOfficePaginatedKey(int $page, int $size): string
    {
        return 'field_offices_' . $page . '_' . $size;
    }

    public function getFieldOfficeByRegionKey(int $regionId): string
    {
        return 'field_offices_region_' . $regionId;
    }

    public function getAllRegionsKey(): string
    {
        return 'regions_all';
    }

    public function getRegionsPaginatedKey(int $page, int $size): string
    {
        return 'regions_' . $page . '_' . $size;
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

    public function getClientTypesPaginatedKey(int $page, int $size): string
    {
        return 'client_types_' . $page . '_' . $size;
    }

    public function getAllTreatmentCategoriesKey(): string
    {
        return 'treatment_categories_all';
    }

    public function getAllClientsKey(): string
    {
        return 'clients_all';
    }

    public function getClientsPaginatedKey(int $page, int $size): string
    {
        return 'clients_' . $page . '_' . $size;
    }

    public function getAllClientSessionsKey(): string
    {
        return 'client_sessions_all';
    }

    public function getClientSessionsPaginatedKey(int $page, int $size): string
    {
        return 'client_sessions_' . $page . '_' . $size;
    }

    public function getAllVolunteersKey(): string
    {
        return 'volunteers_all';
    }

    public function getAllResourceFacilitatorSessionsKey(): string
    {
        return 'resource_facilitator_session_all';
    }

    public function getResourceFacilitatorSessionsPaginatedKey(int $page, int $size): string
    {
        return 'resource_facilitator_sessions_' . $page . '_' . $size;
    }

    public function getAllPositionsKey(): string
    {
        return 'positions_all';
    }

    public function getPositionsPaginatedKey(int $page, int $size): string
    {
        return 'positions_' . $page . '_' . $size;
    }

}