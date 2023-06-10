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

namespace Seat\Eveapi\Jobs\Killmails;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Mapping\Killmails\AttackerMapping;
use Seat\Eveapi\Mapping\Killmails\VictimMapping;
use Seat\Eveapi\Models\Killmails\KillmailAttacker;
use Seat\Eveapi\Models\Killmails\KillmailDetail;
use Seat\Eveapi\Models\Killmails\KillmailVictim;

/**
 * Class Detail.
 *
 * @package Seat\Eveapi\Jobs\Killmails
 */
class Detail extends EsiBase
{
    /**
     * @var int
     */
    private $killmail_id;

    /**
     * @var string
     */
    private $killmail_hash;

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
     * @var array
     */
    protected $tags = ['killmail'];

    /**
     * Detail constructor.
     *
     * @param  int  $killmail_id
     * @param  string  $killmail_hash
     */
    public function __construct(int $killmail_id, string $killmail_hash)
    {
        parent::__construct();

        $this->killmail_id = $killmail_id;
        $this->killmail_hash = $killmail_hash;

        array_push($this->tags, $killmail_id);
    }

    /**
     * Execute the job.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function handle()
    {
        $response = $this->retrieve([
            'killmail_id'   => $this->killmail_id,
            'killmail_hash' => $this->killmail_hash,
        ]);

        $detail = $response->getBody();

        $killmail = KillmailDetail::firstOrCreate([
            'killmail_id'     => $this->killmail_id,
        ], [
            'killmail_time'   => carbon($detail->killmail_time),
            'solar_system_id' => $detail->solar_system_id,
            'moon_id'         => property_exists($detail, 'moon_id') ? $detail->moon_id : null,
            'war_id'          => property_exists($detail, 'war_id') ? $detail->war_id : null,
        ]);

        $victim = KillmailVictim::firstOrNew([
            'killmail_id'    => $this->killmail_id,
        ]);

        VictimMapping::make($victim, $detail->victim, [
            'killmail_id' => function () {
                return $this->killmail_id;
            },
        ])->save();

        collect($detail->attackers)->each(function ($attacker) {

            $model = KillmailAttacker::firstOrNew([
                'killmail_id'     => $this->killmail_id,
                'character_id'    => property_exists($attacker, 'character_id') ?
                    $attacker->character_id : null,
                'corporation_id'  => property_exists($attacker, 'corporation_id') ?
                    $attacker->corporation_id : null,
                'alliance_id'     => property_exists($attacker, 'alliance_id') ?
                    $attacker->alliance_id : null,
                'faction_id'      => property_exists($attacker, 'faction_id') ?
                    $attacker->faction_id : null,
            ]);

            AttackerMapping::make($model, $attacker, [
                'killmail_id' => function () {
                    return $this->killmail_id;
                },
            ])->save();
        });

        if (property_exists($detail->victim, 'items')) {

            collect($detail->victim->items)->each(function ($item) use ($victim) {

                $pivot_attributes = [
                    'flag'      => $item->flag,
                    'singleton' => $item->singleton,
                ];

                if (property_exists($item, 'quantity_destroyed'))
                    $pivot_attributes['quantity_destroyed'] = $item->quantity_destroyed;

                if (property_exists($item, 'quantity_dropped'))
                    $pivot_attributes['quantity_dropped'] = $item->quantity_dropped;

                $victim->items()->attach($item->item_type_id, $pivot_attributes);
            });
        }

        event(sprintf('eloquent.updated: %s', KillmailDetail::class), $killmail);
    }
}
