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

namespace Seat\Eveapi\Traits;

use Illuminate\Support\Collection;
use Seat\Eveapi\Models\Bucket;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Trait BucketManager.
 *
 * @package Seat\Eveapi\Traits
 */
trait BucketManager
{
    /**
     * This is the average duration of all jobs related to a token, in seconds.
     *
     * @var int
     */
    protected $average_batch_duration = 120;

    /**
     * This is the amount of seconds in which all tokens have to be processed.
     *
     * @var int
     */
    protected $update_window = 3600;

    /**
     * Update buckets and balance them based on threshold.
     */
    protected function seedBuckets()
    {
        // retrieve current bucket threshold
        $threshold = $this->getBucketThreshold();

        // in case we don't have enough bucket to satisfy threshold, spawn more of them
        $buckets_count = Bucket::count();
        $required_buckets = ceil(RefreshToken::count() / $threshold);

        if ($buckets_count < $required_buckets)
            $this->spawnBuckets($required_buckets - $buckets_count);

        // collect all buckets with the amount of tokens
        $buckets = Bucket::withCount('refresh_tokens')->get();

        // reduce buckets load which exceed threshold
        $this->reduceBucketLoad($buckets, $threshold);

        // exclude buckets which already meet threshold value
        $this->balanceBucketLoad($buckets, $threshold);

        // drop any orphan buckets
        Bucket::doesntHave('refresh_tokens')->delete();
    }

    /**
     * Take all buckets which are exceeding threshold and remove some tokens to reduce their load.
     *
     * @param  \Illuminate\Support\Collection  $buckets
     * @param  int  $threshold
     */
    private function reduceBucketLoad(Collection $buckets, float $threshold)
    {
        $buckets->where('refresh_tokens_count', '>', $threshold)->each(function ($bucket) use ($threshold) {
            $exceeding_tokens = $bucket->refresh_tokens->count() - $threshold;
            $bucket->refresh_tokens()->detach($bucket->refresh_tokens->take($exceeding_tokens)->pluck('character_id'));
        });
    }

    /**
     * Take all buckets which are under threshold and add tokens to them in order to balance their load.
     *
     * @param  \Illuminate\Support\Collection  $buckets
     * @param  float  $threshold
     */
    private function balanceBucketLoad(Collection $buckets, float $threshold)
    {
        // retrieve all unbalanced buckets and store their list to balance them
        $unbalanced_buckets = $buckets->where('refresh_tokens_count', '<', $threshold)->sortBy('id');

        // loop over each bucket and leverage attached tokens to reach threshold
        $unbalanced_buckets->each(function ($new_bucket) use ($threshold) {

            $tokens_count = $new_bucket->refresh_tokens()->count();
            $missing_tokens = $threshold - $tokens_count;

            // retrieve tokens to attach to the bucket and attach them to the bucket
            $tokens = $this->getOrphanTokens($missing_tokens);
            $new_bucket->refresh_tokens()->attach($tokens->pluck('character_id'));
        });
    }

    /**
     * Return the computed size of a bucket.
     * Buckets can have a number of tokens between 1 and this value.
     *
     * @return float
     */
    private function getBucketThreshold()
    {
        // count the number of tokens available in the system
        $tokens = RefreshToken::count();

        // determine the size of a bucket, round up
        $threshold = ceil($tokens * $this->average_batch_duration / $this->update_window);

        return $threshold == 0 ? 1 : $threshold;
    }

    /**
     * Return the first refresh token which is not already tied to a bucket.
     *
     * @param  int  $count
     * @return \Illuminate\Support\Collection
     */
    private function getOrphanTokens(int $count): Collection
    {
        return RefreshToken::select('refresh_tokens.character_id')
            ->leftJoin('bucket_refresh_token', 'refresh_tokens.character_id', '=', 'bucket_refresh_token.character_id')
            ->whereNull('bucket_refresh_token.character_id')
            ->limit($count)
            ->get();
    }

    /**
     * Seed database with missing buckets amount.
     *
     * @param  int  $count  The number of buckets to spawn.
     */
    private function spawnBuckets(int $count)
    {
        Bucket::insert(array_fill(1, $count, [
            'created_at' => carbon(),
            'updated_at' => carbon(),
        ]));
    }
}
