<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2022 Leon Jacobs
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

namespace Seat\Eveapi\Jobs;

use Illuminate\Bus\Batchable;

/**
 * Class AbstractCharacterJob.
 *
 * @package Seat\Eveapi\Jobs
 */
abstract class AbstractCharacterJob extends EsiBase
{
    use Batchable;

    /**
     * @var int The character ID to which this job is related.
     */
    protected $character_id;

    /**
     * AbstractCharacterJob constructor.
     *
     * @param  int  $character_id
     */
    public function __construct(int $character_id)
    {
        $this->character_id = $character_id;
    }

    /**
     * Get the character ID to which this job is related.
     *
     * @return int
     */
    public function getCharacterId(): int
    {
        return $this->character_id;
    }

    /**
     * {@inheritdoc}
     */
    public function tags(): array
    {
        $tags = parent::tags();

        if (! in_array('character', $tags))
            $tags[] = 'character';

        if (! in_array($this->character_id, $tags))
            $tags[] = $this->character_id;

        return $tags;
    }

    public function handle()
    {
        if ($this->batchId && $this->batch()->cancelled())
            return;

        logger()->debug('Character job is processing...', [
            'name' => static::class,
            'character_id' => $this->character_id,
        ]);
    }
}
