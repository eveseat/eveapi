<?php

namespace Seat\Eveapi\Mapping\Industry;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class ExtractionMapping.
 * @package Seat\Eveapi\Mapping\Industry
 */
class ExtractionMapping extends DataMapping
{
    /**
     * @var array
     */
    protected static $mapping = [
        'moon_id'               => 'moon_id',
        'structure_id'          => 'structure_id',
        'extraction_start_time' => 'extraction_start_time',
        'chunk_arrival_time'    => 'chunk_arrival_time',
        'natural_decay_time'    => 'natural_decay_time',
    ];
}
