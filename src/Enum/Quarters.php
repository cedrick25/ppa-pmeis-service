<?php

declare(strict_types=1);

namespace App\Enum;

use Ds\Hashable;
use MyCLabs\Enum\Enum;

/**
 * @extends Enum<string>
 * @method static self FIRST()
 * @method static self SECOND()
 * @method static self THIRD()
 * @method static self FOURTH()
 */
class Quarters extends Enum implements Hashable
{
    public const FIRST = "FIRST";
    public const SECOND = "SECOND";
    public const THIRD = "THIRD";
    public const FOURTH = "FOURTH";

    public function hash()
    {
        return $this->getValue();
    }
}