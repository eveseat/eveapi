<?php

namespace Seat\Eveapi\Traits;

/**
 * Class ValidationTest
 * @package Seat\Eveapi\Traits
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