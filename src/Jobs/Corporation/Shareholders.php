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
use Seat\Eveapi\Models\Corporation\CorporationShareholder;
use Seat\Eveapi\Models\RefreshToken;

class Shareholders extends EsiBase {

    protected $method = 'get';

    protected $endpoint = '/corporations/{corporation_id}/shareholders/';

    protected $version = 'v1';

    protected $page = 1;

    protected $known_shareholders;

    public function __construct(RefreshToken $token = null)
    {
        $this->known_shareholders = collect();

        parent::__construct($token);
    }

    public function handle()
    {

        while (true) {

            $shareholders = $this->retrieve([
                'corporation_id' => $this->getCorporationId(),
            ]);

            collect($shareholders)->each(function($shareholder){

                CorporationShareholder::firstOrNew([
                    'corporation_id' => $this->getCorporationId(),
                    'shareholder_type' => $shareholder->shareholder_type,
                    'shareholder_id' => $shareholder->shareholder_id,
                ])->fill([
                    'share_count' => $shareholder->share_count,
                ])->save();

            });

            $this->known_shareholders
                ->push(collect($shareholders)
                ->pluck(['shareholder_type', 'shareholder_id'])
                ->flatten()
                ->all());

            if (! $this->nextPage($shareholders->pages))
                break;

        }

    }

}
