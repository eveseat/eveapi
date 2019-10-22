<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2020 Leon Jacobs
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

namespace Seat\Eveapi\Models\Contacts;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Universe\UniverseName;

/**
 * Class CorporationContact.
 * @package Seat\Eveapi\Models\Contacts
 *
 * @SWG\Definition(
 *     description="Corporation Contact",
 *     title="CorporationContact",
 *     type="object"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="contact_id",
 *     description="The entity ID"
 * )
 *
 * @SWG\Property(
 *     type="number",
 *     format="float",
 *     property="standing",
 *     description="The standing between -10 and 10"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     enum={"character","corporation","alliance","faction"},
 *     property="contact_type",
 *     description="The entity type"
 * )
 *
 * @SWG\Property(
 *     type="boolean",
 *     property="is_watched",
 *     description="True if the contact is in the watchlist"
 * )
 *
 * @SWG\Property(
 *     type="boolean",
 *     property="is_blocked",
 *     description="True if the contact is in the blacklist"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     property="label_id",
 *     description="The labels mask attached to the the contact"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     property="label_data"
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
class CorporationContact extends Model
{

    /**
     * @var array
     */
    protected $casts = [
        'label_ids' => 'array',
    ];

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function entity()
    {
        return $this->hasOne(UniverseName::class, 'entity_id', 'contact_id')
            ->withDefault([
                'entity_id' => $this->contact_id,
                'name'      => trans('web::seat.unknown'),
                'category'  => $this->contact_type,
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function labels()
    {
        return $this->belongsToMany(CorporationLabel::class);
    }
}
