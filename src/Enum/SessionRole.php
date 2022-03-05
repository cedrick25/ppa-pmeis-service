<?php

declare(strict_types=1);

namespace App\Enum;

use Ds\Hashable;
use MyCLabs\Enum\Enum;

/**
 * @extends Enum<string>
 * @method static self RESOURCE_PERSON()
 * @method static self FACILITATOR()
 */
class SessionRole extends Enum implements Hashable
{
    public const RESOURCE_PERSON = 'Resource Person';
    public const FACILITATOR = 'Facilitator';

    public function hash()
    {
        return $this->getValue();
    }
}