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

namespace Seat\Eveapi\Models\Mail;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MailHeader.
 * @package Seat\Eveapi\Models\Character
 *
 * @SWG\Definition(
 *     description="Mail Header",
 *     title="MailHeader",
 *     type="object"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="mail_id",
 *     description="The mail identifier"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     property="subject",
 *     description="The mail topic"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="from",
 *     description="The mail sender"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     format="date-time",
 *     property="timestamp",
 *     description="The date-time when the mail has been sent"
 * )
 *
 * @SWG\Property(
 *     type="boolean",
 *     property="boolean",
 *     description="True if the mail has been red"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     property="body",
 *     description="The mail content"
 * )
 *
 * @SWG\Property(
 *     type="array",
 *     property="recipients",
 *     description="A list of recipients",
 *     @SWG\Items(ref="#/definitions/MailRecipient")
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
     * @var null
     */
    protected $primaryKey = null;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function body()
    {

        return $this->hasOne(MailBody::class, 'mail_id', 'mail_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function recipients()
    {

        return $this->hasMany(MailRecipient::class, 'mail_id', 'mail_id');
    }
}
