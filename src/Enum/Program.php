<?php

namespace App\Enum;

use Ds\Hashable;
use MyCLabs\Enum\Enum;

/**
 * @extends Enum<string>
 * @method static self TC()
 * @method static self RJ()
 * @method static self VPA()
 * @method static self GAD()
 * @method static self PWDSC()
 * @method static self OTHERS()
 */
class Program extends Enum implements Hashable
{
    public const TC = "TC";
    public const RJ = "RJ";
    public const VPA = "VPA";
    public const GAD = "GAD";
    public const PWDSC = "PWDSC";
    public const OTHERS = "OTHERS";

    public function hash()
    {
        return $this->getValue();
    }
}