<?php

namespace App\Enum;

use Ds\Hashable;
use MyCLabs\Enum\Enum;

/**
 * @extends Enum<string>
 * @method static self LI()
 * @method static self LO()
 */
class LiLo extends Enum implements Hashable
{
    public const LI = "LI";
    public const LO = "LO";

    public function hash()
    {
        return $this->getValue();
    }
}