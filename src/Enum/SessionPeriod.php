<?php

declare(strict_types=1);

namespace App\Enum;

use Ds\Hashable;
use MyCLabs\Enum\Enum;

/**
 * @extends Enum<string>
 * @method static self AM()
 * @method static self PM()
 */
class SessionPeriod extends Enum implements Hashable
{
    public const AM = "AM";
    public const PM = "PM";

    public function hash()
    {
        return $this->getValue();
    }
}