<?php
/*
The MIT License (MIT)

Copyright (c) 2015 Leon Jacobs
Copyright (c) 2015 eveseat

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

namespace Seat\Eveapi\Api\Character;

use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\CharacterWalletTransaction;
use Seat\Eveapi\Models\EveApiKey;
use Seat\Eveapi\Traits\Utils;

/**
 * Class WalletTransactions
 * @package Seat\Eveapi\Api\Character
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
     * @param \Seat\Eveapi\Models\EveApiKey $api_info
     */
    public function call(EveApiKey $api_info)
    {

        // Ofc, we need to process the update of all
        // of the characters on this key.
        foreach ($api_info->characters as $character) {

            // Define the first MAX from_id to use when
            // retreiving transactions.
            $from_id = PHP_INT_MAX;

            // Set the transaction overlap marker. This will
            // be checked after processing an API response to
            // see if any of the transactions we got back was
            // already known.
            $transaction_overlap = false;

            // Setup the Pheal Instance to use
            $pheal = $this->setKey(
                $api_info->key_id, $api_info->v_code)
                ->getPheal();

            // This infinite loop needs to be broken out of
            // once we have reached the end of the backwards
            // journal walking. Walking ends when we have
            // either received less rows than asked for, or
            // we have reached a known transaction hash.
            while (true) {

                $result = $pheal
                    ->charScope
                    ->WalletTransactions(
                        [
                            'characterID' => $character->characterID,
                            'rowCount'    => $this->rows_per_call
                            // If the from_id is not PHP_INT_MAX, the we can
                            // specify it for the API call. If not, we will
                            // get an error such as:
                            //  Error: 121: Invalid fromID provided.
                        ] + ($from_id == PHP_INT_MAX ? [] : ['fromID' => $from_id])
                    );

                // Loop over the response transactions, checking the
                //existance and updating as required
                foreach ($result->transactions as $transaction) {

                    // Ensure that $from_id is at its lowest
                    $from_id = min($transaction->transactionID, $from_id);

                    // Transactions are uniquely identified by applying a
                    // quick hash function over a few identifying fields.
                    // This is because transactionID's may exhaust their
                    // lifetime and lapse.
                    $transaction_hash = $this->hash_transaction(
                        $character->characterID,
                        $transaction->transactionDateTime,
                        $transaction->clientID,
                        $transaction->transactionID);

                    // Check if the transaction is known. If it is,
                    // then we can just continue to the next. We will
                    // also use this opportunity to mark that the results
                    // received overlapped an existing record, meaning
                    // that we can stop calling the API for more wallet
                    // transactions. We dont immediately break because
                    // the transactions are not always received in any
                    // order for the fromID that was specified.
                    if (CharacterWalletTransaction::where('characterID', $character->characterID)
                        ->where('hash', $transaction_hash)
                        ->first()
                    ) {

                        $transaction_overlap = true;
                        continue;
                    }

                    // Create the transaction for this character
                    CharacterWalletTransaction::create([
                        'characterID'          => $character->characterID,
                        'hash'                 => $transaction_hash,
                        'transactionID'        => $transaction->transactionID,
                        'transactionDateTime'  => $transaction->transactionDateTime,
                        'quantity'             => $transaction->quantity,
                        'typeName'             => $transaction->typeName,
                        'typeID'               => $transaction->typeID,
                        'price'                => $transaction->price,
                        'clientID'             => $transaction->clientID,
                        'clientName'           => $transaction->clientName,
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

        }

        return;
    }
}
