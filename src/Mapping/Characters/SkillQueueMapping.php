<?php

namespace Seat\Eveapi\Mapping\Characters;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class SkillQueueMapping.
 * @package Seat\Eveapi\Mapping\Characters
 */
class SkillQueueMapping extends DataMapping
{
    /**
     * @var string[]
     */
    protected static $mapping = [
        'skill_id'          => 'skill_id',
        'queue_position'    => 'queue_position',
        'finish_date'       => 'finish_date',
        'start_date'        => 'start_date',
        'finished_level'    => 'finished_level',
        'training_start_sp' => 'training_start_sp',
        'level_end_sp'      => 'level_end_sp',
        'level_start_sp'    => 'level_start_sp',
    ];
}
