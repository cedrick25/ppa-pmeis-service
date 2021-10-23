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
    public const VALIDATING_QUARTER_FAILED = "Validating quarter failed.";
    public const CREATING_QUARTER_FAILED = "Creating quarter failed.";
    public const CREATING_QUARTER_SUCCESS = "Creating quarter successful.";
    public const FETCHING_QUARTER_FAILED = "Fetching quarter failed.";
    public const FETCHING_QUARTER_SUCCESS = "Fetching quarter success.";
    public const NO_QUARTER_DATA = "No quarter found.";
    public const DELETING_QUARTER_FAILED = "Deleting quarter failed.";
    public const DELETING_QUARTER_SUCCESS = "Deleting quarter success.";

    public const NO_PHASES_DATA = "No phases found.";
    public const FETCHING_PHASES_SUCCESS = "Fetching phases success.";
    public const FETCHING_PHASES_FAILED = "Fetching phases failed.";

    public const NO_FIELD_OFFICE_DATA = "No field office found.";
    public const FETCHING_FIELD_OFFICE_SUCCESS = "Fetching field office success.";
    public const FETCHING_FIELD_OFFICE_FAILED = "Fetching field office failed.";

    public const NO_SESSION_ACTIVITIES_DATA = "No session activity found.";
    public const FETCHING_SESSION_ACTIVITIES_SUCCESS = "Fetching session activity success.";
    public const FETCHING_SESSION_ACTIVITIES_FAILED = "Fetching session activity failed.";
    public const VALIDATING_SESSION_ACTIVITIES_FAILED = "Validating session activity failed.";
    public const CREATING_SESSION_ACTIVITIES_FAILED = "Creating session activity failed.";
    public const CREATING_SESSION_ACTIVITIES_SUCCESS = "Creating session activity successful.";

    public function hash()
    {
        return $this->getValue();
    }
}