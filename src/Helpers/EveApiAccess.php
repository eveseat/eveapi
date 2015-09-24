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

namespace Seat\Eveapi\Helpers;

use Pheal\Access\CanCheck;
use Pheal\Exceptions\AccessException;

/**
 * Class EveApiAccess
 * @package Seat\Eveapi\Helpers
 */
class EveApiAccess implements CanCheck
{

    /**
     * @var array
     */
    protected $access_bits = [];

    /**
     * Construct the Access Checking class by
     * passing an array of access_bit definitions
     *
     * @param $access_bits
     */
    public function __construct($access_bits)
    {

        $this->access_bits = $access_bits;
    }

    /**
     * @param string $scope
     * @param string $name
     * @param string $keyType
     * @param int    $accessMask
     *
     * @return bool
     * @throws \Pheal\Exceptions\AccessException
     */
    public function check($scope, $name, $keyType, $accessMask)
    {

        // AccountStatus is this odd one out that needs
        // an access_mask check. If the $name is that,
        // then flip the scope to char for checking
        if ($name == 'AccountStatus')
            $scope = 'char';

        // Check if we have the scope for this key defined
        // in our access_bits array. If we dont, we will
        // assume that it is not a type of call that has
        // an accessMask requirement (such as pub calls)
        if (!array_key_exists($scope, $this->access_bits))
            return true;

        // Get the bit definitions for this scope and change
        // the case of the call all to lower
        $scope_bits = $this->access_bits[$scope];
        $call_name = strtolower($name);

        if (!array_key_exists($call_name, $scope_bits))
            throw new AccessException(
                'Blocked request. Unknown call ' . $call_name .
                ' in scope ' . $scope);

        // Check if the accessMask is valid
        if ((int)$scope_bits[$call_name] & (int)$accessMask)
            return true;

        // As a last resort, block the call by throwing an
        // AccessException
        throw new AccessException(
            'Blocked request. An access mask of ' . $accessMask .
            ' is not enough to call ' . $scope . '/' . $call_name .
            ' which needs at least ' . $scope_bits[$call_name]);
    }
}
