<?php

declare(strict_types=1);

namespace App\Enum;

use Ds\Hashable;
use MyCLabs\Enum\Enum;

/**
 * @extends Enum<string>
 * @method static self I()
 * @method static self II()
 * @method static self III()
 * @method static self IV()
 */
class Phases extends Enum implements Hashable
{
    public const I = "I";
    public const II = "II";
    public const III = "III";
    public const IV = "IV";

    public function hash()
    {
        return $this->getValue();
    }
}