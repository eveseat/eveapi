<?php

namespace Seat\Eveapi\Mapping\Characters;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class MedalsMapping.
 * @package Seat\Eveapi\Mapping\Characters
 */
class MedalsMapping extends DataMapping
{
    /**
     * @var array
     */
    protected static $mapping = [
        'medal_id'       => 'medal_id',
        'title'          => 'title',
        'description'    => 'description',
        'corporation_id' => 'corporation_id',
        'issuer_id'      => 'issuer_id',
        'date'           => 'date',
        'reason'         => 'reason',
        'status'         => 'status',
    ];
}
