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

namespace Seat\Eveapi\Jobs\Bookmarks\Corporation;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Bookmarks\CorporationBookmark;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Eveapi\Traits\Utils;


/**
 * Class Bookmarks
 * @package Seat\Eveapi\Jobs\Bookmarks\Corporation
 */
class Bookmarks extends EsiBase
{
    use Utils;

    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/bookmarks/';

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * @var string
     */
    protected $scope = 'esi-bookmarks.read_corporation_bookmarks.v1';

    /**
     * @var array
     */
    protected $tags = ['corporation', 'bookmarks'];

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $known_bookmarks;

    /**
     * Bookmarks constructor.
     *
     * @param \Seat\Eveapi\Models\RefreshToken|null $token
     */
    public function __construct(RefreshToken $token = null)
    {

        $this->known_bookmarks = collect();

        parent::__construct($token);
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {

        while (true) {

            $bookmarks = $this->retrieve([
                'corporation_id' => $this->getCorporationId(),
            ]);

            collect($bookmarks)->each(function ($bookmark) {

                $normalized_location = $this->find_nearest_celestial(
                    $bookmark->location_id,
                    $bookmark->position->x ?? 0.0,
                    $bookmark->position->y ?? 0.0,
                    $bookmark->position->z ?? 0.0);

                CorporationBookmark::firstOrNew([
                    'corporation_id' => $this->getCorporationId(),
                    'bookmark_id'    => $bookmark->bookmark_id,
                ])->fill([
                    'creator_id'  => $bookmark->creator_id,
                    'folder_id'   => $bookmark->folder_id ?? null,
                    'created'     => carbon($bookmark->created),
                    'label'       => $bookmark->label,
                    'notes'       => $bookmark->notes,
                    'location_id' => $bookmark->location_id,
                    'item_id'     => $bookmark->item->item_id ?? null,
                    'type_id'     => $bookmark->item->type_id ?? null,
                    'x'           => $bookmark->coordinates->x ?? null,
                    'y'           => $bookmark->coordinates->y ?? null,
                    'z'           => $bookmark->coordinates->z ?? null,
                    'map_id'      => $normalized_location['map_id'],
                    'map_name'    => $normalized_location['map_name'],
                ])->save();
            });

            $this->known_bookmarks->push(collect($bookmarks)
                ->pluck('bookmark_id')->flatten()->all());

            if (! $this->nextPage($bookmarks->pages))
                break;
        }

        CorporationBookmark::where('corporation_id', $this->getCorporationId())
            ->whereNotIn('bookmark_id', $this->known_bookmarks->flatten()->all())
            ->delete();
    }
}
