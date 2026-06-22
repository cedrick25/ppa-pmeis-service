<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\FieldOffices;
use App\Service\Cmis\CmisRowSanitizer;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

abstract class AbstractCmisClientTableRepository
{
    public function __construct(
        protected Connection $cmisConnection,
        protected FieldOfficesRepository $fieldOfficesRepository,
    ){}

    abstract protected function getTableName(): string;

    /**
     * @return string[]
     */
    abstract protected function getSearchColumns(): array;

    /**
     * @return array<string, mixed>
     * @throws \Doctrine\DBAL\Exception
     */
    public function paginated(
        int $pageSize = 10,
        ?int $fieldOfficeId = null,
        ?string $yearMonth = null,
        ?string $search = null,
        ?int $cursor = null
    ): array {
        $pageSize = max(1, $pageSize);
        [$where, $params, $types] = $this->buildWhere($fieldOfficeId, $yearMonth, $search);

        if ($cursor !== null && $cursor > 0) {
            $where .= ' AND id < :cursor';
            $params['cursor'] = $cursor;
            $types['cursor'] = ParameterType::INTEGER;
        }

        $fetchSize = $pageSize + 1;
        $params['limit'] = $fetchSize;
        $types['limit'] = ParameterType::INTEGER;

        $sql = sprintf(
            'SELECT %s FROM %s WHERE %s ORDER BY id DESC LIMIT :limit',
            $this->getSelectColumns(),
            $this->getTableName(),
            $where
        );

        $rows = CmisRowSanitizer::sanitizeRows(
            $this->cmisConnection->executeQuery($sql, $params, $types)->fetchAllAssociative()
        );

        $hasMore = count($rows) > $pageSize;
        if ($hasMore) {
            array_pop($rows);
        }

        $nextCursor = null;
        if ($hasMore && $rows !== []) {
            $nextCursor = (int) $rows[array_key_last($rows)]['id'];
        }

        return [
            'itemCount' => null,
            'pageCount' => null,
            'hasMore' => $hasMore,
            'nextCursor' => $nextCursor,
            'items' => $rows,
        ];
    }

    /**
     * @return array<string, mixed>|false
     * @throws \Doctrine\DBAL\Exception
     */
    public function findActiveById(int $cmisId): array|false
    {
        $sql = sprintf(
            'SELECT %s FROM %s WHERE status = 1 AND id = :id',
            $this->getSelectColumns(),
            $this->getTableName()
        );

        $row = $this->cmisConnection
            ->executeQuery($sql, ['id' => $cmisId])
            ->fetchAssociative();

        if ($row === false) {
            return false;
        }

        return CmisRowSanitizer::sanitizeRow($row);
    }

    /**
     * @param int[]|null $cmisIds
     * @return array<int, array<string, mixed>>
     * @throws \Doctrine\DBAL\Exception
     */
    public function findActiveForSync(
        ?int $fieldOfficeId = null,
        ?string $yearMonth = null,
        ?array $cmisIds = null,
        int $limit = 500
    ): array {
        [$where, $params, $types] = $this->buildWhere($fieldOfficeId, $yearMonth, null);

        if ($cmisIds !== null && count($cmisIds) > 0) {
            $where .= ' AND id IN (:ids)';
            $params['ids'] = $cmisIds;
            $types['ids'] = Connection::PARAM_INT_ARRAY;
        }

        $params['limit'] = max(1, $limit);
        $types['limit'] = ParameterType::INTEGER;

        $sql = sprintf(
            'SELECT %s FROM %s WHERE %s ORDER BY id ASC LIMIT :limit',
            $this->getSelectColumns(),
            $this->getTableName(),
            $where
        );

        return CmisRowSanitizer::sanitizeRows(
            $this->cmisConnection->executeQuery($sql, $params, $types)->fetchAllAssociative()
        );
    }

    protected function getSelectColumns(): string
    {
        return implode(', ', $this->getSelectColumnList());
    }

    /**
     * @return string[]
     */
    abstract protected function getSelectColumnList(): array;

    /**
     * @return array{0: string, 1: array<string, mixed>, 2: array<string, int|string>}
     */
    protected function buildWhere(?int $fieldOfficeId, ?string $yearMonth, ?string $search): array
    {
        $where = 'status = 1';
        $params = [];
        $types = [];

        if ($fieldOfficeId !== null && $fieldOfficeId > 0) {
            $fieldOffice = $this->fieldOfficesRepository->isExistingById($fieldOfficeId);
            if ($fieldOffice instanceof FieldOffices) {
                $where .= ' AND field_office = :field_office_name';
                $params['field_office_name'] = $fieldOffice->getName();
            }
        }

        if ($yearMonth !== null && $yearMonth !== '') {
            $where .= ' AND Y_M = :year_month';
            $params['year_month'] = $yearMonth;
        }

        if ($search !== null && trim($search) !== '') {
            $searchConditions = array_map(
                fn (string $column) => sprintf('%s LIKE :search', $column),
                $this->getSearchColumns()
            );
            $where .= ' AND (' . implode(' OR ', $searchConditions) . ')';
            $params['search'] = '%' . trim($search) . '%';
        }

        return [$where, $params, $types];
    }
}
