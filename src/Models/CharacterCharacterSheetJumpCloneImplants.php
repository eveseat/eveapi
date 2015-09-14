<?php
/*
This file is part of SeAT

Copyright (C) 2015  Leon Jacobs

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

namespace Seat\Eveapi\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CharacterCharacterSheetJumpCloneImplants
 * @package Seat\Eveapi\Models
 */
class CharacterCharacterSheetJumpCloneImplants extends Model
{

    /**
     * @var array
     */
    protected $fillable = ['jumpCloneID', 'characterID', 'typeID', 'typeName'];

    /**
     * Returns the container Jump Clone
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function jump_clone()
    {

        return $this->belongsTo(
            'Seat\Eveapi\Models\CharacterCharacterSheetJumpClone', 'jumpCloneID', 'jumpCloneID');
    }
}
