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

namespace Seat\Eveapi\Jobs\Contacts\Character;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Contacts\CharacterContactLabel;

/**
 * Class Labels.
 * @package Seat\Eveapi\Jobs\Contacts\Character
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
    protected $endpoint = '/characters/{character_id}/contacts/labels/';

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * @var string
     */
    protected $scope = 'esi-characters.read_contacts.v1';

    /**
     * @var array
     */
    protected $tags = ['character', 'contacts', 'labels'];

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
            'character_id' => $this->getCharacterId(),
        ]);

        if ($labels->isCachedLoad()) return;

        collect($labels)->each(function ($label) {

            CharacterContactLabel::firstOrNew([
                'character_id' => $this->getCharacterId(),
                'label_id'     => $label->label_id,
            ])->fill([
                'label_name' => $label->label_name,
            ])->save();
        });

        CharacterContactLabel::where('character_id', $this->getCharacterId())
            ->whereNotIn('label_id', collect($labels)->pluck('label_id')->flatten()->all())
            ->delete();
    }
}
