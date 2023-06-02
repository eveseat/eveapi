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

namespace Seat\Eveapi\Models\Industry;

use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;
use Seat\Eveapi\Models\Sde\InvType;
use Seat\Eveapi\Models\Sde\RamActivity;
use Seat\Eveapi\Models\Universe\UniverseStructure;

#[OA\Schema(
    title: 'CharacterIndustryJob',
    description: 'Character Industry Job',
    properties: [
        new OA\Property(property: 'job_id', description: 'The job ID', type: 'integer', format: 'int64'),
        new OA\Property(property: 'installer_id', description: 'The character who start the job', type: 'integer', format: 'int64'),
        new OA\Property(property: 'facility_id', description: 'The structure where the job has been started', type: 'integer', format: 'int64'),
        new OA\Property(property: 'station_id', description: 'The outpost where the job has been started (deprecated)', type: 'integer'),
        new OA\Property(property: 'activity_id', description: 'The activity type used for the job', type: 'integer'),
        new OA\Property(property: 'blueprint_id', description: 'The item blueprint ID on which the job is based', type: 'integer', format: 'int64'),
        new OA\Property(property: 'blueprint_location_id', description: 'The place where the blueprint is stored', type: 'integer', format: 'int64'),
        new OA\Property(property: 'output_location_id', description: 'The place where the resulting item should be put', type: 'integer', format: 'int64'),
        new OA\Property(property: 'runs', description: 'The runs amount for the activity', type: 'integer'),
        new OA\Property(property: 'cost', description: 'The job installation fee', type: 'number', format: 'double'),
        new OA\Property(property: 'licensed_runs', description: 'The number of copy', type: 'integer'),
        new OA\Property(property: 'probability', description: 'The success rate', type: 'integer'),
        new OA\Property(property: 'status', description: 'The job status', type: 'string', enum: ['active', 'cancelled', 'delivered', 'paused', 'ready', 'reverted']),
        new OA\Property(property: 'duration', description: 'The job duration in seconds', type: 'integer'),
        new OA\Property(property: 'start_date', description: 'The date/time when job has been started', type: 'string', format: 'date-time'),
        new OA\Property(property: 'end_date', description: 'The date/time when job should be done', type: 'string', format: 'date-time'),
        new OA\Property(property: 'pause_date', description: 'The date/time when job has been paused', type: 'string', format: 'date-time'),
        new OA\Property(property: 'completed_date', description: 'The date/time when job has been delivered', type: 'string', format: 'date-time'),
        new OA\Property(property: 'completed_character_id', description: 'The character who deliver the job', type: 'integer', format: 'int64'),
        new OA\Property(property: 'successful_runs', description: 'The amount of completed runs', type: 'integer'),
        new OA\Property(property: 'blueprint', ref: '#/components/schemas/InvType', description: 'The used blueprint type'),
        new OA\Property(property: 'product', ref: '#/components/schemas/InvType', description: 'The output type'),
    ],
    type: 'object'
)]
class CharacterIndustryJob extends Model
{
    /**
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at'];

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var array
     */
    protected $primaryKey = 'job_id';

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @param $value
     */
    public function setStartDateAttribute($value)
    {
        $this->attributes['start_date'] = is_null($value) ? null : carbon($value);
    }

    /**
     * @param $value
     */
    public function setPauseDateAttribute($value)
    {
        $this->attributes['pause_date'] = is_null($value) ? null : carbon($value);
    }

    /**
     * @param $value
     */
    public function setEndDateAttribute($value)
    {
        $this->attributes['end_date'] = is_null($value) ? null : carbon($value);
    }

    /**
     * @param $value
     */
    public function setCompletedDateAttribute($value)
    {
        $this->attributes['completed_date'] = is_null($value) ? null : carbon($value);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function activity()
    {
        return $this->hasOne(RamActivity::class, 'activityID', 'activity_id')
            ->withDefault([
                'activityName' => trans('web::seat.unknown'),
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function blueprint()
    {
        return $this->hasOne(InvType::class, 'typeID', 'blueprint_type_id')
            ->withDefault([
                'typeName' => trans('web::seat.unknown'),
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function location()
    {
        return $this->hasOne(UniverseStructure::class, 'structure_id', 'facility_id')
            ->withDefault([
                'name' => trans('web::seat.unknown'),
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function product()
    {
        return $this->hasOne(InvType::class, 'typeID', 'product_type_id')
            ->withDefault([
                'typeName' => trans('web::seat.unknown'),
            ]);
    }
}
