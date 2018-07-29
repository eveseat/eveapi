<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018  Leon Jacobs
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

namespace Seat\Eveapi\Models\Killmails;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Traits\HasCompositePrimaryKey;

/**
 * Class CorporationKillmail.
 * @package Seat\Eveapi\Models\Killmails
 *
 * @SWG\Definition(
 *     description="Character Killmail",
 *     title="CharacterKillmail",
 *     type="object"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="killmail_id",
 *     description="The killmail identifier"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     property="killmail_hash",
 *     description="The killmail hash"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="created_at",
 *     description="The date-time when record has been created into SeAT"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="updated_at",
 *     description="The date-time when record has been updated into SeAT"
 * )
 */
class CorporationKillmail extends Model
{
    use HasCompositePrimaryKey;

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var array
     */
    protected $primaryKey = ['corporation_id', 'killmail_id'];
}
