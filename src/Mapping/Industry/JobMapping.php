<?php

namespace Seat\Eveapi\Mapping\Industry;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class JobMapping.
 * @package Seat\Eveapi\Mapping\Industry
 */
class JobMapping extends DataMapping
{
    /**
     * @var array
     */
    protected static $mapping = [
        'job_id'                 => 'job_id',
        'installer_id'           => 'installer_id',
        'facility_id'            => 'facility_id',
        'activity_id'            => 'activity_id',
        'blueprint_id'           => 'blueprint_id',
        'blueprint_type_id'      => 'blueprint_type_id',
        'blueprint_location_id'  => 'blueprint_location_id',
        'output_location_id'     => 'output_location_id',
        'runs'                   => 'runs',
        'cost'                   => 'cost',
        'licensed_runs'          => 'licensed_runs',
        'probability'            => 'probability',
        'product_type_id'        => 'product_type_id',
        'status'                 => 'status',
        'duration'               => 'duration',
        'start_date'             => 'start_date',
        'end_date'               => 'end_date',
        'pause_date'             => 'pause_date',
        'completed_date'         => 'completed_date',
        'completed_character_id' => 'completed_character_id',
        'successful_runs'        => 'successful_runs',
    ];
}
