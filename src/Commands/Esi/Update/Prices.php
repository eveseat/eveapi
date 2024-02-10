<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to present Leon Jacobs
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

use Illuminate\Bus\Batch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Seat\Eveapi\Jobs\Market\History;
use Seat\Eveapi\Jobs\Market\DispatchHistoryJobs;
use Seat\Eveapi\Jobs\Market\OrderAggregates;
use Seat\Eveapi\Jobs\Market\Orders;
use Seat\Eveapi\Jobs\Market\Prices as PricesJob;
use Seat\Eveapi\Models\Sde\InvType;
use Throwable;

/**
 * Class Prices.
 *
 * @package Seat\Eveapi\Commands\Esi\Update
 */
class Prices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'esi:update:prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule updater jobs which will collect market price stats.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Bus::batch([
            new PricesJob(),
            new DispatchHistoryJobs(),
            [
                new Orders(),
                new OrderAggregates()
            ]
        ])
            ->then(function (Batch $batch) {
                logger()->info(
                    sprintf('[Batches][%s] %s - Succeeded : %d/%d - %d failed.',
                        $batch->id, $batch->name, $batch->totalJobs - $batch->failedJobs, $batch->totalJobs, $batch->failedJobs));
            })
            ->catch(function (Batch $batch, Throwable $e) {
                logger()->error(
                    sprintf('[Batches][%s] %s - Some jobs have failed : %s',
                        $batch->id, $batch->name, implode(', ', $batch->failedJobIds)));
                logger()->error(sprintf('[Batches][%s] %s : %s', $batch->id, $batch->name, $e->getMessage()));
            })
            ->finally(function (Batch $batch) {
                logger()->info(
                    sprintf('[Batches][%s] %s - Completed : %d/%d - %d failed.',
                        $batch->id, $batch->name, $batch->totalJobs - $batch->failedJobs, $batch->totalJobs, $batch->failedJobs));
            })
            ->allowFailures()
            ->onQueue('public')
            ->name('Market Prices')
            ->dispatch();

        return $this::SUCCESS;
    }
}
