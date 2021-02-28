<?php

namespace Seat\Eveapi\Mapping\Industry;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class AgentResearchMapping.
 * @package Seat\Eveapi\Mapping\Industry
 */
class AgentResearchMapping extends DataMapping
{
    /**
     * @var array
     */
    protected static $mapping = [
        'agent_id'         => 'agent_id',
        'skill_type_id'    => 'skill_type_id',
        'started_at'       => 'started_at',
        'points_per_day'   => 'points_per_day',
        'remainder_points' => 'remainder_points',
    ];
}
