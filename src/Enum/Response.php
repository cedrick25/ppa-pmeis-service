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
    public function hash(): string
    {
        return $this->getValue();
    }
}