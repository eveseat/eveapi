<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017  Leon Jacobs
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

namespace Seat\Eveapi\Models\Eve;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Models\Account\AccountStatus;
use Seat\Eveapi\Models\Account\ApiKeyInfo;
use Seat\Eveapi\Models\Account\ApiKeyInfoCharacters;
use Seat\Eveapi\Models\JobLog;
use Seat\Web\Models\User;

/**
 * Class ApiKey.
 * @package Seat\Eveapi\Models
 */
class ApiKey extends Model
{
    /**
     * @var string
     */
    protected $table = 'eve_api_keys';

    /**
     * @var string
     */
    protected $primaryKey = 'key_id';

    /**
     * @var array
     */
    protected $casts = [
        'api_call_constraints' => 'array',
    ];

    /**
     * @var array
     */
    protected $fillable = ['key_id', 'v_code', 'user_id', 'enabled', 'last_error'];

    /**
     * Make sure we cleanup when a key is deleted.
     *
     * @return bool|null
     * @throws \Exception
     */
    public function delete()
    {

        // Cleanup the info
        $this->info()->delete();

        // Cleanup the people groups this key is related to
        $this->deleteRelatedPeopleGroups();

        // Cleanup the characters this key had
        $this->characters()->delete();

        return parent::delete();
    }

    /**
     * Returns the key information such as accessMask.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function info()
    {

        return $this->hasOne(
            ApiKeyInfo::class, 'keyID', 'key_id');
    }

    /**
     * Returns the characters for the key.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function characters()
    {

        return $this->hasMany(
            ApiKeyInfoCharacters::class, 'keyID', 'key_id');
    }

    /**
     * Returns the owner of this key.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function owner()
    {

        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * Returns the account status for the key.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function status()
    {

        return $this->hasOne(
            AccountStatus::class, 'keyID', 'key_id');
    }

    /**
     * Returns the update joblogs for this key.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function job_logs()
    {

        return $this->hasMany(JobLog::class, 'key_id');
    }

    /**
     * Cleans up the people groups members related to this API key.
     */
    private function deleteRelatedPeopleGroups()
    {

        // drop any people & place relation directly related to the deleted key
        DB::table('person_members')->where('key_id', $this->key_id)
            ->delete();

        // retrieve all characters related to the deleted key
        foreach ($this->characters as $character) {

            // count character occurrence
            $linked_character_count = DB::table('account_api_key_info_characters')
                ->where('characterID', $character->characterID)
                ->count();

            // if the deleted key is the last one referring to the character,
            // drop the people and group and its children
            if ($linked_character_count == 1) {

                DB::table('person_members')
                    ->where('person_id', function ($query) use ($character) {

                        $query->select('id')->from('people')
                            ->where('main_character_id', $character->characterID);
                    })
                    ->delete();

                DB::table('people')->where('main_character_id', $character->characterID)
                    ->delete();
            }
        }
    }
}
