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

namespace Seat\Eveapi\Commands\Esi\Update;

use Illuminate\Console\Command;
use Seat\Eveapi\Bus\Character;
use Seat\Eveapi\Bus\Corporation;
use Seat\Eveapi\Jobs\Universe\Names;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Eveapi\Models\Wallet\CharacterWalletJournal;
use Seat\Eveapi\Models\Wallet\CharacterWalletTransaction;
use Seat\Eveapi\Models\Wallet\CorporationWalletJournal;
use Seat\Eveapi\Models\Wallet\CorporationWalletTransaction;

/**
 * Class PublicInfo.
 * @package Seat\Eveapi\Commands\Esi\Update
 */
class PublicInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'esi:update:public';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule updater jobs for public information';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $entities_list = $this->buildUniqueEntitiesIdList();

        $entities_list->chunk(Names::ITEMS_LIMIT)->each(function ($chunk) {
            Names::dispatch($chunk);
        });

        CharacterInfo::doesntHave('refresh_token')->each(function ($character) {
            (new Character($character->character_id))->fire();
        });

        CorporationInfo::all()->each(function ($corporation) {
            (new Corporation($corporation->corporation_id))->fire();
        });
    }

    /**
     * @return array
     */
    private function buildUniqueEntitiesIdList()
    {
        $entities_id = collect();

        // collect entries from character wallet
        $entities_id->push(CharacterWalletJournal::select('first_party_id')
            ->whereNotNull('first_party_id')
            ->distinct()
            ->get()
            ->pluck('first_party_id')
            ->toArray());

        $entities_id->push(CharacterWalletJournal::select('second_party_id')
            ->whereNotNull('second_party_id')
            ->distinct()
            ->get()
            ->pluck('second_party_id')
            ->toArray());

        $entities_id->push(CharacterWalletTransaction::select('client_id')
            ->whereNotNull('client_id')
            ->distinct()
            ->get()
            ->pluck('client_id')
            ->toArray());

        // collect entities from corporation wallet
        $entities_id->push(CorporationWalletJournal::select('first_party_id')
            ->whereNotNull('first_party_id')
            ->distinct()
            ->get()
            ->pluck('first_party_id')
            ->toArray());

        $entities_id->push(CorporationWalletJournal::select('second_party_id')
            ->whereNotNull('second_party_id')
            ->distinct()
            ->get()
            ->pluck('second_party_id')
            ->toArray());

        $entities_id->push(CorporationWalletTransaction::select('client_id')
            ->whereNotNull('client_id')
            ->distinct()
            ->get()
            ->pluck('client_id')
            ->toArray());

        return $entities_id->unique()->toArray();
    }
}
