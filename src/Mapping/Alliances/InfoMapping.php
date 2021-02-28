<?php

namespace Seat\Eveapi\Mapping\Alliances;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class InfoMapping.
 * @package Seat\Eveapi\Mapping\Alliances
 */
class InfoMapping extends DataMapping
{
    /**
     * @var string[]
     */
    protected static $mapping = [
        'name' => 'name',
        'creator_id' => 'creator_id',
        'creator_corporation_id' => 'creator_corporation_id',
        'ticker' => 'ticker',
        'executor_corporation_id' => 'executor_corporation_id',
        'date_founded' => 'date_founded',
        'faction_id' => 'faction_id',
    ];
}
