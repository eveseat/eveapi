<?php

namespace Seat\Eveapi\Mapping\Financial;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class OrderMapping.
 * @package Seat\Eveapi\Mapping\Financial
 */
class OrderMapping extends DataMapping
{
    /**
     * @var array
     */
    protected static $mapping = [
        'order_id'        => 'order_id',
        'type_id'         => 'type_id',
        'region_id'       => 'region_id',
        'location_id'     => 'location_id',
        'range'           => 'range',
        'is_buy_order'    => 'is_buy_order',
        'price'           => 'price',
        'volume_total'    => 'volume_total',
        'volume_remain'   => 'volume_remain',
        'issued'          => 'issued',
        'min_volume'      => 'min_volume',
        'duration'        => 'duration',
        'escrow'          => 'escrow',
    ];
}
