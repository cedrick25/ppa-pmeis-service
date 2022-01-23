<?php

namespace App\Enum;

use Ds\Hashable;
use MyCLabs\Enum\Enum;

/**
 * @extends Enum<string>
 * @method static self ACTIVE_SUPERVISION()
 * @method static self PETITIONER()
 */
class RJGroup extends Enum implements Hashable
{
    public const ACTIVE_SUPERVISION = "ACTIVE_SUPERVISION.";
    public const PETITIONER = "PETITIONER.";

    public function hash(): string
    {
        return $this->getValue();
    }
}