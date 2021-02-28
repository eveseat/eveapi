<?php

namespace Seat\Eveapi\Mapping\Characters;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class SkillMapping.
 * @package Seat\Eveapi\Mapping\Characters
 */
class SkillMapping extends DataMapping
{
    /**
     * @var array
     */
    protected static $mapping = [
        'skill_id'             => 'skill_id',
        'skillpoints_in_skill' => 'skillpoints_in_skill',
        'trained_skill_level'  => 'trained_skill_level',
        'active_skill_level'   => 'active_skill_level',
    ];
}
