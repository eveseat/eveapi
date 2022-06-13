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

namespace Seat\Eveapi\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Models\Alliances\Alliance;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Eveapi\Models\FailedJob;
use Seat\Eveapi\Models\Status\EsiStatus;
use Seat\Eveapi\Models\Status\ServerStatus;

/**
 * Class Maintenance.
 *
 * @package Seat\Eveapi\Jobs
 */
class Maintenance implements ShouldQueue
{

    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * The maximum duration of job in seconds before being considered as dead.
     *
     * @var int
     */
    public $timeout = 12000;

    /**
     * The maximum duration of job in seconds before being retried.
     *
     * @var int
     */
    public $retryAfter = 12001;

    /**
     * Perform the maintenance job.
     */
    public function handle()
    {

        $this->cleanup_tables();

        if (setting('cleanup_data', true) == 'yes') {
            $this->cleanup_characters();
            $this->cleanup_corps();
        }
    }

    /**
     * Partially truncates tables that typically contain
     * a lot of data.
     */
    public function cleanup_tables()
    {

        logger()->info('Performing tables maintenance');

        // Prune the failed jobs table
        FailedJob::where('id', '<', (FailedJob::max('id') - 100))->delete();

        // Prune the server statuses older than a week.
        ServerStatus::where('created_at', '<', carbon('now')->subWeek(1))->delete();

        // Prune ESI statuses older than a week
        EsiStatus::where('created_at', '<', carbon('now')->subWeek(1))->delete();

        // ask database to rebuild index in order to properly reduce their space usage on drive
        DB::statement('OPTIMIZE TABLE failed_jobs, server_status, esi_status, character_assets, character_wallet journals, contract_details');
    }

    private function cleanup_characters()
    {
        CharacterInfo::doesntHave('refresh_token')->delete();
    }

    private function cleanup_corps()
    {

        // Need to find all corps that dont have a reason to be kept (no chars with tokens and not part of an alliance that has an active member)
        Alliance::doesntHave('corporations.characters.refresh_token')->each(function ($alliance) {
            $alliance->corporations()->whereNotBetween('alliance_members.corporation_id', [1000000, 1999999])->delete();
        });

        // Now delete all the corps that have no alliance and no active tokens
        CorporationInfo::whereNotBetween('corporation_id', [1000000, 1999999])
            ->doesntHave('alliance')
            ->doesntHave('characters.refresh_token')
            ->delete();
    }
}
