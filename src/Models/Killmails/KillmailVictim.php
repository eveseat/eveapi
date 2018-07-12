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

/**
 * Class KillmailVictim.
 * @package Seat\Eveapi\Models\Killmails
 *
 * @SWG\Definition(
 *     description="Killmail Victim",
 *     title="KillmailVictim",
 *     type="object"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="killmail_id",
 *     description="The killmail identifier to which this victim is attached"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="character_id",
 *     description="The killed character identified"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="corporation_id",
 *     description="The killed character corporation identifier"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="alliance_id",
 *     description="The killed character alliance identifier (if any)"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="faction_id",
 *     description="The killed character faction identifier (if factional warfare)"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="damage_taken",
 *     description="The damage amount the killed character get"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="ship_type_id",
 *     description="The destroyed ship inventory type identifier"
 * )
 *
 * @SWG\Property(
 *     type="number",
 *     format="double",
 *     property="x",
 *     description="The x coordinate where the kill occurs"
 * )
 *
 * @SWG\Property(
 *     type="number",
 *     format="double",
 *     property="y",
 *     description="The y coordinate where the kill occurs"
 * )
 *
 * @SWG\Property(
 *     type="number",
 *     format="double",
 *     property="z",
 *     description="The z coordinate where the kill occurs"
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
class KillmailVictim extends Model
{
    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $primaryKey = 'killmail_id';
}
