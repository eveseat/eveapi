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

namespace Seat\Eveapi\Jobs\Contracts\Corporation;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Contracts\ContractItem;
use Seat\Eveapi\Models\Contracts\CorporationContract;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class Items.
 * @package Seat\Eveapi\Jobs\Contracts\Corporation
 */
class Items extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/contracts/{contract_id}/items/';

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * @var string
     */
    protected $scope = 'esi-contracts.read_corporation_contracts.v1';

    /**
     * @var array
     */
    protected $tags = ['corporation', 'contracts', 'items'];

    /**
     * The number of requests made in the current throttle cycle.
     *
     * https://github.com/ccpgames/esi-issues/issues/636
     *
     * > The way it works is you can make 20 requests per 10 seconds
     * > for a contract tied to a specific character ID.
     *
     * @var int
     */
    protected $iteration_count = 0;

    /**
     * The time when the current throttle iteration cycle started.
     *
     * @var \Carbon\Carbon
     */
    protected $cycle_start;

    /**
     * The number of seconds for a single throttle cycle.
     *
     * @var int
     */
    protected $cycle_duration = 10;

    /**
     * The maximum number of requests that can be made per
     * throttling cycle.
     *
     * @var int
     */
    protected $max_cycle_requests = 20;

    /**
     * The maximum runtime for this job before Horizon
     * comes along and kills the fun.
     *
     * 10 minutes.
     *
     * @var \Illuminate\Config\Repository|mixed
     */
    protected $max_job_runtime = 600;

    /**
     * When did this job start.
     *
     * Used when calculating when we should stop.
     *
     * @var \Carbon\Carbon
     */
    protected $job_start_time;

    /**
     * Items constructor.
     *
     * @param \Seat\Eveapi\Models\RefreshToken|null $token
     */
    public function __construct(RefreshToken $token = null)
    {

        $this->cycle_start = carbon('now');
        $this->job_start_time = carbon('now');

        parent::__construct($token);
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {

        if (! $this->authenticated()) return;

        $empty_contracts = CorporationContract::join('contract_details',
            'corporation_contracts.contract_id', '=',
            'contract_details.contract_id')
            ->where('corporation_id', $this->getCorporationId())
            ->where('type', '<>', 'courier')
            ->where('status', '<>', 'deleted')
            ->where('volume', '>', 0)
            ->whereNotIn('corporation_contracts.contract_id', function ($query) {

                $query->select('contract_id')
                    ->from('contract_items');

            })
            ->pluck('corporation_contracts.contract_id');

        $empty_contracts->each(function ($contract_id) {

            $this->iteration_count++;

            $items = $this->retrieve([
                'corporation_id' => $this->getCorporationId(),
                'contract_id'    => $contract_id,
            ]);

            if ($items->isCachedLoad()) return;

            collect($items)->each(function ($item) use ($contract_id) {

                ContractItem::firstOrCreate([
                    'contract_id' => $contract_id,
                    'record_id'   => $item->record_id,
                ], [
                    'type_id'      => $item->type_id,
                    'quantity'     => $item->quantity,
                    'raw_quantity' => $item->raw_quantity ?? null,
                    'is_singleton' => $item->is_singleton,
                    'is_included'  => $item->is_included,
                ]);
            });

            // Check if we should be stopping this job all together.
            // The nexy time a job is queued it will just continue
            // where it left off.
            if ($this->job_start_time->addSeconds($this->max_job_runtime) > carbon('now'))
                return false;

            // Check if we should be sleeping. This should be true if we
            // have made 20 requests in the last 10 seconds.
            // If the time we started, plus 10 seconds is more than the current
            // time, wait for the remainder of the time.
            if ($this->iteration_count >= $this->max_cycle_requests &&
                $this->cycle_start->addSeconds($this->cycle_duration) > carbon('now')) {

                $wait_duration = $this->cycle_start->addSeconds($this->cycle_duration)
                    ->diffInSeconds(carbon('now'));

                sleep($wait_duration);

                // Reset the cycle start time as well as the iteration count.
                $this->cycle_start = carbon('now');
                $this->iteration_count = 0;
            }

            // Check if we should just reset the iteration & cycle count as a result of
            // us not using the full 20 requests in a 10 second window.
            if ($this->cycle_start->addSeconds($this->cycle_duration) < carbon('now')) {

                $this->cycle_start = carbon('now');
                $this->iteration_count = 0;
            }

        });
    }
}
