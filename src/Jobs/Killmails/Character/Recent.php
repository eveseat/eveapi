<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2020 Leon Jacobs
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

use Seat\Eveapi\Jobs\AbstractAuthCharacterJob;
use Seat\Eveapi\Jobs\Killmails\Detail;
use Seat\Eveapi\Models\Killmails\Killmail;
use Seat\Eveapi\Models\Killmails\KillmailDetail;

/**
 * Class Recent.
 * @package Seat\Eveapi\Jobs\Killmails\Character
 */
class Recent extends AbstractAuthCharacterJob
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
    protected $tags = ['character', 'killmail'];

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {
        while (true) {

            $killmails = $this->retrieve([
                'character_id' => $this->getCharacterId(),
            ]);

            if ($killmails->isCachedLoad()) return;

            collect($killmails)->each(function ($killmail) {

                Killmail::firstOrCreate([
                    'killmail_id' => $killmail->killmail_id,
                ], [
                    'killmail_hash' => $killmail->killmail_hash,
                ]);

                if (! KillmailDetail::find($killmail->killmail_id))
                    dispatch(new Detail($killmail->killmail_id, $killmail->killmail_hash));
            });

            if (! $this->nextPage($killmails->pages))
                break;
        }
    }
}
