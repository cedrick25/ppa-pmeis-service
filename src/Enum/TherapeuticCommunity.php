<?php

declare(strict_types=1);

namespace App\Enum;

use Ds\Hashable;
use MyCLabs\Enum\Enum;

/**
 * @extends Enum<string>
 * @method static self VALIDATING_QUARTER_FAILED()
 * @method static self CREATING_QUARTER_FAILED()
 * @method static self CREATING_QUARTER_SUCCESS()
 * @method static self FETCHING_QUARTER_FAILED()
 * @method static self FETCHING_QUARTER_SUCCESS()
 * @method static self NO_QUARTER_DATA()
 * @method static self DELETING_QUARTER_FAILED()
 * @method static self DELETING_QUARTER_SUCCESS()
 */
class TherapeuticCommunity extends Enum implements Hashable
{
    public const VALIDATING_QUARTER_FAILED = "Validation quarter failed.";
    public const CREATING_QUARTER_FAILED = "Creating quarter failed.";
    public const CREATING_QUARTER_SUCCESS = "Creating quarter successful.";
    public const FETCHING_QUARTER_FAILED = "Fetching quarter failed.";
    public const FETCHING_QUARTER_SUCCESS = "Fetching quarter success.";
    public const NO_QUARTER_DATA = "No quarter found.";
    public const DELETING_QUARTER_FAILED = "Deleting quarter failed.";
    public const DELETING_QUARTER_SUCCESS = "Deleting quarter success.";

    public function hash()
    {
        return $this->getValue();
    }
}