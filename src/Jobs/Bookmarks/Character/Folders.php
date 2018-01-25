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
use Seat\Eveapi\Models\Bookmarks\CharacterBookmarkFolder;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class Folders
 * @package Seat\Eveapi\Jobs\Bookmarks\Characters
 */
class Folders extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/bookmarks/folders/';

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
    protected $tags = ['character', 'bookmarks', 'folders'];

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $known_folder_ids;

    /**
     * Folders constructor.
     *
     * @param \Seat\Eveapi\Models\RefreshToken|null $token
     */
    public function __construct(RefreshToken $token = null)
    {

        $this->known_folder_ids = collect();

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

        while (true) {

            $folders = $this->retrieve([
                'character_id' => $this->getCharacterId(),
            ]);

            collect($folders)->each(function ($folder) {

                CharacterBookmarkFolder::firstOrNew([
                    'character_id' => $this->getCharacterId(),
                    'folder_id'    => $folder->folder_id,
                ])->fill([
                    'name' => $folder->name,
                ])->save();
            });

            $this->known_folder_ids->push(collect($folders)
                ->pluck('folder_id')->flatten()->all());

            if (! $this->nextPage($folders->pages))
                break;
        }

        // Cleanup removed folders
        CharacterBookmarkFolder::where('character_id', $this->getCharacterId())
            ->whereNotIn('folder_id', $this->known_folder_ids->flatten()->all())
            ->delete();
    }
}
