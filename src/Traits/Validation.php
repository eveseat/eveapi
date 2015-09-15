<?php
/*
This file is part of SeAT

Copyright (C) 2015  Leon Jacobs

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

namespace Seat\Eveapi\Traits;

use Seat\Eveapi\Exception\InvalidKeyPairException;
use Seat\Eveapi\Exception\InvalidScopeException;
use Seat\Eveapi\Exception\MissingKeyPairException;

/**
 * Class Validation
 * @package Seat\Eveapi\Traits
 */
trait Validation
{

    /**
     * @param $key
     * @param $vcode
     *
     * @throws \Seat\Eveapi\Exception\InvalidKeyPairException
     * @throws \Seat\Eveapi\Exception\MissingKeyPairException
     */
    public function validateKeyPair($key, $vcode)
    {

        // Check that the values are not null
        if (is_null($key) || is_null($vcode))
            throw new MissingKeyPairException;

        // Do some really simple validation to ensure that
        // the key pair at least looks sane.
        if (!is_numeric($key) || strlen($vcode) <> 64)
            throw new InvalidKeyPairException;

        return;
    }

    /**
     * @param $scope
     *
     * @throws \Seat\Eveapi\Exception\InvalidScopeException
     */
    public function validateScope($scope)
    {

        // Scopes that should be considered valid
        $valid_scopes = [
            'account', 'api', 'char', 'corp', 'eve', 'map', 'server'];

        if (!in_array($scope, $valid_scopes))
            throw new InvalidScopeException;

        return;
    }
}