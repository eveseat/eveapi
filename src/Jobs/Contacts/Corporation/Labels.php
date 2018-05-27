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
use Seat\Eveapi\Models\Contacts\CorporationContactLabel;

/**
 * Class Labels.
 * @package Seat\Eveapi\Jobs\Contacts\Corporation
 */
class Labels extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/contacts/labels/';

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
    protected $tags = ['corporation', 'contacts', 'labels'];

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

        $labels = $this->retrieve([
            'corporation_id' => $this->getCorporationId(),
        ]);

        if ($labels->isCachedLoad()) return;

        collect($labels)->each(function ($label) {

            CorporationContactLabel::firstOrNew([
                'corporation_id' => $this->getCorporationId(),
                'label_id'     => $label->label_id,
            ])->fill([
                'label_name' => $label->label_name,
            ])->save();
        });

        CorporationContactLabel::where('corporation_id', $this->getCorporationId())
            ->whereNotIn('label_id', collect($labels)->pluck('label_id')->flatten()->all())
            ->delete();
    }
}
