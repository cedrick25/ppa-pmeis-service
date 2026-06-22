<?php

declare(strict_types=1);

namespace App\Service\Cmis;

final class CmisSource
{
    public const F5T7 = 'f5t7';
    public const F5T11 = 'f5t11';
    public const F21T8_PAROL = 'f21t8_parol';
    public const F21T8_PARDON = 'f21t8_pardon';

    public static function all(): array
    {
        return [
            self::F5T7,
            self::F5T11,
            self::F21T8_PAROL,
            self::F21T8_PARDON,
        ];
    }

    public static function isValid(string $source): bool
    {
        return in_array($source, self::all(), true);
    }

    public static function label(string $source): string
    {
        return match ($source) {
            self::F5T7 => 'Form 5 Table 7 — Probationers',
            self::F5T11 => 'Form 5 Table 11 — Dispositions',
            self::F21T8_PAROL => 'Form 21 Table 8 — Parolees',
            self::F21T8_PARDON => 'Form 21 Table 8 — Pardonees',
            default => $source,
        };
    }

    public static function defaultClientTypeCode(string $source): string
    {
        return match ($source) {
            self::F21T8_PAROL => 'PR',
            self::F21T8_PARDON => 'PD',
            default => 'PS',
        };
    }
}
