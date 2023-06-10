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

namespace Seat\Eveapi\Commands\Seat\Cache;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Seat\Services\Contracts\EsiClient;
use Seat\Services\Helpers\AnalyticsContainer;
use Seat\Services\Jobs\Analytics;

/**
 * Class Clear.
 *
 * @package Seat\Eveapi\Commands\Seat\Cache
 */
class Clear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seat:cache:clear {--skip-esi : Do not clear the ESI cache} {--yes : Do not wait for confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear caches used by SeAT.';

    /**
     * @var \Seat\Services\Contracts\EsiClient
     */
    private EsiClient $esi;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(EsiClient $client)
    {
        parent::__construct();

        $this->esi = $client;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $this->line('SeAT Cache Clearing Tool');
        $this->line('');

        if(! $this->option('yes')) {
            if (! $this->confirm('Are you sure you want to clear ALL caches (file/redis/db)?', true)) {

                $this->warn('Exiting without clearing cache');

                return;
            }
        }

        $this->clear_redis_cache();

        // If we are not clearing
        if (! $this->option('skip-esi')) {

            $this->clear_esi_cache();
        }

        // Analytics
        dispatch(new Analytics((new AnalyticsContainer)
            ->set('type', 'event')
            ->set('ec', 'admin')
            ->set('ea', 'cache_clear')
            ->set('el', 'console')));

    }

    /**
     * Flush all keys in Redis.
     */
    public function clear_redis_cache()
    {

        $redis_host = config('database.redis.default.host');
        $redis_port = config('database.redis.default.port');

        $this->info('Clearing the Redis Cache at: ' . $redis_host . ':' . $redis_port);

        try {

            Redis::flushall();

        } catch (Exception $e) {

            $this->error('Failed to clear the Redis Cache. Error: ' . $e->getMessage());

        }

    }

    /**
     * Clear the ESI Storage Cache.
     */
    public function clear_esi_cache()
    {
        // ESI Cache Clearing
        $this->info('Clearing the ESI Cache...');

        $result = $this->esi->getCache()->clear();

        if (! $result)
            $this->error('Failed to clear the ESI Cache. Check configuration.');
    }
}
