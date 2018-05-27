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
use Seat\Eveapi\Models\Corporation\CorporationOutpost;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class Outposts.
 * @package Seat\Eveapi\Jobs\Corporation
 */
class Outposts extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/outposts/';

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * @var string
     */
    protected $scope = 'esi-corporations.read_outposts.v1';

    /**
     * @var array
     */
    protected $roles = ['Director'];

    /**
     * @var array
     */
    protected $tags = ['corporation', 'outposts'];

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $known_outposts;

    /**
     * Outposts constructor.
     *
     * @param RefreshToken|null $token
     */
    public function __construct(RefreshToken $token = null)
    {

        $this->known_outposts = collect();

        parent::__construct($token);
    }

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {

        if (! $this->preflighted()) return;

        while (true) {

            $outposts = $this->retrieve([
                'corporation_id' => $this->getCorporationId(),
            ]);

            if ($outposts->isCachedLoad()) return;

            collect($outposts)->each(function ($outpost_id) {

                CorporationOutpost::firstOrNew([
                    'corporation_id' => $this->getCorporationId(),
                    'outpost_id'     => $outpost_id,
                ])->save();

                $this->known_outposts->push($outpost_id);

            });

            if (! $this->nextPage($outposts->pages))
                break;
        }

        CorporationOutpost::where('corporation_id', $this->getCorporationId())
            ->whereNotIn('outpost_id', $this->known_outposts->flatten()->all())
            ->delete();
    }
}
