<?php

declare(strict_types=1);

namespace App\Enum;

use Ds\Hashable;
use MyCLabs\Enum\Enum;

/**
 * @extends Enum<string>
 * @method static self USER_CREATE_SUCCESS()
 * @method static self USER_CREATE_FAILED()
 * @method static self USER_VALIDATION_FAILED()
 */
class Response extends Enum implements Hashable
{
    public const USER_CREATE_SUCCESS = "User creation successful.";
    public const USER_CREATE_FAILED = "User creation failed.";
    public const USER_VALIDATION_FAILED = "User validation failed.";

    public function hash(): string
    {
        return $this->getValue();
    }
}