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

namespace Seat\Eveapi\Test\LiveApi;

use Seat\Eveapi\Helpers\XsdValidator;
use Seat\Eveapi\Traits\Core;

/**
 * Class MapTest
 * @package Seat\Eveapi\Test\LiveApi
 */
class MapTest extends \PHPUnit_Framework_TestCase
{

    use Core;

    /**
     * A Pheal instance
     * @var
     */
    protected $client;

    /**
     * Setup by getting a new Pheal Instance
     */
    public function setUp()
    {

        $this->client = $this->getPheal();
    }

    /**
     * @group LiveApi
     * @throws \Exception
     */
    public function testJumpsApiResponseIsValid()
    {

        // Call the EVE API for the source XML
        $this->client->mapScope->Jumps();

        // Start the XSDValidator and load the XSD
        // and XML string from the API call
        $validator = new XsdValidator();
        $validator->setXSDFile(
            __DIR__ . '/../Support/Xsd/Map/Jumps.xsd')
            ->setXML($this->client->xml);

        // Validate and assert
        $result = $validator->validate();

        $this->assertTrue($result);
    }

    /**
     * @group LiveApi
     * @throws \Exception
     */
    public function testKillsApiResponseIsValid()
    {

        // Call the EVE API for the source XML
        $this->client->mapScope->Kills();

        // Start the XSDValidator and load the XSD
        // and XML string from the API call
        $validator = new XsdValidator();
        $validator->setXSDFile(
            __DIR__ . '/../Support/Xsd/Map/Kills.xsd')
            ->setXML($this->client->xml);

        // Validate and assert
        $result = $validator->validate();

        $this->assertTrue($result);
    }

    /**
     * @group LiveApi
     * @throws \Exception
     */
    public function testSovereigntyApiResponseIsValid()
    {

        // Call the EVE API for the source XML
        $this->client->mapScope->Sovereignty();

        // Start the XSDValidator and load the XSD
        // and XML string from the API call
        $validator = new XsdValidator();
        $validator->setXSDFile(
            __DIR__ . '/../Support/Xsd/Map/Sovereignty.xsd')
            ->setXML($this->client->xml);

        // Validate and assert
        $result = $validator->validate();

        $this->assertTrue($result);
    }
}