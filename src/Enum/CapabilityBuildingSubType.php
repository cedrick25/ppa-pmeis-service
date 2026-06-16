<?php

namespace App\Enum;

use Ds\Hashable;
use MyCLabs\Enum\Enum;

/**
 * @extends Enum<string>
 * @method static self TTC()
 * @method static self TRJ()
 * @method static self TV()
 * @method static self TG()
 * @method static self OTHER()
 * @method static self CC()
 * @method static self SCMPD()
 * @method static self VMA()
 */
class CapabilityBuildingSubType extends Enum implements Hashable
{
    public const TTC = 'Training on Therapeutic Community';
    public const TRJ = 'Training on Restorative Justice';
    public const TV = 'Training on Volunteerism';
    public const TG = 'Training on GAD';
    public const OTHER = 'Other Training Courses or Seminars or Fora or Symposia';
    public const CC = 'Conferences or Conventions';
    public const SCMPD = "Staff or Committee Meetings with Professional Dev't";
    public const VMA = 'VPA Meetings or Assemblies';

    public function hash()
    {
        return $this->getValue();
    }
}