<?php
/*
This file is part of SeAT

Copyright (C) 2015, 2016  Leon Jacobs

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

namespace Seat\Eveapi\Api\Corporation;

use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\Corporation\AccountBalance as AccountBalanceModel;
use Seat\Eveapi\Models\Corporation\WalletTransaction;
use Seat\Eveapi\Traits\Utils;

/**
 * Class WalletTransactions
 * @package Seat\Eveapi\Api\Corporation
 */
class WalletTransactions extends Base
{

    use Utils;

    /**
     * The amount of rows to expect in a
     * API call to the EVE API
     *
     * @var int
     */
    protected $rows_per_call = 1000;

    /**
     * Run the Update
     *
     * @return mixed|void
     */
    public function call()
    {

        $pheal = $this->setScope('corp')
            ->setCorporationID()->getPheal();

        $account_ids = AccountBalanceModel::where(
            'corporationID', $this->corporationID)
            ->pluck('accountKey');

        foreach ($account_ids as $account_id) {

            // Define the first MAX from_id to use when
            // retreiving transactions.
            $from_id = PHP_INT_MAX;

            // Set the transaction overlap marker. This will
            // be checked after processing an API response to
            // see if any of the transactions we got back was
            // already known.
            $transaction_overlap = false;

            // This infinite loop needs to be broken out of
            // once we have reached the end of the backwards
            // journal walking. Walking ends when we have
            // either received less rows than asked for, or
            // we have reached a known transaction hash.
            while (true) {

                $result = $pheal->WalletTransactions([
                        'accountKey' => $account_id,
                        'rowCount'   => $this->rows_per_call,

                        // If the from_id is not PHP_INT_MAX, the we can
                        // specify it for the API call. If not, we will
                        // get an error such as:
                        //  Error: 121: Invalid fromID provided.
                    ] + ($from_id == PHP_INT_MAX ? [] : ['fromID' => $from_id])
                );

                $this->writeJobLog('transactions',
                    'API responsed with ' . count($result->transactions) . ' transactions');

                foreach ($result->transactions as $transaction) {

                    // Ensure that $from_id is at its lowest
                    $from_id = min($transaction->transactionID, $from_id);

                    // Transactions are uniquely identified by applying a
                    // quick hash function over a few identifying fields.
                    // This is because transactionID's may exhaust their
                    // lifetime and lapse.
                    $transaction_hash = $this->hash_transaction(
                        $this->corporationID,
                        $account_id,
                        $transaction->transactionDateTime . $transaction->clientID,
                        $transaction->transactionID);

                    // Check if the transaction is known. If it is,
                    // then we can just continue to the next. We will
                    // also use this opportunity to mark that the results
                    // received overlapped an existing record, meaning
                    // that we can stop calling the API for more wallet
                    // transactions. We dont immediately break because
                    // the transactions are not always received in any
                    // order for the fromID that was specified.
                    if (WalletTransaction::where('corporationID', $this->corporationID)
                        ->where('hash', $transaction_hash)
                        ->first()
                    ) {

                        $transaction_overlap = true;
                        continue;
                    }

                    WalletTransaction::create([
                        'hash'                 => $transaction_hash,
                        'corporationID'        => $this->corporationID,
                        'accountKey'           => $account_id,
                        'transactionDateTime'  => $transaction->transactionDateTime,
                        'transactionID'        => $transaction->transactionID,
                        'quantity'             => $transaction->quantity,
                        'typeName'             => $transaction->typeName,
                        'typeID'               => $transaction->typeID,
                        'price'                => $transaction->price,
                        'clientID'             => $transaction->clientID,
                        'clientName'           => $transaction->clientName,
                        'characterID'          => $transaction->characterID,
                        'characterName'        => $transaction->characterName,
                        'stationID'            => $transaction->stationID,
                        'stationName'          => $transaction->stationName,
                        'transactionType'      => $transaction->transactionType,
                        'transactionFor'       => $transaction->transactionFor,
                        'journalTransactionID' => $transaction->journalTransactionID,
                        'clientTypeID'         => $transaction->clientTypeID
                    ]);

                } // Foreach transactions

                // As previously mentioned, there may be a few
                // conditions where we may decide its time to
                // break out of the infinite loop. This is where
                // we will be doing those checks. The most ob-
                // vious one being that we may have received less
                // than the total amount of rows asked for.
                if (count($result->transactions) < $this->rows_per_call)
                    break;

                // If the response contained known transactions,
                // stop processing for this character.
                if ($transaction_overlap)
                    break;

            } // while(true)

        } // Foreach account_id

        return;
    }
}
