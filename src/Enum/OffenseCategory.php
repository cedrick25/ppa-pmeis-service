<?php

declare(strict_types=1);

namespace App\Enum;

use Ds\Hashable;
use MyCLabs\Enum\Enum;

/**
 * @extends Enum<string>
 * @method static self DO()
 * @method static self NDO()
 */
class OffenseCategory extends Enum implements Hashable
{
    public const DO = "DO";
    public const NDO = "NDO";

    public function hash()
    {
        return $this->getValue();
    }

}