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

namespace Seat\Eveapi\Jobs\Mail;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Mail\MailBody;
use Seat\Eveapi\Models\Mail\MailHeader;

/**
 * Class Bodies.
 * @package Seat\Eveapi\Jobs\Mail
 */
class Bodies extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/mail/{mail_id}/';

    /**
     * @var int
     */
    protected $version = 'v1';

    /**
     * @var string
     */
    protected $scope = 'esi-mail.read_mail.v1';

    /**
     * @var array
     */
    protected $tags = ['character', 'mail', 'bodies'];

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {

        if (! $this->preflighted()) return;

        // Determine which mail headers do not have bodies yet
        $mail_ids = MailHeader::where('character_id', $this->getCharacterId())
            ->whereNotIn('mail_id', function ($query) {

                $query->select('mail_id')
                    ->from('mail_bodies');

            })->pluck('mail_id');

        // Process the mailid's that are missing their bodies.
        $mail_ids->each(function ($mail_id) {

            $body = $this->retrieve([
                'character_id' => $this->getCharacterId(),
                'mail_id'      => $mail_id,
            ]);

            if ($body->isCachedLoad()) return;

            MailBody::firstOrCreate([
                'mail_id' => $mail_id,
            ], [
                'body'    => $body->body,
            ]);
        });
    }
}
