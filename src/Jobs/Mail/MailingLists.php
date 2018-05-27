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
use Seat\Eveapi\Models\Mail\MailMailingList;

/**
 * Class MailingLists.
 * @package Seat\Eveapi\Jobs\Mail
 */
class MailingLists extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/mail/lists/';

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
    protected $tags = ['character', 'mails'];

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {

        if (! $this->preflighted()) return;

        $mailing_lists = $this->retrieve([
            'character_id' => $this->getCharacterId(),
        ]);

        if ($mailing_lists->isCachedLoad()) return;

        collect($mailing_lists)->each(function ($list) {

            MailMailingList::firstOrCreate([
                'character_id'    => $this->getCharacterId(),
                'mailing_list_id' => $list->mailing_list_id,
            ], [
                'name'            => $list->name,
            ]);
        });
    }
}
