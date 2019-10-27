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
use Seat\Eveapi\Traits\HasCompositePrimaryKey;

/**
 * Class CorporationIndustryJob.
 * @package Seat\Eveapi\Models\Industry
 *
 * @SWG\Definition(
 *     description="Corporation Industry Jobs",
 *     title="CorporationIndustryJob",
 *     type="object"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="job_id",
 *     description="The job ID"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="installer_id",
 *     description="The character who start the job"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="facility_id",
 *     description="The structure where the job has been started"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="station_id",
 *     description="The outpost where the job has been started (deprecated)"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="activity_id",
 *     description="The activity type used for the job"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="blueprint_id",
 *     description="The item blueprint ID on which the job is based"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="blueprint_type_id",
 *     description="The used blueprint type"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="blueprint_location_id",
 *     description="The place where the blueprint is stored"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="output_location_id",
 *     description="The place where the resulting item should be put"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="runs",
 *     description="The runs amount for the activity"
 * )
 *
 * @SWG\Property(
 *     type="number",
 *     format="double",
 *     property="cost",
 *     description="The job installation cost"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="licensed_runs",
 *     description="The number of copy"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="probability",
 *     description="The success rate"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="product_type_id",
 *     description="The resulting item type"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     enum={"active","cancelled","delivered","paused","ready","reverted"},
 *     property="status",
 *     description="The job status"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="duration",
 *     description="The job duration in seconds"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="start_date",
 *     description="The date-time when job has been started"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="end_date",
 *     description="The date-time when job should be done"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="pause_date",
 *     description="The date-time when job has been paused"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="completed_date",
 *     description="The date-time when job has been delivered"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="completed_character_id",
 *     description="The character who deliver the job"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="successful_runs",
 *     description="The amount of completed runs"
 * )
 *
 * @SWG\Property(
 *     type="object",
 *     property="created_at",
 *     description="The contact creation date",
 *     @SWG\Property(
 *          type="string",
 *          format="date-time",
 *          property="date"
 *     ),
 *     @SWG\Property(
 *          type="integer",
 *          property="timezone_type"
 *     ),
 *     @SWG\Property(
 *          type="string",
 *          property="timezone"
 *     )
 * )
 *
 * @SWG\Property(
 *     type="object",
 *     property="updated_at",
 *     description="The contact creation date",
 *     @SWG\Property(
 *          type="string",
 *          format="date-time",
 *          property="date"
 *     ),
 *     @SWG\Property(
 *          type="integer",
 *          property="timezone_type"
 *     ),
 *     @SWG\Property(
 *          type="string",
 *          property="timezone"
 *     )
 * )
 */
class CorporationIndustryJob extends Model
{
    use HasCompositePrimaryKey;

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var array
     */
    protected $primaryKey = ['corporation_id', 'job_id'];

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
