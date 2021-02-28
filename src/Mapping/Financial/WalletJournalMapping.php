<?php

namespace Seat\Eveapi\Mapping\Financial;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class WalletJournalMapping.
 * @package Seat\Eveapi\Mapping\Financial
 */
class WalletJournalMapping extends DataMapping
{
    /**
     * @var array
     */
    protected static $mapping = [
        'id'              => 'id',                         // changed from ref_id to id into v4
        'date'            => 'date',
        'ref_type'        => 'ref_type',
        'first_party_id'  => 'first_party_id',
        'second_party_id' => 'second_party_id',
        'amount'          => 'amount',
        'balance'         => 'balance',
        'reason'          => 'reason',
        'tax_receiver_id' => 'tax_receiver_id',
        'tax'             => 'tax',
        // appears in version 4
        'description'     => 'description',
        'context_id'      => 'context_id',
        'context_id_type' => 'context_id_type',
    ];
}
