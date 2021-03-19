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
use Seat\Eveapi\Bus\Alliance as AllianceBus;
use Seat\Eveapi\Jobs\Alliances\Alliances as AlliancesJob;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class Alliances.
 *
 * @package Seat\Eveapi\Commands\Esi\Update
 */
class Alliances extends Command
{
    /**
     * @var string
     */
    protected $signature = 'esi:update:alliances {alliance_ids?* : Optional alliance_ids to update}';

    /**
     * @var string
     */
    protected $description = 'Schedule update jobs for alliances';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $alliance_ids = $this->argument('alliance_ids') ?: [];

        // if a specific list of alliance has been provided, update only those.
        if (! empty($alliance_ids)) {
            $this->handleAllianceList($alliance_ids);

            $this->info('Queue ' . count($alliance_ids) . ' update jobs.');

            return;
        }

        // otherwise, queue a job which will pull alliance IDs list and queue update jobs which will pull alliance related data.
        AlliancesJob::dispatch();
    }

    /**
     * @param array $alliance_ids
     */
    private function handleAllianceList(array $alliance_ids)
    {
        foreach ($alliance_ids as $alliance_id) {
            $token = RefreshToken::whereHas('character.affiliation', function ($query) use ($alliance_id) {
                $query->where('alliance_id', $alliance_id);
            })->first();

            (new AllianceBus($alliance_id, $token))->fire();
        }
    }
}
