<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2021 Leon Jacobs
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
use Seat\Eveapi\Jobs\Character\Affiliation;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Universe\UniverseName;

/**
 * Class Affiliations.
 *
 * @package Seat\Eveapi\Commands\Esi\Update
 */
class Affiliations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'esi:update:affiliations {character_id?* : Optional character_ids to update}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule updater jobs for characters affiliations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $character_ids = collect($this->argument('character_id'));

        // build small batch of a maximum of 1000 entries to avoid long running job.
        if (! $character_ids->isEmpty()) {
            $character_ids->unique()->chunk(Affiliation::REQUEST_ID_LIMIT)->each(function ($chunk) {
                $character_ids = $chunk->toArray();
                Affiliation::dispatch($character_ids);
            });

            return;
        }

        // in case no IDs has been specified, collect all characters and universe names.
        // build small batch of a maximum of 200 entries to avoid long running job.
        CharacterInfo::select('character_id')
            ->union(UniverseName::where('category', 'character')
                ->selectRaw('entity_id as character_id')
            )
            ->orderBy('character_id')
            ->chunk(Affiliation::REQUEST_ID_LIMIT, function ($chunk) {
                $character_ids = $chunk->pluck('character_id')->toArray();
                Affiliation::dispatch($character_ids);
        });
    }
}
