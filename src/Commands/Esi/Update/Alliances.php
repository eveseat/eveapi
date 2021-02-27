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
use Seat\Eveapi\Models\Alliances\Alliance;
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
     * @var array
     */
    private $alliance_blacklist = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->processTokens();
        $this->processPublic();
    }

    private function processTokens()
    {
        $tokens = RefreshToken::whereHas('character.affiliation', function ($query) {
            $query->whereNotNull('alliance_id');
        })->when($this->argument('alliance_ids'), function ($tokens) {
            return $tokens->whereHas('character.affiliation', function ($query) {
                $query->whereIn('alliance_id', $this->argument('alliance_ids'));
            });
        })->get()->unique('character.affiliation.alliance_id')->each(function ($token) {

            // init a blacklist which will be seed by token loop in order to prevent multiple jobs targetting same alliance
            // to be queued
            $this->alliance_blacklist[] = $token->character->affiliation->alliance_id;

            // Fire the class to update alliance information
            (new AllianceBus($token->character->affiliation->alliance_id, $token))->fire();
        });

        $this->info('Processed ' . $tokens->count() . ' refresh tokens.');
    }

    private function processPublic()
    {
        // collect optional alliance ID from arguments
        $alliance_ids = $this->argument('alliance_ids') ?: [];

        $alliances = Alliance::query();

        // in case at least one ID has been provided, filter alliances on arguments
        if (! empty($alliance_ids))
            $alliances->whereIn('alliance_id', $alliance_ids);

        // loop over alliances and queue detailed jobs
        // if we don't have any alliance registered -> queue a global job to collect them
        if ($alliances->get()->each(function ($alliance) {

            // ignore already processed alliances
            if (in_array($alliance->alliance_id, $this->alliance_blacklist))
                return true;

            (new AllianceBus($alliance->alliance_id))->fire();
        })->isEmpty() && empty($alliance_ids)) AlliancesJob::dispatch();
    }
}
