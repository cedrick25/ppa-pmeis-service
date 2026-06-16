<?php

namespace App\Enum;

use MyCLabs\Enum\Enum;

/**
 * @extends Enum<string>
 * @method static self APPLICANT()
 * @method static self COMPLETED()
 * @method static self ENDORSED()
 * @method static self APPOINTED()
 * @method static self DROPPED()
 * @method static self EXPIRING()
 */
class VolunteerStatus extends Enum
{
    public const APPLICANT = 'APPLICANT';
    public const COMPLETED = 'COMPLETED';
    public const ENDORSED = 'ENDORSED';
    public const APPOINTED = 'APPOINTED';
    public const DROPPED = 'DROPPED';
    public const EXPIRING = 'EXPIRING';
}
