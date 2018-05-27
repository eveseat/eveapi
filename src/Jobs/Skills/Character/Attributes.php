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

namespace Seat\Eveapi\Jobs\Skills\Character;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Skills\CharacterAttribute;

/**
 * Class Attributes.
 * @package Seat\Eveapi\Jobs\Skills\Character
 */
class Attributes extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/attributes/';

    /**
     * @var int
     */
    protected $version = 'v1';

    /**
     * @var string
     */
    protected $scope = 'esi-skills.read_skills.v1';

    /**
     * @var array
     */
    protected $tags = ['character', 'skills', 'attributes'];

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {

        if (! $this->preflighted()) return;

        $attributes = $this->retrieve([
            'character_id' => $this->getCharacterId(),
        ]);

        if ($attributes->isCachedLoad()) return;

        CharacterAttribute::firstOrNew([
            'character_id' => $this->getCharacterId(),
        ])->fill([
            'charisma'                    => $attributes->charisma,
            'intelligence'                => $attributes->intelligence,
            'memory'                      => $attributes->memory,
            'perception'                  => $attributes->perception,
            'willpower'                   => $attributes->willpower,
            'bonus_remaps'                => property_exists($attributes, 'bonus_remaps') ?
                $attributes->bonus_remaps : null,
            'last_remap_date'             => property_exists($attributes, 'last_remap_date') ?
                carbon($attributes->last_remap_date) : null,
            'accrued_remap_cooldown_date' => property_exists($attributes, 'accrued_remap_cooldown_date') ?
                carbon($attributes->accrued_remap_cooldown_date) : null,
        ])->save();
    }
}
