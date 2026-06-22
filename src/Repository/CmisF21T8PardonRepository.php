<?php

declare(strict_types=1);

namespace App\Repository;

class CmisF21T8PardonRepository extends AbstractCmisClientTableRepository
{
    protected function getTableName(): string
    {
        return 'f21t8_pardon';
    }

    /**
     * @return string[]
     */
    protected function getSelectColumnList(): array
    {
        return [
            'id', 'docket_no', 'probationer', 'referral_type', 'received_date', 'case_classification',
            'supervising_officer', 'probation_start', 'probation_end', 'Y_M', 'source', 'field_office',
            'field_office_id', 'status', 'created_date', 'created_by',
        ];
    }

    /**
     * @return string[]
     */
    protected function getSearchColumns(): array
    {
        return ['docket_no', 'probationer'];
    }
}
