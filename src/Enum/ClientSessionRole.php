<?php

declare(strict_types=1);

namespace App\Enum;

use Ds\Hashable;
use MyCLabs\Enum\Enum;

/**
 * @extends Enum<string>
 * @method static self PS()
 * @method static self PR()
 * @method static self PD()
 * @method static self JICL()
 * @method static self FTMDO()
 * @method static self PET()
 * @method static self TERM()
 */
class ClientSessionRole extends Enum implements Hashable
{
    public const PS = "PS";
    public const PR = "PR";
    public const PD = "PD";
    public const JICL = "JICL";
    public const FTMDO = "FTMDO";
    public const PET = "PET";
    public const TERM = "TERM";

    public function hash()
    {
        return $this->getValue();
    }
}