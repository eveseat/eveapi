<?php
/*
The MIT License (MIT)

Copyright (c) 2015 Leon Jacobs
Copyright (c) 2015 eveseat

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

namespace Seat\Eveapi\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CharacterCharacterSheet
 * @package Seat\Eveapi\Models
 */
class CharacterCharacterSheet extends Model
{

    /**
     * @var string
     */
    protected $primaryKey = 'characterID';

    /**
     * @var array
     */
    protected $fillable = [
        'characterID', 'name', 'homeStationID', 'DoB', 'race', 'bloodLineID',
        'bloodLine', 'ancestryID', 'ancestry', 'gender', 'corporationName',
        'corporationID', 'allianceName', 'allianceID', 'factionName', 'factionID',
        'cloneTypeID', 'cloneName', 'cloneSkillPoints', 'freeSkillPoints',
        'freeRespecs', 'cloneJumpDate', 'lastRespecDate', 'lastTimedRespec',
        'remoteStationDate', 'jumpActivation', 'jumpFatigue', 'jumpLastUpdate',
        'balance', 'intelligence', 'memory', 'charisma', 'perception', 'willpower'
    ];

    /**
     * Return any Jump Clones the character has
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jump_clones()
    {

        return $this->hasMany(
            'Seat\Eveapi\Models\CharacterCharacterSheetJumpClone', 'characterID', 'characterID');
    }

    /**
     * Returns any implants the character may have
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function implants()
    {

        return $this->hasMany(
            'Seat\Eveapi\Models\CharacterCharacterSheetImplants', 'characterID', 'characterID');
    }

    /**
     * Returns any skills the character may have
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function skills()
    {

        return $this->hasMany(
            'Seat\Eveapi\Models\CharacterCharacterSheetSkills', 'characterID', 'characterID');
    }

    /**
     * Returns any corp titles the character may have
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function corporation_titles()
    {

        return $this->hasMany(
            'Seat\Eveapi\Models\CharacterCharacterSheetCorporationTitles', 'characterID', 'characterID');
    }
}
