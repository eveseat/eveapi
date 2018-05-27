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

namespace Seat\Eveapi\Jobs\Corporation;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Corporation\CorporationAllianceHistory;

/**
 * Class AllianceHistory.
 * @package Seat\Eveapi\Jobs\Corporation
 */
class AllianceHistory extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/alliancehistory/';

    /**
     * @var string
     */
    protected $version = 'v2';

    /**
     * @var array
     */
    protected $tags = ['corporation', 'alliance_history'];

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {

        if (! $this->preflighted()) return;

        $history = $this->retrieve([
            'corporation_id' => $this->getCorporationId(),
        ]);

        if ($history->isCachedLoad()) return;

        collect($history)->each(function ($alliance) {

            CorporationAllianceHistory::firstOrNew([
                'corporation_id' => $this->getCorporationId(),
                'record_id'      => $alliance->record_id,
            ])->fill([
                'start_date'  => carbon($alliance->start_date),
                'alliance_id' => $alliance->alliance_id ?? null,
                'is_deleted'  => $alliance->is_deleted ?? false,
            ])->save();

        });
    }
}
