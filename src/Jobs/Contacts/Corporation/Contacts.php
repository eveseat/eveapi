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

namespace Seat\Eveapi\Jobs\Contacts\Corporation;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Contacts\CorporationContact;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class Contacts.
 * @package Seat\Eveapi\Jobs\Contacts\Corporation
 */
class Contacts extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/contacts/';

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * @var string
     */
    protected $scope = 'esi-corporations.read_contacts.v1';

    /**
     * @var array
     */
    protected $tags = ['corporation', 'contacts'];

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $known_contact_ids;

    /**
     * Contacts constructor.
     *
     * @param \Seat\Eveapi\Models\RefreshToken|null $token
     */
    public function __construct(RefreshToken $token = null)
    {

        $this->known_contact_ids = collect();

        parent::__construct($token);
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle()
    {

        if (! $this->preflighted()) return;

        while (true) {

            $contacts = $this->retrieve([
                'corporation_id' => $this->getCorporationId(),
            ]);

            if ($contacts->isCachedLoad()) return;

            collect($contacts)->each(function ($contact) {

                CorporationContact::firstOrNew([
                    'corporation_id' => $this->getCorporationId(),
                    'contact_id'     => $contact->contact_id,
                ])->fill([
                    'standing'     => $contact->standing,
                    'contact_type' => $contact->contact_type,
                    'is_watched'   => $contact->is_watched ?? false,
                    'label_id'     => $contact->label_id ?? null,
                ])->save();
            });

            $this->known_contact_ids->push(collect($contacts)
                ->pluck('contact_id')->flatten()->all());

            if (! $this->nextPage($contacts->pages))
                break;
        }

        // Cleanup old contacts
        CorporationContact::where('corporation_id', $this->getCorporationId())
            ->whereNotIn('contact_id', $this->known_contact_ids->flatten()->all())
            ->delete();
    }
}
