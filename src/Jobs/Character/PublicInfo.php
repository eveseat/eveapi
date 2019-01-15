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

namespace Seat\Eveapi\Jobs\Character;

use Seat\Eveapi\Jobs\Character\Info as Info;
use Seat\Web\Models\User;

/**
 * Class PublicInfo.
 * @package Seat\Eveapi\Jobs\Character
 */
class PublicInfo extends Info
{
    /**
     * PublicInfo constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @throws \Exception
     */
    public function handle()
    {
        if (! $this->preflighted()) return;

        $character_ids = User::doesntHave('refresh_token')
                ->select('id')
                ->where('id', '<>', 1)
                ->get()
                ->pluck('id');

        $character_ids->each(function ($character_id) {

            $character_info = $this->getCharacterInfo($character_id);

            if ($character_info->isCachedLoad()) return;

            $this->saveCharacterInfo($character_id, $character_info);
        });
    }
}
