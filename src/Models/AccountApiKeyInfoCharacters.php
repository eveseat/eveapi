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
 * Class AccountApiKeyInfoCharacters
 * @package Seat\Eveapi\Models
 */
class AccountApiKeyInfoCharacters extends Model
{

    /**
     * @var array
     */
    protected $fillable = [
        'keyID', 'characterID', 'characterName', 'corporationID', 'corporationName'];

    /**
     * Returns the KeyInfo this character belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function key_info()
    {

        return $this->hasOne(
            'Seat\Eveapi\Models\AccountApiKeyInfo', 'keyID', 'keyID');
    }

    /**
     * Returns the API Key a character belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function key()
    {

        return $this->hasOne(
            'Seat\Eveapi\Models\EveApiKey', 'key_id', 'keyID');
    }
}
