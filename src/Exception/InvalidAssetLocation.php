<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to present Leon Jacobs
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
 * Class InvalidAssetLocation.
 *
 * @package Seat\Eveapi\Exception
 */
class InvalidAssetLocation extends Exception
{
    public function __construct(Throwable $previous = null)
    {
        $message = 'Invalid Asset Location requested. Check your logs to get more details.';
        $code = 400;

        parent::__construct($message, $code, $previous);
    }
}
