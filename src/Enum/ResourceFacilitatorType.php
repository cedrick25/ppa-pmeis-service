<?php

namespace App\Enum;

use Ds\Hashable;
use MyCLabs\Enum\Enum;

/**
 * @extends Enum<string>
 * @method static self PPO()
 * @method static self VPA()
 * @method static self ERP()
 */
class ResourceFacilitatorType extends Enum implements Hashable
{
    public const PPO = "PPO";
    public const VPA = "VPA";
    public const ERP = "ERP";

    public function hash()
    {
        return $this->getValue();
    }

}