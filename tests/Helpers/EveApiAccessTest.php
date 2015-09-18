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

namespace Seat\Eveapi\Test\Helpers;

use Seat\Eveapi\Helpers\EveApiAccess;

/**
 * Class EveApiAccessTest
 * @package Seat\Eveapi\Test\Helpers
 */
class EveApiAccessTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var
     */
    protected $access_check;

    /**
     *
     */
    public function setUp()
    {

        $bit_db = ['char' => ['killmails' => 256]];
        $this->access_check = new EveApiAccess($bit_db);
    }

    /**
     *
     */
    public function testDoesNotHaveAccess()
    {

        $this->setExpectedException('Pheal\Exceptions\AccessException');
        $this->access_check->check('char', 'KillMails', 'Character', 1);
    }

    /**
     *
     */
    public function testDoesHaveAccess()
    {

        $check = $this->access_check->check('char', 'KillMails', 'Character', 268435455);
        $this->assertTrue($check);
    }

}
