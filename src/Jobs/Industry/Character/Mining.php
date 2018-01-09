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

namespace Seat\Eveapi\Jobs\Industry\Character;


use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Industry\CharacterMining;

/**
 * Class Mining
 * @package Seat\Eveapi\Jobs\Industry\Character
 */
class Mining extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/mining/';

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {

        while (true) {

            $mining = $this->retrieve([
                'character_id' => $this->getCharacterId(),
            ]);

            collect($mining)->each(function ($ledger_entry) {


                CharacterMining::firstOrNew([
                    'character_id'    => $this->getCharacterId(),
                    'date'            => $ledger_entry->date,
                    'solar_system_id' => $ledger_entry->solar_system_id,
                    'type_id'         => $ledger_entry->type_id,
                ])->fill([
                    'quantity' => $ledger_entry->quantity,
                ])->save();

            });

            if (! $this->nextPage($mining->pages))
                break;
        }
    }
}