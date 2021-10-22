<?php

declare(strict_types=1);

namespace App\Enum;

use Ds\Hashable;
use MyCLabs\Enum\Enum;

/**
 * @extends Enum<string>
 * @method static self FO()
 * @method static self RD()
 * @method static self ND()
 */
class UserType extends Enum implements Hashable
{
    public const FO = "FO";
    public const RD = "RD";
    public const ND = "ND";

    public function hash()
    {
        return $this->getValue();
    }
}