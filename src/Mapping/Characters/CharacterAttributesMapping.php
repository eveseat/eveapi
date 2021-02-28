<?php

namespace Seat\Eveapi\Mapping\Characters;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class CharacterAttributesMapping.
 * @package Seat\Eveapi\Mapping\Characters
 */
class CharacterAttributesMapping extends DataMapping
{
    /**
     * @var array
     */
    protected static $mapping = [
        'charisma'                    => 'charisma',
        'intelligence'                => 'intelligence',
        'memory'                      => 'memory',
        'perception'                  => 'perception',
        'willpower'                   => 'willpower',
        'bonus_remaps'                => 'bonus_remaps',
        'last_remap_date'             => 'last_remap_date',
        'accrued_remap_cooldown_date' => 'accrued_remap_cooldown_date',
    ];
}
