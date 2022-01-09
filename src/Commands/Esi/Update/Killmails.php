<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2022 Leon Jacobs
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Seat\Eveapi\Commands\Esi\Update;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Seat\Eveapi\Jobs\Killmails\Character\Recent as RecentCharacterKills;
use Seat\Eveapi\Jobs\Killmails\Corporation\Recent as RecentCorporationKills;
use Seat\Eveapi\Jobs\Killmails\Detail;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Eveapi\Models\Killmails\Killmail;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class Killmails.
 *
 * @package Seat\Eveapi\Commands\Esi\Update
 */
class Killmails extends Command
{
    /**
     * @var string
     */
    protected $signature = 'esi:update:killmails {killmail_ids?* : Optional killmail_ids to update}';

    /**
     * @var string
     */
    protected $description = 'Schedule update jobs for killmails';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // collect optional kills ID from arguments
        $killmail_ids = $this->argument('killmail_ids') ?: [];

        $killmails = Killmail::whereDoesntHave('detail');

        // in case at least one ID has been provided, filter kills on arguments
        if (! empty($killmail_ids))
            $killmails->whereIn('killmail_id', $killmail_ids);

        // loop over kills and queue detailed jobs
        // if we don't have any kills registered -> queue character and corporation jobs to collect them
        $data = $killmails->get();

        if ($data->isNotEmpty()) {
            Bus::batch($data->map(function ($killmail) {
                return new Detail($killmail->killmail_id, $killmail->killmail_hash);
            }))->name('Killmails batch')->dispatch();

            return $this::SUCCESS;
        }

        if (empty($killmail_ids)) {
            RefreshToken::chunk(100, function ($tokens) {
                $tokens->each(function ($token) {
                    $character = CharacterInfo::firstOrNew(
                        ['character_id' => $token->character_id],
                        ['name' => "Unknown Character : {$token->character_id}"]
                    );

                    Bus::batch([new RecentCharacterKills($token)])
                        ->name("{$character->name} Killmails")
                        ->dispatch();
                });
            });

            RefreshToken::whereHas('character.affiliation', function ($query) {
                $query->whereNotNull('corporation_id');
            })->whereHas('character.corporation_roles', function ($query) {
                $query->where('scope', 'roles');
                $query->where('role', 'Director');
            })->get()->unique('character.affiliation.corporation_id')->each(function ($token) {
                $corporation = CorporationInfo::firstOrNew(
                    ['corporation_id' => $token->character->affiliation->corporation_id],
                    ['name' => "Unknown Corporation : {$token->character->affiliation->corporation_id}"]
                );

                Bus::batch([new RecentCorporationKills($token->character->affiliation->corporation_id, $token)])
                    ->name("{$corporation->name} Killmails")
                    ->dispatch();
            });
        }

        return $this::SUCCESS;
    }
}
