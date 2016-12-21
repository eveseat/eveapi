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

namespace Seat\Eveapi\Api\Character;

use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\Eve\CharacterAffiliation;

class Affiliation extends Base
{

    /**
     * @var string
     */
    protected $cache_prefix = 'intel_';

    /**
     * How many minutes a characters affiliation
     * should be considered up to date.
     *
     * @var int
     */
    protected $cache_lifetime_minutes = 720;

    /**
     * How many ids do we give the API at a time
     * to update.
     *
     * @var int
     */
    protected $ids_to_process = 50;

    /**
     * Source tables and columns of characterIDs
     * that will be updated.
     *
     * @var array
     */
    protected $tables_and_columns = [
        'character_wallet_journals'     => ['ownerID1', 'ownerID2'],
        'character_wallet_transactions' => ['clientID'],
        'character_contact_lists'       => ['contactID'],
        'character_contracts'           => ['assigneeID', 'acceptorID'],
        'character_mail_messages'       => ['senderID']
    ];

    /**
     * Run the CharacterAffiliation updater. The basic
     * idea is to grab all of the possible characterIDs
     * out of the database, unique them, and ask the EVE
     * API for updated affiliation information.
     *
     * Performance Notes:
     *  Some queries from things like wallet journals will
     * return insanely large datasets! For this reason, we
     * are going to chunk the databse reads and make use of
     * the cache to determine which ids should be resolved.
     *
     * This job will take a *long* time.
     */
    public function call()
    {

        // For every character on the key...
        foreach ($this->api_info->characters as $character) {

            // ... and evey table with information...
            foreach ($this->tables_and_columns as $table_name => $columns) {

                // ... and evey column...
                foreach ($columns as $column)

                    /// Update the affiliation data!
                    $this->getDbDataAndUpdate(
                        $table_name,
                        $column,
                        $character->characterID);

            }
        }

    }

    /**
     * Cunks database values and passes results on to the
     * EVE API Updater.
     *
     * @param string $table
     * @param string $column
     * @param int    $character_id
     */
    public function getDbDataAndUpdate(string $table, string $column, int $character_id)
    {

        DB::table($table)->select($column)->where('characterID', $character_id)
            ->groupBy($column)->chunk($this->ids_to_process, function ($character_ids) use ($column) {

                $ids_to_process = $character_ids->filter(function ($value, $_) use ($column) {

                    // Predetermine what the cache key will be
                    $cache_key = $this->cache_prefix . $value->$column;

                    // If its not cached, it means we havent seen this
                    // characterID yet and should push it for an update
                    // request.
                    if (!cache($cache_key)) {

                        // Add the characterID to the cache for an hour.
                        cache([$cache_key => true], $this->cache_lifetime_minutes);

                        // Return the value for update
                        return $value->$column;
                    }

                    // Returning nothing to the filter() method means
                    // the value will not end up in $ids_to_process

                });

                // If there is something to update, do it.
                if ($ids_to_process->count() > 0)
                    $this->processUpdate($ids_to_process->implode($column, ','));

            });
    }

    /**
     * This method takes a string of comma seperated
     * characterIDs and asks the EVE API for affiliation
     * information about them. The results is stored in
     * the character_affiliations table.
     *
     * @param string $ids
     */
    public function processUpdate(string $ids)
    {

        $result = $this->setScope('eve')
            ->getPheal()
            ->CharacterAffiliation(['ids' => $ids]);

        foreach ($result->characters as $character) {

            CharacterAffiliation::firstOrNew([
                'characterID' => $character->characterID])
                ->fill([
                    'characterName'   => $character->characterName,
                    'corporationID'   => $character->corporationID,
                    'corporationName' => $character->corporationName,
                    'allianceID'      => $character->allianceID,
                    'allianceName'    => $character->allianceName,
                    'factionID'       => $character->factionID,
                    'factionName'     => $character->factionName
                ])
                ->save();
        }

    }

}
