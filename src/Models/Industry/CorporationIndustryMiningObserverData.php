<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2021 Leon Jacobs
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
use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Sde\InvType;
use Seat\Eveapi\Models\Universe\UniverseName;

/**
 * Class CorporationIndustryMiningObserverData.
 *
 * @package Seat\Eveapi\Models\Industry
 */
class CorporationIndustryMiningObserverData extends Model
{
    /**
     * @var string
     */
    protected $table = 'corporation_industry_mining_observer_data';

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var bool
     */
    public $incrementing = true;

    /**
     * @inheritdoc
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($data) {
            if (! isset($data->extraction_id) || ($data->extraction_id == 0)) {
                $minDate = carbon($data->last_updated)->subDays(5);
                $maxDate = carbon($data->last_updated)->addDays(5);

                $extraction = CorporationIndustryMiningExtraction::select()
                    ->where('structure_id', $data->observer_id)
                    ->whereBetween('chunk_arrival_time', [$minDate, $maxDate])
                    ->get()
                    ->first();
                if ($extraction) {
                    $data->extraction_id = $extraction->id;
                }
            }
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function extraction()
    {
        return $this->belongsTo(CorporationIndustryMiningExtraction::class, 'extraction_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function character()
    {
        return $this->belongsTo(CharacterInfo::class, 'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function universe_name()
    {
        return $this->belongsTo(UniverseName::class, 'character_id', 'entity_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function type()
    {
        return $this->hasOne(InvType::class, 'typeID', 'type_id')
            ->withDefault([
                'typeName' => trans('seat::web.unknown'),
            ]);
    }
}
