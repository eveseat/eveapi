<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018, 2019  Leon Jacobs
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
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Corporation\CorporationInfo;

/**
 * Class MailRecipient.
 * @package Seat\Eveapi\Models\Mail
 *
 * @SWG\Definition(
 *     description="Mail Recipient",
 *     title="MailRecipient",
 *     type="object"
 * )
 *
 * @SWG\Property(
 *     type="integer",
 *     format="int64",
 *     property="recipient_id",
 *     description="The recipient ID"
 * )
 *
 * @SWG\Property(
 *     type="string",
 *     enum={"character", "corporation", "alliance", "mailing_list"},
 *     property="recipient_type",
 *     description="The recipient qualifier"
 * )
 */
class MailRecipient extends Model
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mail()
    {
        return $this->belongsTo(MailHeader::class, 'mail_id', 'mail_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function body()
    {
        return $this->hasOne(MailBody::class, 'mail_id', 'mail_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|null
     */
    public function mailing_list()
    {
        if ($this->recipient_type !== 'mailing_list')
            return null;

        return $this->belongsTo(MailMailingList::class, 'recipient_id', 'mailing_list_id');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|null
     */
    public function character_view()
    {
        if ($this->recipient_type !== 'character')
            return null;

        $character_id = $this->mail->character_id;

        $character = CharacterInfo::find($this->recipient_id) ?: $this->recipient_id;

        return view('web::partials.character', compact('character', 'character_id'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|null
     */
    public function corporation_view()
    {
        if ($this->recipient_type !== 'corporation')
            return null;

        $character_id = $this->mail->character_id;

        $corporation = CorporationInfo::find($this->recipient_id) ?: $this->recipient_id;

        return view('web::partials.corporation', compact('corporation', 'character_id'));
    }
}
