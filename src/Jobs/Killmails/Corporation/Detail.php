<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018  Leon Jacobs
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

namespace Seat\Eveapi\Jobs\Killmails\Corporation;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Killmails\CorporationKillmail;
use Seat\Eveapi\Models\Killmails\KillmailAttacker;
use Seat\Eveapi\Models\Killmails\KillmailDetail;
use Seat\Eveapi\Models\Killmails\KillmailVictim;
use Seat\Eveapi\Models\Killmails\KillmailVictimItem;

/**
 * Class Detail.
 * @package Seat\Eveapi\Jobs\Killmails\Corporation
 */
class Detail extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/killmails/{killmail_id}/{killmail_hash}/';

    /**
     * @var int
     */
    protected $version = 'v1';

    /**
     * @var string
     */
    protected $scope = 'esi-killmails.read_corporation_killmails.v1';

    /**
     * @var array
     */
    protected $tags = ['corporation', 'killmails'];

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {

        if (! $this->preflighted()) return;

        $killmails = CorporationKillmail::where('corporation_id', $this->getCorporationId())
            ->whereNotIn('killmail_id', function ($query) {

                $query->select('killmail_id')
                    ->from('killmail_details');

            })->pluck('killmail_id', 'killmail_hash');

        $killmails->each(function ($killmail_id, $killmail_hash) {

            $detail = $this->retrieve([
                'killmail_id'   => $killmail_id,
                'killmail_hash' => $killmail_hash,
            ]);

            if ($detail->isCachedLoad()) return;

            KillmailDetail::firstOrCreate([
                'killmail_id'     => $killmail_id,
            ], [
                'killmail_time'   => carbon($detail->killmail_time),
                'solar_system_id' => $detail->solar_system_id,
                'moon_id'         => $detail->moon_id ?? null,
                'war_id'          => $detail->war_id ?? null,
            ]);

            KillmailVictim::firstOrCreate([
                'killmail_id'    => $killmail_id,
            ], [
                'character_id'   => $detail->victim->character_id ?? null,
                'corporation_id' => $detail->victim->corporation_id ?? null,
                'alliance_id'    => $detail->victim->alliance_id ?? null,
                'faction_id'     => $detail->victim->faction_id ?? null,
                'damage_taken'   => $detail->victim->damage_taken,
                'ship_type_id'   => $detail->victim->ship_type_id,
                'x'              => $detail->victim->position->x ?? null,
                'y'              => $detail->victim->position->y ?? null,
                'z'              => $detail->victim->position->z ?? null,
            ]);

            collect($detail->attackers)->each(function ($attacker) use ($killmail_id) {

                KillmailAttacker::firstOrCreate([
                    'killmail_id'     => $killmail_id,
                    'character_id'    => $attacker->character_id ?? null,
                    'corporation_id'  => $attacker->corporation_id ?? null,
                    'alliance_id'     => $attacker->alliance_id ?? null,
                    'faction_id'      => $attacker->faction_id ?? null,
                    'security_status' => $attacker->security_status,
                    'final_blow'      => $attacker->final_blow,
                    'damage_done'     => $attacker->damage_done,
                    'ship_type_id'    => $attacker->ship_type_id ?? null,
                    'weapon_type_id'  => $attacker->weapon_type_id ?? null,
                ]);
            });

            if (property_exists($detail->victim, 'items')) {

                collect($detail->victim->items)->each(function ($item) use ($killmail_id) {

                    KillmailVictimItem::firstOrNew([
                        'killmail_id'  => $killmail_id,
                        'item_type_id' => $item->item_type_id,
                    ])->fill([
                        'quantity_destroyed' => $item->quantity_destroyed ?? null,
                        'quantity_dropped'   => $item->quantity_dropped ?? null,
                        'singleton'          => $item->singleton,
                        'flag'               => $item->flag,
                    ])->save();

                    // TODO: Process $item->items as a nested model.
                });
            }
        });
    }
}
