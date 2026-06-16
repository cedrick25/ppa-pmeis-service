<?php

namespace App\Enum;

use MyCLabs\Enum\Enum;

/**
 * @extends Enum<string>
 * @method static self ARMED_FORCES_OCCUPATION()
 * @method static self MANAGERS()
 * @method static self PROFESSIONALS()
 * @method static self TECHNICAL_ASSOCIATE_PROFESSIONALS()
 * @method static self CLERICAL_SUPPORT_WORKERS()
 * @method static self SERVICE_AND_SALES_WORKERS()
 * @method static self SKILLED_AGRICULTURAL_FORESTRY_AND_FISHERY_WORKERS()
 * @method static self CRAFT_AND_RELATED_TRADES_WORKERS()
 * @method static self PLANT_AND_MACHINES_OPERATORS_AND_ASSEMBLERS()
 * @method static self ELEMENTARY_OCCUPATION()
 * @method static self UNEMPLOYED()
 */
class OccupationType extends Enum
{
    public const ARMED_FORCES_OCCUPATION = 'Armed Forces Occupation';
    public const MANAGERS = 'Managers';
    public const PROFESSIONALS = 'Professionals';
    public const TECHNICAL_ASSOCIATE_PROFESSIONALS = 'Technical Associate Professionals';
    public const CLERICAL_SUPPORT_WORKERS = 'Clerical Support Workers';
    public const SERVICE_AND_SALES_WORKERS = 'Service and Sales Workers';
    public const SKILLED_AGRICULTURAL_FORESTRY_AND_FISHERY_WORKERS
        = 'Skilled Agricultural, Forestry and Fishery Workers';
    public const CRAFT_AND_RELATED_TRADES_WORKERS = 'Craft and Related Trades Workers';
    public const PLANT_AND_MACHINES_OPERATORS_AND_ASSEMBLERS = 'Plant and Machines Operators and Assemblers';
    public const ELEMENTARY_OCCUPATION = 'Elementary Occupation';
    public const UNEMPLOYED = 'Unemployed';
}
