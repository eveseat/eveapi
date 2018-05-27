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

/**
 * Class Recent.
 * @package Seat\Eveapi\Jobs\Killmails\Character
 */
class Recent extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/killmails/recent/';

    /**
     * @var int
     */
    protected $version = 'v1';

    /**
     * @var string
     */
    protected $scope = 'esi-killmails.read_killmails.v1';

    /**
     * @var array
     */
    protected $tags = ['character', 'killmails'];

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {

        if (! $this->preflighted()) return;

        $killmails = $this->retrieve([
            'character_id' => $this->getCharacterId(),
        ]);

        if ($killmails->isCachedLoad()) return;

        collect($killmails)->each(function ($killmail) {

            CharacterKillmail::firstOrCreate([
                'character_id'  => $this->getCharacterId(),
                'killmail_id'   => $killmail->killmail_id,
            ], [
                'killmail_hash' => $killmail->killmail_hash,
            ]);
        });
    }
}
