<?php

declare(strict_types=1);

namespace App\Enum;

use Ds\Hashable;
use MyCLabs\Enum\Enum;

/**
 * @extends Enum<string>
 */
class Response extends Enum implements Hashable
{
    public const OK = "OK";
    public const NO_RECORD = "No record found.";
    public const CONFLICTED_INPUT = "Input data conflicted with current record.";

    public function hash(): string
    {
        return $this->getValue();
    }
}