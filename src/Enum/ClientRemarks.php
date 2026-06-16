<?php

namespace App\Enum;

use MyCLabs\Enum\Enum;

/**
 * @extends Enum<string>
 * @method static self W_FR_VR()
 * @method static self ON_CS()
 * @method static self TERMINATED()
 * @method static self REVOKED()
 * @method static self TRANSFERRED()
 * @method static self ABSCONDED_W_NO_REPORT_SUBMITTED_TO_COURT()
 * @method static self DIED_W_NO_REPORT_SUBMITTED_TO_COURT()
 * @method static self IN_JAIL_WITH_NO_REPORT_SUBMITTED_TO_COURT()
 * @method static self WITH_SERIOUS_AILMENT()
 * @method static self ON_TRAVEL_ABROAD_WITH_PERMIT()
 * @method static self CASE_S_PENDING_IN_COURT()
 * @method static self OTHERS()
 * @method static self ON_CS_TO_OTHER_FIELD_OFFICES()
 */
class ClientRemarks extends Enum
{
    public const W_FR_VR = 'w/ FR/VR';
    public const ON_CS = 'on CS';
    public const TERMINATED = 'Terminated';
    public const REVOKED = 'Revoked';
    public const TRANSFERRED = 'Transferred';
    public const ABSCONDED_W_NO_REPORT_SUBMITTED_TO_COURT = 'Absconded  w/ no report submitted to Court';
    public const DIED_W_NO_REPORT_SUBMITTED_TO_COURT = 'Died w/ no report submitted to Court';
    public const IN_JAIL_WITH_NO_REPORT_SUBMITTED_TO_COURT = 'In Jail with no report submitted to Court';
    public const WITH_SERIOUS_AILMENT = 'With Serious Ailment';
    public const ON_TRAVEL_ABROAD_WITH_PERMIT = 'On Travel Abroad (with permit)';
    public const CASE_S_PENDING_IN_COURT = 'Case/ s pending in Court';
    public const OTHERS = 'Others';
    public const ON_CS_TO_OTHER_FIELD_OFFICES = 'On CS to other Field Offices';
}