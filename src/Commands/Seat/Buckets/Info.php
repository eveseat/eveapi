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
 * Class Info.
 *
 * @package Seat\Eveapi\Commands\Seat\Buckets
 */
class Info extends Command
{
    use BucketManager;

    /**
     * @var string
     */
    protected $signature = 'seat:buckets:info {bucket_id : ID from a bucket you want details about}';

    /**
     * @var string
     */
    protected $description = 'Show information from a specified bucket with included tokens.';

    /**
     * Execute Command.
     */
    public function handle()
    {
        $threshold = $this->getBucketThreshold();
        $bucket = Bucket::with('refresh_tokens', 'refresh_tokens.character')
            ->withCount('refresh_tokens')
            ->find($this->argument('bucket_id'));

        if (! $bucket) {
            $this->error('Provided bucket ID is invalid.');

            return;
        }

        $status = $bucket->getStatus($threshold);
        $active = Cache::get('buckets:processed', 0);

        if ($bucket->id == $active)
            $status = sprintf('%s *', $status);

        $rows = $bucket->refresh_tokens->map(function ($token) {
            return [
                $token->character_id,
                $token->character->name ?: 'unknown',
                $token->expires_on,
                $token->expires_on->gt(carbon()) ? 'valid' : 'expired',
            ];
        });

        $this->line(sprintf('ID: %d', $bucket->id));
        $this->line(sprintf('Tokens count: %d', $bucket->refresh_tokens_count));
        $this->line(sprintf('Status: %s', $status));

        if ($status == '! overload')
            $this->warn('This bucket is overload. Run seat:buckets:seed to fix this now.');

        $this->line('');

        $this->table([
            'Character ID',
            'Character Name',
            'Expires On',
            'Status',
        ], $rows->toArray());
    }
}
