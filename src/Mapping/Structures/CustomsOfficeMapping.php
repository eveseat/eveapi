<?php

namespace Seat\Eveapi\Mapping\Structures;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class CustomsOfficeMapping.
 * @package Seat\Eveapi\Mapping\Structures
 */
class CustomsOfficeMapping extends DataMapping
{
    /**
     * @var string[]
     */
    protected static $mapping = [
        'office_id'                   => 'office_id',
        'system_id'                   => 'system_id',
        'reinforce_exit_start'        => 'reinforce_exit_start',
        'reinforce_exit_end'          => 'reinforce_exit_end',
        'corporation_tax_rate'        => 'corporation_tax_rate',
        'allow_alliance_access'       => 'allow_alliance_access',
        'alliance_tax_rate'           => 'alliance_tax_rate',
        'allow_access_with_standings' => 'allow_access_with_standings',
        'standing_level'              => 'standing_level',
        'excellent_standing_tax_rate' => 'excellent_standing_tax_rate',
        'good_standing_tax_rate'      => 'good_standing_tax_rate',
        'neutral_standing_tax_rate'   => 'neutral_standing_tax_rate',
        'bad_standing_tax_rate'       => 'bad_standing_tax_rate',
        'terrible_standing_tax_rate'  => 'terrible_standing_tax_rate',
    ];
}
