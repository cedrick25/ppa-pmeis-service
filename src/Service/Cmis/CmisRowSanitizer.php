<?php

declare(strict_types=1);

namespace App\Service\Cmis;

final class CmisRowSanitizer
{
    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    public static function sanitizeRow(array $row): array
    {
        foreach ($row as $key => $value) {
            $row[$key] = self::sanitizeValue($value);
        }

        return $row;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    public static function sanitizeRows(array $rows): array
    {
        return array_map(fn (array $row) => self::sanitizeRow($row), $rows);
    }

    public static function sanitizeString(string $value): string
    {
        if ($value === '') {
            return $value;
        }

        if (!mb_check_encoding($value, 'UTF-8')) {
            $value = mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
        }

        $clean = iconv('UTF-8', 'UTF-8//IGNORE', $value);

        return $clean !== false ? $clean : '';
    }

    private static function sanitizeValue(mixed $value): mixed
    {
        if (is_string($value)) {
            return self::sanitizeString($value);
        }

        if (is_array($value)) {
            return self::sanitizeRow($value);
        }

        return $value;
    }
}
