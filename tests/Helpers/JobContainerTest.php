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

namespace Seat\Eveapi\Test\Helpers;

use Seat\Eveapi\Helpers\JobContainer;

/**
 * Class JobContainerTest
 * @package Seat\Eveapi\Test\Helpers
 */
class JobContainerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var
     */
    protected $container;

    /**
     *
     */
    public function setUp()
    {

        $this->container = new JobContainer();
    }

    /**
     *
     */
    public function testDoesNotHaveKey()
    {

        $array = $this->container;
        $this->assertArrayNotHasKey('nope', $array);
    }

    /**
     *
     */
    public function testDoesHaveKey()
    {

        $array = $this->container;
        $array->new_key = 'test';
        $this->assertArrayHasKey('new_key', $array);
    }

    /**
     *
     */
    public function testSetsNewValue()
    {

        $array = $this->container;
        $array->key = 'test_value';

        $this->assertEquals('test_value', $array->key);
    }

    /**
     *
     */
    public function testJobContainerHasDefaultKeys()
    {

        $array = $this->container;

        $this->assertArrayHasKey('queue', $array);
        $this->assertArrayHasKey('scope', $array);
        $this->assertArrayHasKey('api', $array);
        $this->assertArrayHasKey('owner_id', $array);
    }
}
