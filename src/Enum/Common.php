<?php

declare(strict_types=1);

namespace App\Enum;

use Ds\Hashable;
use MyCLabs\Enum\Enum;

/**
 * @extends Enum<mixed>
 * @method static self VPA_NUMBER_OF_MONTHS_APPOINTMENT()
 * @method static self NUMBER_OF_MONTHS_BEFORE_VPA_EXPIRATION()
 */
class Common extends Enum implements Hashable
{
    public const VPA_NUMBER_OF_MONTHS_APPOINTMENT = 24;

    // Number of months since date appointed
    // Example: if expiring in 6 months for 2 years appointment, that would be 24-6 = 18
    public const NUMBER_OF_MONTHS_BEFORE_VPA_EXPIRATION = 6;

    public function hash()
    {
        return $this->getValue();
    }
}