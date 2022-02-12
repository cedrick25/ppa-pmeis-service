<?php

namespace App\Enum;

use Ds\Hashable;
use MyCLabs\Enum\Enum;

/**
 * @extends Enum<string>
 * @method static self VPA()
 * @method static self PERSONNEL()
 */
class IdSupportType extends Enum implements Hashable
{
    public const VPA = "VPA";
    public const PERSONNEL = "Personnel";

    public function hash()
    {
        return $this->getValue();
    }
}