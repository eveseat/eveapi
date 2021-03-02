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

namespace Seat\Eveapi\Models\Industry;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Corporation\CorporationStructure;
use Seat\Eveapi\Models\Sde\Moon;

/**
 * Class CorporationIndustryMiningExtraction.
 * @package Seat\Eveapi\Models\Industry
 */
class CorporationIndustryMiningExtraction extends Model
{
    /**
     * Return the theoretical duration of a chunk once it reached its drilling cycle.
     */
    const THEORETICAL_DEPLETION_COUNTDOWN = 172800;

    /**
     * Return the minimum allowed drilling duration (base from Singularity : 6 days and 3 minutes).
     */
    const MINIMUM_DRILLING_DURATION = 518580;

    /**
     * Return the maximum allowed drilling duration (base from Singularity : 55 days, 23 hours and 24 minutes).
     */
    const MAXIMUM_DRILLING_DURATION = 4836240;

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var string
     */
    protected $primaryKey = 'moon_id';

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @return \Carbon\Carbon
     */
    public function getExpiresAtAttribute()
    {
        return carbon($this->chunk_arrival_time)->addSeconds(self::THEORETICAL_DEPLETION_COUNTDOWN);
    }

    /**
     * @param $value
     */
    public function setExtractionStartTimeAttribute($value)
    {
        $this->attributes['extraction_start_time'] = is_null($value) ? null : carbon($value);
    }

    /**
     * @param $value
     */
    public function setChunkArrivalTimeAttribute($value)
    {
        $this->attributes['chunk_arrival_time'] = is_null($value) ? null : carbon($value);
    }

    /**
     * @param $value
     */
    public function setNaturalDecayTimeAttribute($value)
    {
        $this->attributes['natural_decay_time'] = is_null($value) ? null : carbon($value);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function moon()
    {
        return $this->belongsTo(Moon::class, 'moon_id', 'moon_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function structure()
    {
        return $this->belongsTo(CorporationStructure::class, 'structure_id', 'structure_id');
    }

    /**
     * Determine if a chunk can be drill.
     *
     * @return bool
     */
    public function isReady()
    {
        return carbon()->gte(carbon($this->chunk_arrival_time));
    }
}
