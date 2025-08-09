<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to present Leon Jacobs
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

use Seat\Eveapi\Jobs\AbstractAuthCharacterJob;
use Seat\Eveapi\Models\Mail\MailBody;
use Seat\Eveapi\Models\Mail\MailHeader;
use Seat\Eveapi\Models\Mail\MailRecipient;

/**
 * Class Headers.
 *
 * @package Seat\Eveapi\Jobs\Mail
 */
class Mails extends AbstractAuthCharacterJob
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
    protected $tags = ['mail'];

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {
        parent::handle();

        // get last known mail ID to detect when parity has been reached
        $last_known_mail = MailRecipient::where('recipient_id', $this->getCharacterId())
            ->orderBy('mail_id', 'desc')
            ->first();

        $response = $this->retrieve([
            'character_id' => $this->getCharacterId(),
        ]);

        $mails = $response->getBody();

        collect($mails)->each(function ($header) use ($last_known_mail) {

            if (! is_null($last_known_mail) && ($last_known_mail->mail_id == $header->mail_id))
                return false;

            // seed mail header if not exists
            $mail_header = MailHeader::firstOrCreate([
                'mail_id' => $header->mail_id,
            ], [
                'subject' => $header->subject,
                'from' => $header->from,
                'timestamp' => carbon($header->timestamp),
            ]);

            // seed recipient using requested character
            MailRecipient::updateOrCreate([
                'mail_id' => $header->mail_id,
                'recipient_id' => $this->getCharacterId(),
                'recipient_type' => 'character',
            ], [
                'is_read' => property_exists($header, 'is_read') ? $header->is_read : false,
                'labels' => $header->labels,
            ]);

            // Add others mail recipients
            collect($header->recipients)->each(function ($recipient) use ($header) {

                MailRecipient::firstOrCreate([
                    'mail_id' => $header->mail_id,
                    'recipient_id' => $recipient->recipient_id,
                    'recipient_type' => $recipient->recipient_type,
                ]);
            });

            // pull related body if header is new
            if ($mail_header->wasRecentlyCreated) {
                $body = $this->esi->setCompatibilityDate('2025-08-09')->invoke('get', '/characters/{character_id}/mail/{mail_id}/', [
                    'character_id' => $this->getCharacterId(),
                    'mail_id' => $header->mail_id,
                ]);

                MailBody::firstOrCreate([
                    'mail_id' => $header->mail_id,
                ], [
                    'body' => $body->getBody()->body,
                ]);
            }
        });
    }
}
