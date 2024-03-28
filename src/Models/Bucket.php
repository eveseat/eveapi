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

namespace Seat\Eveapi\Models;

use Seat\Services\Models\ExtensibleModel;

/**
 * Class Bucket.
 *
 * @package Seat\Eveapi\Models
 */
class Bucket extends ExtensibleModel
{
    /**
     * @param  int  $threshold
     * @return string
     */
    public function getStatus(int $threshold)
    {
        $status = '! overload';

        if ($this->refresh_tokens_count == $threshold)
            $status = 'balanced';

        if ($this->refresh_tokens_count < $threshold)
            $status = 'available';

        return $status;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function refresh_tokens()
    {
        return $this->belongsToMany(RefreshToken::class, 'bucket_refresh_token', 'bucket_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function disabled_tokens()
    {
        return $this->belongsToMany(RefreshToken::class, 'bucket_refresh_token', 'bucket_id', 'character_id')
            ->onlyTrashed();
    }
}
