<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018, 2019  Leon Jacobs
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
use Seat\Eveapi\Models\Sde\InvType;
use Seat\Eveapi\Models\Sde\RamActivity;
use Seat\Eveapi\Models\Universe\UniverseStructure;

/**
 * Class CharacterIndustryJob.
 * @package Seat\Eveapi\Models\Industry
 *
 * @OA\Schema(
 *     description="Character Industry Jobs",
 *     title="CharacterIndustryJob",
 *     type="object"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     format="int64",
 *     property="job_id",
 *     description="The job ID"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     format="int64",
 *     property="installer_id",
 *     description="The character who start the job"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     format="int64",
 *     property="facility_id",
 *     description="The structure where the job has been started"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="station_id",
 *     description="The outpost where the job has been started (deprecated)"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="activity_id",
 *     description="The activity type used for the job"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     format="int64",
 *     property="blueprint_id",
 *     description="The item blueprint ID on which the job is based"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     format="int64",
 *     property="blueprint_location_id",
 *     description="The place where the blueprint is stored"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     format="int64",
 *     property="output_location_id",
 *     description="The place where the resulting item should be put"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="runs",
 *     description="The runs amount for the activity"
 * )
 *
 * @OA\Property(
 *     type="number",
 *     format="double",
 *     property="cost",
 *     description="The job installation cost"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="licensed_runs",
 *     description="The number of copy"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="probability",
 *     description="The success rate"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     enum={"active","cancelled","delivered","paused","ready","reverted"},
 *     property="status",
 *     description="The job status"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="duration",
 *     description="The job duration in seconds"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     format="date-time",
 *     property="start_date",
 *     description="The date-time when job has been started"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     format="date-time",
 *     property="end_date",
 *     description="The date-time when job should be done"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     format="date-time",
 *     property="pause_date",
 *     description="The date-time when job has been paused"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     format="date-time",
 *     property="completed_date",
 *     description="The date-time when job has been delivered"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     format="int64",
 *     property="completed_character_id",
 *     description="The character who deliver the job"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="successful_runs",
 *     description="The amount of completed runs"
 * )
 *
 * @OA\Property(
 *     property="blueprint",
 *     description="The used blueprint type",
 *     ref="#/components/schemas/InvType"
 * )
 *
 * @OA\Property(
 *     property="product",
 *     description="The output type",
 *     ref="#/components/schemas/InvType"
 * )
 */
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
