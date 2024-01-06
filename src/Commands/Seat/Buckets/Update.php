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

namespace Seat\Eveapi\Commands\Seat\Buckets;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Seat\Eveapi\Bus\Character;
use Seat\Eveapi\Bus\Corporation;
use Seat\Eveapi\Models\Bucket;

/**
 * Class Update.
 *
 * @package Seat\Eveapi\Commands\Seat\Buckets
 */
class Update extends Command
{
    /**
     * @var string
     */
    protected $signature = 'seat:buckets:update';

    /**
     * @var string
     */
    protected $description = 'Schedule jobs from next bucket to update tokens.';

    /**
     * Execute command.
     */
    public function handle()
    {
        // retrieve the bucket which need to be processed.
        $bucket = $this->getNextBucket();

        // store bucket ID, so we keep track of the flow.
        Cache::forever('buckets:processed', $bucket->id);

        $this->updateCharacters($bucket);
        $this->updateCorporations($bucket);
    }

    /**
     * Update characters tied to the bucket tokens.
     *
     * @param  \Seat\Eveapi\Models\Bucket  $bucket
     */
    private function updateCharacters(Bucket $bucket)
    {
        // loop over each attached tokens and enqueue job tied to this token.
        $bucket->refresh_tokens->each(function ($token) use ($bucket) {

            // create a cache entry with TTL 1 hour - so we can prevent a character to be updated more than once
            // in the defined update window.
            $lock = Cache::lock(sprintf('buckets:characters:%d', $token->character_id), 3600);

            // if we are not able to spawn the entry, log the event and interrupt the command.
            if (! $lock->get()) {
                logger()->warning('[Buckets] This character has already been processed during the last update window. Process has been interrupted.', [
                    'bucket_id' => $bucket->id,
                    'character_id' => $token->character_id,
                    'update_window' => 3600,
                ]);

                return;
            }

            // queue character jobs for the selected token.
            (new Character($token->character_id, $token))->fire();

            logger()->debug('[Buckets] Processing token from a bucket', [
                'bucket' => $bucket->id,
                'flow' => 'character',
                'token' => $token->character_id,
            ]);
        });
    }

    /**
     * Update corporations tied to the bucket tokens.
     *
     * @param  \Seat\Eveapi\Models\Bucket  $bucket
     */
    private function updateCorporations(Bucket $bucket)
    {
        $bucket->refresh_tokens()->whereHas('character.affiliation', function ($query) {
            $query->whereNotNull('corporation_id');
        })->whereHas('character.corporation_roles', function ($query) {
            $query->where('scope', 'roles');
            $query->where('role', 'Director');
        })->get()->unique('character.affiliation.corporation_id')->each(function ($token) use ($bucket) {

            // create a cache entry with TTL 1 hour - so we can prevent a corporation to be updated more than once
            // in the defined update window.
            $lock = Cache::lock(sprintf('buckets:corporations:%d',
                $token->character->affiliation->corporation_id), 3600);

            // if we are not able to spawn the entry, log the event and interrupt the command.
            if (! $lock->get()) {
                logger()->warning('[Buckets] This corporation has already been processed during the last update window. Process has been interrupted.', [
                    'bucket_id' => $bucket->id,
                    'corporation_id' => $token->character->affiliation->corporation_id,
                    'update_window' => 3600,
                ]);

                return;
            }

            // Fire the class to update corporation information
            (new Corporation($token->character->affiliation->corporation_id, $token))->fire();

            logger()->debug('[Buckets] Processing token from a bucket.', [
                'bucket' => $bucket->id,
                'flow' => 'corporation',
                'token' => $token->character_id,
            ]);
        });
    }

    /**
     * Determine what is the next bucket to process.
     *
     * @return \Seat\Eveapi\Models\Bucket
     */
    private function getNextBucket(): Bucket
    {
        // retrieve last processed bucket ID.
        $last_bucket = $this->getLastProcessedBucketID();

        // pickup next bucket based.
        $bucket = Bucket::where('id', '>', $last_bucket)->orderBy('id')->first();

        // in case we cannot find any bucket, restart cycle from the beginning.
        if (is_null($bucket)) {
            $bucket = Bucket::orderBy('id')->first();

            // if we're still not able to find a candidate, spawn a new bucket.
            if (! $bucket) {
                $bucket = new Bucket();
                $bucket->save();

                return $bucket;
            }
        }

        return $bucket;
    }

    /**
     * Determine from Redis which bucket has been processed lastly.
     *
     * @return int
     */
    private function getLastProcessedBucketID(): int
    {
        return Cache::get('buckets:processed') ?: 0;
    }
}
