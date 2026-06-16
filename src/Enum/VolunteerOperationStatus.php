<?php

namespace App\Enum;

use Ds\Hashable;
use MyCLabs\Enum\Enum;

/**
 * @extends Enum<string>
 * @method static self APPOINTED()
 * @method static self REAPPOINTED()
 * @method static self DROPPED()
 */

class VolunteerOperationStatus extends Enum implements Hashable
{
    public const APPOINTED = "APPOINTED";
    public const REAPPOINTED = "REAPPOINTED";
    public const DROPPED = "DROPPED";

    public function hash()
    {
        return $this->getValue();
    }

}