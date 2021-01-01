<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2020 Leon Jacobs
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

namespace Seat\Eveapi\Observers;

use Seat\Eveapi\Jobs\Character\Info;
use Seat\Eveapi\Models\Bucket;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Eveapi\Traits\BucketManager;
use Seat\Services\Helpers\AnalyticsContainer;
use Seat\Services\Jobs\Analytics;

/**
 * Class RefreshTokenObserver.
 *
 * @package Seat\Eveapi\Observers
 */
class RefreshTokenObserver
{
    use BucketManager;

    /**
     * @param \Seat\Eveapi\Models\RefreshToken $token
     */
    public function created(RefreshToken $token)
    {
        dispatch(new Info($token->character_id))->onQueue('high');

        // update buckets
        $this->seedBuckets();

        $telemetry = new AnalyticsContainer();
        $telemetry->set('type', 'event')
            ->set('ec', 'tokens')
            ->set('ea', 'created')
            ->set('ev', RefreshToken::count());

        dispatch(new Analytics($telemetry));
    }

    /**
     * @param \Seat\Eveapi\Models\RefreshToken $token
     */
    public function restored(RefreshToken $token)
    {
        // update buckets
        $this->seedBuckets();

        $telemetry = new AnalyticsContainer();
        $telemetry->set('type', 'event')
            ->set('ec', 'tokens')
            ->set('ea', 'restored')
            ->set('ev', RefreshToken::count());

        dispatch(new Analytics($telemetry));
    }

    /**
     * @param \Seat\Eveapi\Models\RefreshToken $token
     */
    public function softDeleted(RefreshToken $token)
    {
        $this->deleted($token);

        $telemetry = new AnalyticsContainer();
        $telemetry->set('type', 'event')
            ->set('ec', 'tokens')
            ->set('ea', 'deleted')
            ->set('ev', RefreshToken::count());

        dispatch(new Analytics($telemetry));
    }

    /**
     * @param \Seat\Eveapi\Models\RefreshToken $token
     */
    public function deleted(RefreshToken $token)
    {
        // remove token from his bucket
        $bucket = Bucket::whereHas('disabled_tokens', function ($query) use ($token) {
            $query->where('refresh_tokens.character_id', $token->character_id);
        })->first();

        if ($bucket)
            $bucket->disabled_tokens()->detach($token->character_id);

        // update buckets
        $this->seedBuckets();

        $telemetry = new AnalyticsContainer();
        $telemetry->set('type', 'event')
            ->set('ec', 'tokens')
            ->set('ea', 'deleted')
            ->set('ev', RefreshToken::count());

        dispatch(new Analytics($telemetry));
    }
}
