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
     * @return array
     */
    public function providerValidScopes()
    {

        return [
            ['account'],
            ['api'],
            ['char'],
            ['corp'],
            ['eve'],
            ['map'],
            ['server']
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

    /**
     * @throws \Seat\Eveapi\Exception\InvalidScopeException
     */
    public function testScopeIsInvalid()
    {

        $this->setExpectedException('Seat\Eveapi\Exception\InvalidScopeException');
        $this->validateScope('not_valid');
    }

    /**
     * @param $scope
     *
     * @dataProvider providerValidScopes
     */
    public function testScopeIsValid($scope)
    {

        $scope = $this->validateScope($scope);
        $this->assertNull($scope);
    }
}
