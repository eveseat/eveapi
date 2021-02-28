<?php

namespace Seat\Eveapi\Mapping\Financial;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class WalletTransactionMapping.
 * @package Seat\Eveapi\Mapping\Financial
 */
class WalletTransactionMapping extends DataMapping
{
    /**
     * @var string[]
     */
    protected static $mapping = [
        'transaction_id' => 'transaction_id',
        'date'           => 'date',
        'type_id'        => 'type_id',
        'location_id'    => 'location_id',
        'unit_price'     => 'unit_price',
        'quantity'       => 'quantity',
        'client_id'      => 'client_id',
        'is_buy'         => 'is_buy',
        'journal_ref_id' => 'journal_ref_id',
    ];
}
