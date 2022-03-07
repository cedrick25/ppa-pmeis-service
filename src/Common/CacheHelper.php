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
        return "account_with_details_id_" . $id;
    }

    public function getAccountKey(int $id): string
    {
        return "account_id_" . $id;
    }

    public function getQuarterByNameAndYearKey(string $name, string $year): string
    {
        return 'quarter_' . $name . '_' . $year;
    }

    public function getAllUsersKey(): string
    {
        return 'users_all';
    }

    public function getUsersPaginatedKey(int $page, int $size): string
    {
        return 'users_' . $page . '_' . $size;
    }

    public function getAllQuartersKey(): string
    {
        return 'quarters_all';
    }

    public function getQuartersPaginatedKey(int $page, int $size): string
    {
        return 'quarters_' . $page . '_' . $size;
    }

    public function getQuartersPaginatedSearchKey(string $field, string $query, int $page, int $size): string
    {
        return 'quarters_' . $field . '_' . $query . '_' . $page . '_' . $size;
    }

    public function getQuartersTCA1Part1Key(int $id, int $fieldOfficeId): string
    {
        return 'quarters_tc_a1_part1_' . $id . '_' . $fieldOfficeId;
    }

    public function getQuartersTCA1Part2Key(int $id, int $fieldOfficeId): string
    {
        return 'quarters_tc_a1_part2_' . $id . '_' . $fieldOfficeId;
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

    public function getSessionActivitiesPaginatedKey(int $page, int $size): string
    {
        return 'session_activities_' . $page . '_' . $size;
    }

    public function getAllVenuesKey(): string
    {
        return 'venues_all';
    }

    public function getVenuesPaginatedKey(int $page, int $size): string
    {
        return 'venues_' . $page . '_' . $size;
    }

    public function getAllSessionsKey(): string
    {
        return 'sessions_all';
    }

    public function getAllSessionsWithClientsAndFacilitatorsKey(): string
    {
        return 'sessions_all_with_client_and_facilitator';
    }

    public function getSessionsPaginatedKey(int $page, int $size): string
    {
        return 'sessions_' . $page . '_' . $size;
    }

    public function getSessionsById(int $id): string
    {
        return 'sessions_by_id_' . $id;
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

    public function getAllClientSessionsBySessionIdKey(int $id): string
    {
        return 'client_sessions_by_session_id' . $id;
    }

    public function getClientSessionsPaginatedKey(int $page, int $size): string
    {
        return 'client_sessions_' . $page . '_' . $size;
    }

    public function getAllVolunteersKey(): string
    {
        return 'volunteers_all';
    }

    public function getVolunteersPaginatedKey(int $page, int $size): string
    {
        return 'volunteers_' . $page . '_' . $size;
    }

    public function getVolunteersByQuarterAndFieldOfficeKey(int $quarterId, int $fieldOfficeId): string
    {
        return 'volunteers_quarter_field_office_' . $quarterId . '_' . $fieldOfficeId;
    }

    public function getAllResourceFacilitatorSessionsKey(): string
    {
        return 'resource_facilitator_session_all';
    }

    public function getAllResourceFacilitatorSessionsBySessionIdKey(int $id): string
    {
        return 'resource_facilitator_session_by_session_id' . $id;
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

    public function getAllRJConductProcessesKey(): string
    {
        return 'rj_conduct_processes_all';
    }

    public function getRJIB1Key(
        int $quarterId,
        int $fieldOffice
    ): string
    {
        return 'rjib1_' . $quarterId . '_' . $fieldOffice;
    }

    public function getAllOffensesKey(): string
    {
        return 'offenses_all';
    }

    public function getAllRJProcessKey(): string
    {
        return 'rj_process_all';
    }

    public function getAllRJProcessStatusKey(): string
    {
        return 'rj_process_status_all';
    }

    public function getAllRJOutcomesKey(): string
    {
        return 'rj_outcomes_all';
    }

    public function getAllRJRelatedActivitiesKey(): string
    {
        return 'rj_related_activities_all';
    }

    public function getRJIB2Key(
        int $quarterId,
        int $fieldOffice
    ): string
    {
        return 'rjib2_' . $quarterId . '_' . $fieldOffice;
    }

    public function getAllPaymentFormsKey(): string
    {
        return 'payment_forms_all';
    }

    public function getAllPaymentModesKey(): string
    {
        return 'payment_modes_all';
    }

    public function getAllRJRelatedRestitutionsKey(): string
    {
        return 'rj_related_restitutions_all';
    }

    public function getRJIB3Key(
        int $quarterId,
        int $fieldOffice
    ): string
    {
        return 'rjib3_' . $quarterId . '_' . $fieldOffice;
    }

    public function getAllVolunteerOperationsKey(): string
    {
        return 'volunteer_operations_all';
    }

    public function getAllVolunteerIdsKey(): string
    {
        return 'volunteer_ids_all';
    }

    public function getAllIdSupportsKey(): string
    {
        return 'id_supports_all';
    }

    public function getAllTechnicalAssistanceKey(): string
    {
        return 'technical_assistance_all';
    }

    public function getAllSocialMarketingKey(): string
    {
        return 'social_marketing_all';
    }

    public function getAllProgramMaterialsDevelopmentKey(): string
    {
        return 'program_materials_development_all';
    }

    public function getAllResourceMobilizationKey(): string
    {
        return 'resource_mobilization_all';
    }

    public function getAllJailDecongestionKey(): string
    {
        return 'jail_decongestion_all';
    }

    public function getAllSpecialAssignmentsKey(): string
    {
        return 'special_assignment_all';
    }

    public function getAllSupportOfRegionToFieldOfficesKey(): string
    {
        return 'all_support_of_region_to_field_offices';
    }

    public function getAllVolunteerSupervisionsKey(): string
    {
        return 'all_volunteer_supervisions';
    }

    public function getAllCapabilityBuildingsKey(): string
    {
        return 'all_capability_buildings';
    }
}