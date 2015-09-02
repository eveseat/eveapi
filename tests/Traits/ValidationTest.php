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

namespace Seat\Eveapi\Test\Traits;

use Seat\Eveapi\Traits\Validation;

/**
 * Class ValidationTest
 * @package Seat\Eveapi\Test\Traits
 */
class ValidationTest extends \PHPUnit_Framework_TestCase
{

    use Validation;

    /**
     * @return array
     */
    public function providerValidApiKeyPairs()
    {

        return [
            ['1234', 'wSkGl9VHS27EN2MK7rnfEWVabiJ7XEfM7929omHD1Epe86QATvOOjAbkMmLNkhEU'],
            ['98765', 'HJrR5yPuOnAzAurScwXw9JECW0TOlZURd9WOTJnwDI0CTrnSErCeoN6zKGkYSHfa']
        ];
    }

    /**
     * @return array
     */
    public function providerInvalidApiKeyPairs()
    {

        return [
            ['abcde', 'wSkGl9VHS27EN2MK7rnfEWVabiJ7XEfM7929omHD1Epe86QATvOOjAbkMmLNkhEU'],
            ['98765', 'HJrR5yPuOnAzAurScwXw9JECW0TO'],
        ];
    }

    /**
     * @return array
     */
    public function providerEmptyApiKeyPairs()
    {

        return [
            [null, 'HJrR5yPuOnAzAurScwXw9JECW0TOlZURd9WOTJnwDI0CTrnSErCeoN6zKGkYSHfa'],
            ['12345', null]
        ];
    }

    /**
     * @param string $testKey   The API Key
     * @param string $testVcode The vCode
     *
     * @dataProvider providerValidApiKeyPairs
     */
    public function testKeyPairIsValid($testKey, $testVcode)
    {

        $check = $this->validateKeyPair($testKey, $testVcode);
        $this->assertNull($check);
    }

    /**
     * @param string $testKey   The API Key
     * @param string $testVcode The vCode
     *
     * @dataProvider providerInvalidApiKeyPairs
     */
    public function testKeyPairIsInvalid($testKey, $testVcode)
    {

        $this->setExpectedException('Seat\Eveapi\Exception\InvalidKeyPairException');
        $this->validateKeyPair($testKey, $testVcode);
    }

    /**
     * @param string $testKey   The API Key
     * @param string $testVcode The vCode
     *
     * @dataProvider providerEmptyApiKeyPairs
     */
    public function testKeyPairIsEmpty($testKey, $testVcode)
    {

        $this->setExpectedException('Seat\Eveapi\Exception\MissingKeyPairException');
        $this->validateKeyPair($testKey, $testVcode);
    }
}