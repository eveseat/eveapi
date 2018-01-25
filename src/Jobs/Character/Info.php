<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017  Leon Jacobs
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

namespace Seat\Eveapi\Jobs\Character;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Character\CharacterInfo;

/**
 * Class Info
 * @package Seat\Eveapi\Jobs\Character
 */
class Info extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/';

    /**
     * @var int
     */
    protected $version = 'v4';

    /**
     * @var array
     */
    protected $tags = ['character', 'info'];

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle()
    {

        $character_info = $this->retrieve([
            'character_id' => $this->getCharacterId(),
        ]);

        CharacterInfo::firstOrNew(['character_id' => $this->getCharacterId()])->fill([
            'name'            => $character_info->name,
            'description'     => $character_info->optional('description'),
            'corporation_id'  => $character_info->corporation_id,
            'alliance_id'     => $character_info->optional('alliance_id'),
            'birthday'        => $character_info->birthday,
            'gender'          => $character_info->gender,
            'race_id'         => $character_info->race_id,
            'bloodline_id'    => $character_info->bloodline_id,
            'ancenstry_id'    => $character_info->optional('ancenstry_id'),
            'security_status' => $character_info->optional('security_status'),
            'faction_id'      => $character_info->optional('faction_id'),
        ])->save();
    }
}
