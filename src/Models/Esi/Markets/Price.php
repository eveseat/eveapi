<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2017  Loic Leuilliot
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

namespace Seat\Eveapi\Models\Esi\Markets;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Price
 * @package Seat\Eveapi\Models\Esi\Markets
 */
class Price extends Model
{
    protected $table = 'markets_prices';

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = 'type_id';

    protected $fillable = ['type_id', 'adjusted_price', 'average_price'];
}
