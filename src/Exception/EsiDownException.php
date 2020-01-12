<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018, 2019, 2020  Leon Jacobs
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

namespace Seat\Eveapi\Exception;

use Exception;
use Throwable;

/**
 * Class EsiDownException.
 *
 * @package Seat\Eveapi\Exception
 */
class EsiDownException extends Exception
{
    /**
     * EsiDownException constructor.
     *
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct($code = 0, Throwable $previous = null)
    {
        $message = 'ESI seems to be down. If you think it is an error, ensure your workers are up and ' .
                   'the command esi:update:status is scheduled.';

        parent::__construct($message, $code, $previous);
    }
}
