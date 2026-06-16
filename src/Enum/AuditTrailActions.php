<?php

namespace App\Enum;

use Ds\Hashable;
use MyCLabs\Enum\Enum;

/**
 * @extends Enum<string>
 * @method static self CREATE()
 * @method static self UPDATE()
 * @method static self DELETE()
 * @method static self DOWNLOAD()
 */
class AuditTrailActions extends Enum implements Hashable
{
    public const CREATE = 'Create';
    public const UPDATE = 'Update';
    public const DELETE = 'Delete';
    public const DOWNLOAD = 'Download';
    public const GENERATE_REPORT = 'Generate Report';

    public function hash()
    {
        return $this->getValue();
    }
}