<?php
/*
This file is part of SeAT

Copyright (C) 2015, 2016  Leon Jacobs

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

namespace Seat\Eveapi\Test\Traits;

use Seat\Eveapi\Traits\Utils;

/**
 * Class UtilsTest
 * @package Seat\Eveapi\Test\Traits
 */
class UtilsTest extends \PHPUnit_Framework_TestCase
{

    use Utils;

    public function testTransactionHashIsOK()
    {

        $result = $this->hash_transaction(
            1234, '2015-08-31 08:02:26', 'seed-1', 'seed-2');
        $this->assertEquals('25a489c0444192252a9bf86b63e4b4d8', $result);
    }

}
