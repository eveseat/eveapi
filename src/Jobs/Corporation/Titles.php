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

namespace Seat\Eveapi\Jobs\Corporation;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Corporation\CorporationTitle;
use Seat\Eveapi\Models\Corporation\CorporationTitleRole;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class Titles
 * @package Seat\Eveapi\Jobs\Corporation
 */
class Titles extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/titles/';

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $known_titles;

    /**
     * @var array
     */
    protected $types = [
        'roles',
        'grantable_roles',
        'roles_at_hq',
        'grantable_roles_at_hq',
        'roles_at_base',
        'grantable_roles_at_base',
        'roles_at_other',
        'grantable_roles_at_other',
    ];

    /**
     * Titles constructor.
     *
     * @param RefreshToken|null $token
     */
    public function __construct(RefreshToken $token = null)
    {

        $this->known_titles = collect();

        parent::__construct($token);
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {

        $titles = $this->retrieve([
            'corporation_id' => $this->getCorporationId(),
        ]);

        collect($titles)->each(function ($title) {

            CorporationTitle::firstOrNew([
                'corporation_id' => $this->getCorporationId(),
                'title_id'       => $title->title_id,
            ])->fill([
                'name' => $title->name,
            ])->save();

            collect($this->types)->each(function ($type) use ($title) {

                if (! property_exists($title, $type))
                    return;

                collect($title->{$type})->each(function ($name) use ($title, $type) {

                    CorporationTitleRole::firstOrCreate([
                        'corporation_id' => $this->getCorporationId(),
                        'title_id'       => $title->title_id,
                        'type'           => $type,
                        'role'           => $name,
                    ]);
                });

                CorporationTitleRole::where('corporation_id', $this->getCorporationId())
                    ->where('title_id', $title->title_id)
                    ->where('type', $type)
                    ->whereNotIn('role', collect($title->{$type})->flatten()->all())
                    ->delete();

            });

            $this->known_titles->push($title->title_id);

        });

        CorporationTitle::where('corporation_id', $this->getCorporationId())
            ->whereNotIn('title_id', $this->known_titles->flatten()->all())
            ->delete();
    }
}
