<?php

declare(strict_types=1);

namespace App\Repository;

class CmisF5T7Repository extends AbstractCmisClientTableRepository
{
    protected function getTableName(): string
    {
        return 'f5t7';
    }

    /**
     * @return string[]
     */
    protected function getSelectColumnList(): array
    {
        return [
            'id', 'docket_no', 'probationer', 'fname', 'mname', 'lname', 'suffixname', 'alias',
            'case_classification', 'received_date', 'supervising_officer', 'probation_start',
            'probation_end', 'Y_M', 'source', 'field_office', 'field_office_id', 'status',
            'created_date', 'created_by',
        ];
    }

    /**
     * @return string[]
     */
    protected function getSearchColumns(): array
    {
        return ['docket_no', 'probationer', 'fname', 'lname'];
    }
}
