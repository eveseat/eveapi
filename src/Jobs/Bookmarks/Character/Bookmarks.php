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

namespace Seat\Eveapi\Jobs\Bookmarks\Character;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Bookmarks\CharacterBookmark;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Eveapi\Traits\Utils;

/**
 * Class Bookmarks.
 * @package Seat\Eveapi\Jobs\Bookmarks\Characters
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
    protected $endpoint = '/characters/{character_id}/bookmarks/';

    /**
     * @var string
     */
    protected $version = 'v2';

    /**
     * @var string
     */
    protected $scope = 'esi-bookmarks.read_character_bookmarks.v1';

    /**
     * @var array
     */
    protected $tags = ['character', 'bookmarks'];

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
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle()
    {

        if (! $this->preflighted()) return;

        while (true) {

            $bookmarks = $this->retrieve([
                'character_id' => $this->getCharacterId(),
            ]);

            if ($bookmarks->isCachedLoad()) return;

            collect($bookmarks)->chunk(1000)->each(function ($chunk) {

                $records = $chunk->map(function ($bookmark, $key) {

                    $normalized_location = $this->find_nearest_celestial(
                        $bookmark->location_id,
                        $bookmark->position->x ?? 0.0,
                        $bookmark->position->y ?? 0.0,
                        $bookmark->position->z ?? 0.0);

                    return [
                        'character_id' => $this->getCharacterId(),
                        'bookmark_id'  => $bookmark->bookmark_id,
                        'creator_id'   => $bookmark->creator_id,
                        'folder_id'    => $bookmark->folder_id ?? null,
                        'created'      => carbon($bookmark->created),
                        'label'        => $bookmark->label,
                        'notes'        => $bookmark->notes,
                        'location_id'  => $bookmark->location_id,
                        'item_id'      => $bookmark->item->item_id ?? null,
                        'type_id'      => $bookmark->item->type_id ?? null,
                        'x'            => $bookmark->coordinates->x ?? null,
                        'y'            => $bookmark->coordinates->y ?? null,
                        'z'            => $bookmark->coordinates->z ?? null,
                        'map_id'       => $normalized_location['map_id'],
                        'map_name'     => $normalized_location['map_name'],
                        'created_at'   => carbon(),
                        'updated_at'   => carbon(),
                    ];
                });

                CharacterBookmark::insertOnDuplicateKey($records->toArray(), [
                    'character_id',
                    'bookmark_id',
                    'creator_id',
                    'folder_id',
                    'created',
                    'label',
                    'notes',
                    'location_id',
                    'item_id',
                    'type_id',
                    'x',
                    'y',
                    'z',
                    'map_id',
                    'map_name',
                    'updated_at',
                ]);
            });

            $this->known_bookmarks->push(collect($bookmarks)
                ->pluck('bookmark_id')->flatten()->all());

            if (! $this->nextPage($bookmarks->pages))
                break;
        }

        CharacterBookmark::where('character_id', $this->getCharacterId())
            ->whereNotIn('bookmark_id', $this->known_bookmarks->flatten()->all())
            ->delete();
    }
}
