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

namespace Seat\Eveapi\Jobs\Location\Character;

use Seat\Eveapi\Jobs\AbstractAuthCharacterJob;
use Seat\Eveapi\Models\Location\CharacterOnline;

/**
 * Class Online.
 *
 * @package Seat\Eveapi\Jobs\Location\Character
 */
class Online extends AbstractAuthCharacterJob
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/online/';

    /**
     * @var int
     */
    protected $version = 'v2';

    /**
     * @var string
     */
    protected $scope = 'esi-location.read_online.v1';

    /**
     * @var array
     */
    protected $tags = ['character', 'meta'];

    /**
     * @return string
     */
    public function displayName(): string
    {
        return "Retrieve character status";
    }

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {
        parent::handle();

        $response = $this->retrieve([
            'character_id' => $this->getCharacterId(),
        ]);

        if ($response->isFromCache() && CharacterOnline::find($this->getCharacterId())) return;

        $status = $response->getBody();

        CharacterOnline::firstOrNew([
            'character_id' => $this->getCharacterId(),
        ])->fill([
            'online'      => $status->online,
            'last_login'  => property_exists($status, 'last_login') ?
                carbon($status->last_login) : null,
            'last_logout' => property_exists($status, 'last_logout') ?
                carbon($status->last_logout) : null,
            'logins'      => property_exists($status, 'logins') ?
                $status->logins : null,
        ])->save();
    }
}
