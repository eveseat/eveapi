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

namespace Seat\Eveapi\Models\Universe;

use Carbon\Carbon;
use Seat\Eveapi\Traits\HasCompositePrimaryKey;
use Seat\Services\Models\ExtensibleModel;

/**
 * A citadel access cache that is stored in the database
 *
 * @property int citadel_id
 * @property int character_id
 * @property Carbon last_failed_access
 */
class CitadelAccessCache extends ExtensibleModel
{
    use HasCompositePrimaryKey;

    /**
     * @var string
     */
    protected $primaryKey = ['citadel_id', 'character_id'];

    /**
     * @var bool
     */
    public $incrementing = true;

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'citadel_access_cache';
}
