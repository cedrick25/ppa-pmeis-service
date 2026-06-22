<?php

declare(strict_types=1);

namespace App\Repository;

class CmisF5T11Repository extends AbstractCmisClientTableRepository
{
    protected function getTableName(): string
    {
        return 'f5t11';
    }

    /**
     * @return string[]
     */
    protected function getSelectColumnList(): array
    {
        return [
            'id', 'docket_no', 'probationer', 'fname', 'mname', 'lname', 'suffixname', 'alias',
            'disposed_decision', 'disposed_date', 'extension_probation', 'transfer', 'reason_other',
            'Y_M', 'source', 'field_office', 'field_office_id', 'status', 'created_date', 'created_by',
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
