<?php

namespace App\Enum;

use Ds\Hashable;
use MyCLabs\Enum\Enum;

/**
 * @extends Enum<string>
 * @method static self GO()
 * @method static self NGO()
 * @method static self IND()
 */
class SourceType extends Enum implements Hashable
{

    public const GO = "GO";
    public const NGO = "NGO";
    public const IND = "IND";

    public function hash()
    {
        return $this->getValue();
    }
}