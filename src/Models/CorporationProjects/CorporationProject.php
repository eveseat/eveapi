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

use Seat\Eveapi\Models\Universe\UniverseName;
use Seat\Services\Models\ExtensibleModel;

/**
 * Class CorporationDivision.
 *
 * @package Seat\Eveapi\Models\Corporation
 */
class CorporationProject extends ExtensibleModel
{

    /**
     * @var bool
     */
    protected static $unguarded = true;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    public function contributors()
    {
        return $this->hasMany(CorporationProjectContributor::class, 'project_id', 'id');
    }

    public function creator()
    {
        return $this->hasOne(UniverseName::class, 'entity_id', 'creator_id')
            ->withDefault([
                'category' => 'character',
                'name' => trans('web::seat.unknown'),
            ]);
    }
}
