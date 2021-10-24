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
    public const VALIDATING_FAILED = "Validating failed.";
    public const NO_DATA = "No data found.";
    public const FETCHING_SUCCESS = "Fetching success.";
    public const FETCHING_FAILED = "Fetching failed.";
    public const CREATING_FAILED = "Creating failed.";
    public const CREATING_SUCCESS = "Creating successful.";
    public const DELETING_FAILED = "Deleting failed.";
    public const DELETING_SUCCESS = "Deleting success.";
    public const UPDATING_FAILED = "Updating failed.";
    public const UPDATING_SUCCESS = "Updating success.";

    public function hash()
    {
        return $this->getValue();
    }
}