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
use Seat\Eveapi\Jobs\Character\Roles;
use Seat\Eveapi\Models\Bucket;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Eveapi\Models\RefreshTokenSchedule;

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

    private array $scheduled_corporations = [];

    /**
     * Execute command.
     */
    public function handle()
    {
        // retrieve the bucket which need to be processed.
        $bucket = $this->getNextBucket();

        // store bucket ID, so we keep track of the flow.
        Cache::forever('buckets:processed', $bucket->id);

        $bucket->refresh_tokens()
            ->with(['character', 'affiliation', 'token_schedule'])
            ->get()->each(function (RefreshToken $token) {
                $this->updateToken($token);
            });
    }

    /**
     * Checks whether a token should be updated and dispatches the required jobs
     *
     * @param RefreshToken $token
     * @return void
     */
    private function updateToken(RefreshToken $token): void
    {
        // this contains the info when this character was last scheduled and how often the character should get scheduled
        $token_schedule = $token->token_schedule;

        // ensure the update interval is not below the cache time
        $esi_update_interval = max(60 * 60, $token_schedule->update_interval ?? null);

        // schedule the character if 1: he hasn't been scheduled before, 2: enough time has passed since the last time
        if($token_schedule === null || $token_schedule->last_update->diffInSeconds(now()) > $esi_update_interval) {
            $this->dispatchCharacterEsiUpdate($token);
        }
        // if the token hasn't been updated in the last day, make sure to schedule a single job to keep the token alive
        // the value is 23 hours instead of 24 so the web ui, using 24h, never shows it as outdated
        elseif ($token->updated_at->lt(now()->subHours(23))) {
            $this->dispatchCharacterTokenKeepAlive($token);
        }
    }

    /**
     * Dispatches the jobs to update the character
     *
     * @param RefreshToken $token
     * @return void
     */
    private function dispatchCharacterEsiUpdate(RefreshToken $token): void
    {
        // update the last_update field so the token won't be scheduled again
        $token_schedule = $token->token_schedule;
        if($token_schedule === null) {
            $token_schedule = new RefreshTokenSchedule();
            $token_schedule->character_id = $token->character_id;
        }
        $token_schedule->last_update = now();
        $token_schedule->save();

        // dispatch the jobs for this character
        (new Character($token->character_id, $token))->fire();
        logger()->debug('[Buckets] Processing token from a bucket', [
            'flow' => 'character',
            'token' => $token->character_id,
        ]);

        // if this is a director, dispatch corporation jobs, but only if no other director has already been scheduled
        if (
            $token->affiliation->corporation_id !== null
            && $token->character->corporation_roles->where('scope', 'roles')->where('role', 'Director')->isNotEmpty()
            && ! $this->isCorporationAlreadyScheduled($token->affiliation->corporation_id)
        ) {
            $this->markCorporationScheduled($token->affiliation->corporation_id);
            (new Corporation($token->affiliation->corporation_id, $token))->fire();
            logger()->debug('[Buckets] Processing token from a bucket.', [
                'flow' => 'corporation',
                'token' => $token->character_id,
            ]);
        }
    }

    /**
     * Dispatches a job that uses the token, so it doesn't expire.
     *
     * @param RefreshToken $token
     * @return void
     */
    private function dispatchCharacterTokenKeepAlive(RefreshToken $token): void
    {
        // TODO: add a job that only requests a new access token instead of a random esi job. This will require some eseye rework
        Roles::dispatch($token)->onQueue('characters');
    }

    /**
     * Determine what is the next bucket to process.
     *
     * @return Bucket
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

    /**
     * Determine if jobs for a corporation have already been scheduled
     *
     * @param int $corporation_id
     * @return bool
     */
    private function isCorporationAlreadyScheduled(int $corporation_id): bool
    {
        return array_key_exists($corporation_id, $this->scheduled_corporations);
    }

    /**
     * Mark a corporation as already being processed
     *
     * @param int $corporation_id
     * @return void
     */
    private function markCorporationScheduled(int $corporation_id): void
    {
        $this->scheduled_corporations[$corporation_id] = true;
    }
}
