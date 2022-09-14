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

namespace Seat\Eveapi\Events;

use Illuminate\Foundation\Events\Dispatchable;

abstract class AbstractEsiJobEvent
{
    use Dispatchable;

    /**
     * @var string
     */
    public string $job_class;

    /**
     * @var string
     */
    public string $job_display_name;

    /**
     * @var string
     */
    public string $scope;

    /**
     * @var int
     */
    public int $entity_id;

    /**
     * @param  string  $job_class
     * @param  string  $job_display_name
     * @param  string  $scope
     * @param  int  $entity_id
     */
    public function __construct(string $job_class, string $job_display_name, string $scope, int $entity_id = 0)
    {
        $this->job_class = $job_class;
        $this->job_display_name = $job_display_name;
        $this->scope = $scope;
        $this->entity_id = $entity_id;
    }
}
