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
use OpenApi\Annotations as OA;
use Seat\Eveapi\Models\Universe\UniverseName;

/**
 * Class MailRecipient.
 *
 * @package Seat\Eveapi\Models\Mail
 *
 * @OA\Schema(
 *     description="Mail Recipient",
 *     title="MailRecipient",
 *     type="object"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     format="int64",
 *     property="recipient_id",
 *     description="The recipient ID"
 * )
 *
 * @OA\Property(
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
    public $incrementing = true;

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $casts = [
        'labels' => 'array',
    ];

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
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function mailing_list()
    {
        return $this->hasOne(MailMailingList::class, 'mailing_list_id', 'recipient_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function entity()
    {
        return $this->hasOne(UniverseName::class, 'entity_id', 'recipient_id')
            ->withDefault([
                'name'      => trans('web::seat.unknown'),
                'category'  => 'character',
            ]);
    }
}
