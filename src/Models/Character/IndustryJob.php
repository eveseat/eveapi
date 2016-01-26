<?php
/*
This file is part of SeAT

Copyright (C) 2015, 2016  Leon Jacobs

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

namespace Seat\Eveapi\Models\Character;

use Illuminate\Database\Eloquent\Model;

/**
 * Class IndustryJob
 * @package Seat\Eveapi\Models
 */
class IndustryJob extends Model
{

    /**
     * @var string
     */
    protected $table = 'character_industry_jobs';

    /**
     * @var array
     */
    protected $fillable = [
        'characterID', 'jobID', 'installerID', 'installerName', 'facilityID',
        'solarSystemID', 'solarSystemName', 'stationID', 'activityID', 'blueprintID',
        'blueprintTypeID', 'blueprintTypeName', 'blueprintLocationID', 'outputLocationID',
        'runs', 'cost', 'teamID', 'licensedRuns', 'probability', 'productTypeID',
        'productTypeName', 'status', 'timeInSeconds', 'startDate', 'endDate', 'pauseDate',
        'completedDate', 'completedCharacterID', 'successfulRuns'
    ];
}
