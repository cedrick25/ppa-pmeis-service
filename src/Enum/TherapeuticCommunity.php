<?php

declare(strict_types=1);

namespace App\Enum;

use Ds\Hashable;
use MyCLabs\Enum\Enum;

/**
 * @extends Enum<string>
 */
class TherapeuticCommunity extends Enum implements Hashable
{
    public const VALIDATING_FAILED = "Validating inputs failed.";
    public const NO_DATA = "No data found.";
    public const FETCHING_SUCCESS = "Fetching data success.";
    public const FETCHING_FAILED = "Fetching data failed.";
    public const CREATING_FAILED = "Creating new record failed.";
    public const CREATING_SUCCESS = "Creating new record successful.";
    public const DELETING_FAILED = "Deleting record failed.";
    public const DELETING_SUCCESS = "Deleting record success.";
    public const UPDATING_FAILED = "Updating record failed.";
    public const UPDATING_SUCCESS = "Updating record success.";

    public function hash()
    {
        return $this->getValue();
    }
}