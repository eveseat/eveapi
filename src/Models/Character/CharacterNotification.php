<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2021 Leon Jacobs
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

namespace Seat\Eveapi\Models\Character;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Universe\UniverseName;
use Symfony\Component\Yaml\Yaml;

/**
 * Class CharacterNotification.
 * @package Seat\Eveapi\Models\Character
 *
 * @OA\Schema(
 *     description="Character Notification",
 *     title="CharacterNotification",
 *     type="object"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     property="notification_id",
 *     description="The notification identifier"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     property="type",
 *     description="The notification type"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     format="int64",
 *     property="sender_id",
 *     description="The entity who sent the notification"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     enum={"character","corporation","alliance","faction","other"},
 *     property="sender_type",
 *     description="The sender qualifier"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     format="date-time",
 *     property="timestamp",
 *     description="The date-time when notification has been sent"
 * )
 *
 * @OA\Property(
 *     type="boolean",
 *     property="is_read",
 *     description="True if the notification has been red"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     property="object",
 *     description="The notification content"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     format="date-time",
 *     property="created_at",
 *     description="The date-time when notification has been created into SeAT"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     format="date-time",
 *     property="updated_at",
 *     description="The date-time when notification has been updated into SeAT"
 * )
 */
class CharacterNotification extends Model
{
    /**
     * @var array
     */
    protected $casts = [
        'is_read' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at'];

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var mixed
     */
    private $parsed_text;

    /**
     * Return YAML parsed value of the notification content.
     *
     * @param $value
     * @return mixed
     */
    public function getTextAttribute($value)
    {
        if (is_null($this->parsed_text) && ! is_null($value))
            $this->parsed_text = Yaml::parse($value);

        return $this->parsed_text;
    }

    /**
     * Reset parsed value of notification content and update raw value.
     *
     * @param $value
     */
    public function setTextAttribute($value)
    {
        $this->parsed_text = null;
        $this->attributes['text'] = $value;
    }

    /**
     * @param $value
     */
    public function setTimestampAttribute($value)
    {
        $this->attributes['timestamp'] = is_null($value) ? null : carbon($value);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function recipient()
    {
        return $this->belongsTo(CharacterInfo::class, 'character_id', 'character_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function sender()
    {
        return $this->hasOne(UniverseName::class, 'entity_id', 'sender_id')
            ->withDefault([
                'name'      => trans('web::seat.unknown'),
                'category'  => 'character',
            ]);
    }
}
