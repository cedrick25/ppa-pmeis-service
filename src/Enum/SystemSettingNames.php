<?php

namespace App\Enum;

use MyCLabs\Enum\Enum;

/**
 * @extends Enum<string>
 * @method static self OIC_ADMINISTRATOR()
 * @method static self VPA_CERTIFICATE_REPORT_CODE()
 * @method static self GENERATED_REPORTS_CODE()
 */
class SystemSettingNames extends Enum
{
    public const OIC_ADMINISTRATOR = 'OIC Administrator';
    public const VPA_CERTIFICATE_REPORT_CODE = 'VPA Certificate Report Code';
    public const GENERATED_REPORTS_CODE = 'Generated Reports Code';

}