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

namespace Seat\Eveapi\Models\Mail;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Universe\UniverseName;

/**
 * Class MailHeader.
 *
 * @package Seat\Eveapi\Models\Character
 *
 * @OA\Schema(
 *     description="Mail Header",
 *     title="MailHeader",
 *     type="object"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     format="int64",
 *     property="mail_id",
 *     description="The mail identifier"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     property="subject",
 *     description="The mail topic"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     format="int64",
 *     property="from",
 *     description="The mail sender"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     format="date-time",
 *     property="timestamp",
 *     description="The date-time when the mail has been sent"
 * )
 *
 * @OA\Property(
 *     type="boolean",
 *     property="boolean",
 *     description="True if the mail has been red"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     property="body",
 *     description="The mail content"
 * )
 *
 * @OA\Property(
 *     type="array",
 *     property="recipients",
 *     description="A list of recipients",
 *     @OA\Items(ref="#/components/schemas/MailRecipient")
 * )
 */
class MailHeader extends Model
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
    protected $primaryKey = 'mail_id';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function body()
    {

        return $this->hasOne(MailBody::class, 'mail_id', 'mail_id')
            ->withDefault([
                'body' => '',
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function recipients()
    {

        return $this->hasMany(MailRecipient::class, 'mail_id', 'mail_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function sender()
    {

        return $this->hasOne(UniverseName::class, 'entity_id', 'from')
            ->withDefault([
                'name'      => trans('web::seat.unknown'),
                'category'  => 'character',
            ]);
    }
}
