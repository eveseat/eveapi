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

namespace Seat\Eveapi\Models\CorporationProjects;

use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Services\Models\ExtensibleModel;

class CorporationProjectContributor extends ExtensibleModel
{
    protected $table = 'corporation_project_contributors';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $fillable = [
        'project_id',
        'character_id',
        'contributed',
    ];

    protected $casts = [
        'contributed' => 'integer',
    ];

    public function project()
    {
        return $this->belongsTo(CorporationProject::class, 'project_id', 'id');
    }

    public function character()
    {
        return $this->belongsTo(CharacterInfo::class, 'character_id', 'character_id');
    }
}
