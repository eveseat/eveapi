<?php

namespace Seat\Eveapi\Mapping\Characters;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class InfoMapping.
 * @package Seat\Eveapi\Mapping\Characters
 */
class InfoMapping extends DataMapping
{
    /**
     * @var array
     */
    protected static $mapping = [
        'name'            => 'name',
        'description'     => 'description',
        'birthday'        => 'birthday',
        'gender'          => 'gender',
        'race_id'         => 'race_id',
        'bloodline_id'    => 'bloodline_id',
        'ancestry_id'     => 'ancestry_id',
        'security_status' => 'security_status',
    ];
}
