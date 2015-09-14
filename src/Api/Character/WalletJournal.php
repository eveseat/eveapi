<?php
/*
This file is part of SeAT

Copyright (C) 2015  Leon Jacobs

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

namespace Seat\Eveapi\Api\Character;

use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\CharacterWalletJournal;
use Seat\Eveapi\Models\EveApiKey;
use Seat\Eveapi\Traits\Utils;

/**
 * Class WalletJournals
 * @package Seat\Eveapi\Api\Character
 */
class WalletJournal extends Base
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
                    ->WalletJournal(
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
                    $from_id = min($transaction->refID, $from_id);

                    // Transactions are uniquely identified by applying a
                    // quick hash function over a few identifying fields.
                    // This is because transactionID's may exhaust their
                    // lifetime and lapse.
                    $transaction_hash = $this->hash_transaction(
                        $character->characterID,
                        $transaction->date,
                        $transaction->ownerID1,
                        $transaction->refID);

                    // Check if the transaction is known. If it is,
                    // then we can just continue to the next. We will
                    // also use this opportunity to mark that the results
                    // received overlapped an existing record, meaning
                    // that we can stop calling the API for more wallet
                    // transactions. We dont immediately break because
                    // the transactions are not always received in any
                    // order for the fromID that was specified.
                    if (CharacterWalletJournal::where('characterID', $character->characterID)
                        ->where('hash', $transaction_hash)
                        ->first()
                    ) {

                        $transaction_overlap = true;
                        continue;
                    }

                    // Create the transaction for this character
                    CharacterWalletJournal::create([
                        'characterID'   => $character->characterID,
                        'hash'          => $transaction_hash,
                        'refID'         => $transaction->refID,
                        'date'          => $transaction->date,
                        'refTypeID'     => $transaction->refTypeID,
                        'ownerName1'    => $transaction->ownerName1,
                        'ownerID1'      => $transaction->ownerID1,
                        'ownerName2'    => $transaction->ownerName2,
                        'ownerID2'      => $transaction->ownerID2,
                        'argName1'      => $transaction->argName1,
                        'argID1'        => $transaction->argID1,
                        'amount'        => $transaction->amount,
                        'balance'       => $transaction->balance,
                        'reason'        => $transaction->reason,
                        'taxReceiverID' => $transaction->taxReceiverID,
                        'taxAmount'     => $transaction->taxAmount,
                        'owner1TypeID'  => $transaction->owner1TypeID,
                        'owner2TypeID'  => $transaction->owner2TypeID
                    ]);

                } // Foreach transactions

                // As previously mentioned, there may be a few
                // conditions where we may decide its time to
                // break out of the infinite loop. This is where
                // we will be doing those checks. The most ob-
                // vious one being that we may have received less
                // than the total amount of rows asked for.
                if (count($result->kills) < $this->rows_per_call)
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
