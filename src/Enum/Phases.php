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
 * @method static self IV_ONGOING()
 * @method static self IV_COMPLETED()
 */
class Phases extends Enum implements Hashable
{
    public const PREP = "Prep";
    public const I = "I";
    public const II = "II";
    public const III = "III";
    public const IV_ONGOING = "IV-Ongoing";
    public const IV_COMPLETED = "IV-Completed";

    public function hash()
    {
        return $this->getValue();
    }
}