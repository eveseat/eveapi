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
use Seat\Eveapi\Models\Mail\MailHeader;
use Seat\Eveapi\Models\Mail\MailRecipient;

/**
 * Class Headers.
 * @package Seat\Eveapi\Jobs\Mail
 */
class Headers extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/mail/';

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
    protected $tags = ['character', 'mail', 'headers'];

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {

        if (! $this->preflighted()) return;

        $mail = $this->retrieve([
            'character_id' => $this->getCharacterId(),
        ]);

        if ($mail->isCachedLoad()) return;

        collect($mail)->each(function ($header) {

            MailHeader::firstOrCreate([
                'character_id' => $this->getCharacterId(),
                'mail_id'      => $header->mail_id,
            ], [
                'subject'      => $header->subject,
                'from'         => $header->from,
                'timestamp'    => carbon($header->timestamp),
                'labels'       => json_encode($header->labels),
            ]);

            // Update the 'read' status
            if (isset($header->is_read))

                MailHeader::where('character_id', $this->getCharacterId())
                    ->where('mail_id', $header->mail_id)
                    ->update(['is_read' => $header->is_read]);

            // Add mail recipients
            collect($header->recipients)->each(function ($recipient) use ($header) {

                MailRecipient::firstOrCreate([
                    'mail_id'        => $header->mail_id,
                    'recipient_id'   => $recipient->recipient_id,
                    'recipient_type' => $recipient->recipient_type,
                ]);
            });
        });
    }
}
