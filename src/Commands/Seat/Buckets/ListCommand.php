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

namespace Seat\Eveapi\Commands\Seat\Buckets;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Seat\Eveapi\Models\Bucket;
use Seat\Eveapi\Traits\BucketManager;

/**
 * Class ListCommand.
 *
 * @package Seat\Eveapi\Commands\Seat\Buckets
 */
class ListCommand extends Command
{
    use BucketManager;

    /**
     * @var string
     */
    protected $signature = 'seat:buckets:list';

    /**
     * @var string
     */
    protected $description = 'Show a list of registered bucket with tokens count';

    /**
     * Execute command.
     */
    public function handle()
    {
        $threshold = $this->getBucketThreshold();
        $buckets = Bucket::select('id')->withCount('refresh_tokens')->get();
        $active = Cache::get('buckets:processed', 0);

        $rows = $buckets->map(function ($bucket) use ($active, $threshold) {
            $status = $bucket->getStatus($threshold);

            if ($bucket->id == $active)
                $status = sprintf('%s *', $status);

            return [
                'id' => $bucket->id,
                'count' => $bucket->refresh_tokens_count,
                'status' => $status,
            ];
        });

        $this->table([
            'Bucket',
            'Tokens',
            'Status',
        ], $rows->toArray());

        if ($rows->where('status', '! overload')->isNotEmpty()) {
            $this->line('');
            $this->warn('Certain buckets are overload. Run seat:buckets:seed to fix this now.');
        }
    }
}
