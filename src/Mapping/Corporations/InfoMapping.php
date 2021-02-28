<?php

namespace Seat\Eveapi\Mapping\Corporations;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class InfoMapping.
 * @package Seat\Eveapi\Mapping\Corporations
 */
class InfoMapping extends DataMapping
{
    /**
     * @var array
     */
    protected static $mapping = [
        'name'            => 'name',
        'ticker'          => 'ticker',
        'member_count'    => 'member_count',
        'ceo_id'          => 'ceo_id',
        'alliance_id'     => 'alliance_id',
        'description'     => 'description',
        'tax_rate'        => 'tax_rate',
        'date_founded'    => 'date_founded',
        'creator_id'      => 'creator_id',
        'url'             => 'url',
        'faction_id'      => 'faction_id',
        'home_station_id' => 'home_station_id',
        'shares'          => 'shares',
    ];
}
