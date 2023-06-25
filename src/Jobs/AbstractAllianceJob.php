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
 * Class AbstractAllianceJob.
 *
 * @package Seat\Eveapi\Jobs
 */
abstract class AbstractAllianceJob extends EsiBase
{
    use Batchable;

    /**
     * @var int The alliance ID to which the job is related.
     */
    protected $alliance_id;

    /**
     * AbstractAllianceJob constructor.
     *
     * @param  int  $alliance_id
     */
    public function __construct(int $alliance_id)
    {
        $this->alliance_id = $alliance_id;

        parent::__construct();
    }

    /**
     * Get the alliance ID to which this job is related.
     *
     * @return int
     */
    public function getAllianceId(): int
    {
        return $this->alliance_id;
    }

    /**
     * {@inheritdoc}
     */
    public function tags(): array
    {
        $tags = parent::tags();

        if (! in_array('alliance', $tags))
            $tags[] = 'alliance';

        if (! in_array($this->getAllianceId(), $tags))
            $tags[] = $this->getAllianceId();

        return $tags;
    }

    public function handle()
    {
        if ($this->batchId && $this->batch()->cancelled())
            return;

        logger()->debug(
            sprintf('[Jobs][%s] Alliance job is processing...', $this->job->getJobId()),
            [
                'fqcn' => static::class,
                'alliance_id' => $this->alliance_id,
            ]);
    }
}
