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

namespace Seat\Eveapi\Jobs\Killmails\Character;


use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Killmails\CharacterKillmail;
use Seat\Eveapi\Models\Killmails\KillmailDetail;

/**
 * Class Detail
 * @package Seat\Eveapi\Jobs\Killmails\Character
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
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {

        $killmails = CharacterKillmail::where('character_id', $this->getCharacterId())
            ->whereNotIn('killmail_id', function ($query) {

                $query->select('killmail_id')
                    ->from('killmail_details');
            })->pluck('killmail_id', 'killmail_hash');

        $killmails->each(function ($killmail_id, $killmail_hash) {

            $detail = $this->retrieve([
                'killmail_id'   => $killmail_id,
                'killmail_hash' => $killmail_hash,
            ]);

            KillmailDetail::firstOrCreate([
                'killmail_id'     => $killmail_id,
                'killmail_time'   => carbon($detail->killmail_time),
                'solar_system_id' => $detail->solar_system_id,
                'moon_id'         => property_exists($detail, 'moon_id') ? $detail->moon_id : null,
                'war_id'          => property_exists($detail, 'war_id') ? $detail->war_id : null,
            ]);

            // TODO: Complete Victims && Attackers
        });
    }
}