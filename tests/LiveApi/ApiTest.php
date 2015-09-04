<?php
/*
The MIT License (MIT)

Copyright (c) 2015 Leon Jacobs
Copyright (c) 2015 eveseat

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

namespace Seat\Eveapi\Test\LiveApi;

use Seat\Eveapi\Helpers\XsdValidator;
use Seat\Eveapi\Traits\Core;

/**
 * Class EveTest
 * @package Seat\Eveapi\Test\LiveApi
 */
class ApiTest extends \PHPUnit_Framework_TestCase
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
    public function testCallListApiResponseIsValid()
    {

        // Call the EVE API for the source XML
        $this->client->apiScope->CallList();

        // Start the XSDValidator and load the XSD
        // and XML string from the API call
        $validator = new XsdValidator();
        $validator->setXSDFile(
            __DIR__ . '/../Support/Xsd/Api/CallList.xsd')
            ->setXML($this->client->xml);

        // Validate and assert
        $result = $validator->validate();

        $this->assertTrue($result);
    }
}
