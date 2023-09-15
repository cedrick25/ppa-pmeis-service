<?php

declare(strict_types=1);

namespace App\Enum;

use Ds\Hashable;
use MyCLabs\Enum\Enum;

/**
 * @extends Enum<string>
 * @method static self FO()
 * @method static self CSD()
 * @method static self RD()
 * @method static self ND()
 * @method static self CSU()
 * @method static self PLANNING()
 * @method static self CPPO()
 * @method static self CMRD()
 * @method static self CLERK()
 * @method static self ARD()
 * @method static self SA()
 * @method static self CMRU()
 */
class UserType extends Enum implements Hashable
{
    public const FO = "FIELD_OFFICER";
    public const CSD = "CSD";
    public const RD = "REGIONAL_DIRECTOR";
    public const ND = "NATIONAL_DIRECTOR";
    public const CSU = "CSU";
    public const PLANNING = "PLANNING";
    public const CPPO = "CPPO";
    public const CMRD = "CMRD";
    public const CLERK = "CLERK";
    public const ARD = "ASSISTANT_REGIONAL_DIRECTOR";
    public const SA = "SPECIAL_ASSISTANT";
    public const CMRU = "CMRU";

    public function hash()
    {
        return $this->getValue();
    }
}