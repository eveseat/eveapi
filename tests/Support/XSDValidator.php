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

namespace Seat\Eveapi\Test\Support;

use DOMDocument;

/**
 * Class XSDValidator
 * @package Seat\Eveapi\Test\Support
 */
class XSDValidator
{

    /**
     * @var
     */
    protected $xml;

    /**
     * @var
     */
    protected $xsd_file;

    /**
     * @param $xml
     *
     * @return $this
     */
    public function setXML($xml)
    {

        $this->xml = $xml;

        return $this;
    }

    /**
     * @param $xsd_file
     *
     * @return $this
     * @throws \Exception
     */
    public function setXSDFile($xsd_file)
    {

        if (!is_file($xsd_file))
            throw new \Exception('XSD file ' . $xsd_file . ' not found.');

        $this->xsd_file = $xsd_file;

        return $this;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function validate()
    {

        libxml_use_internal_errors(true);

        if (!$this->xml)
            throw new \Exception('You must provide XML.');

        $xml = simplexml_load_string($this->xml);
        if (!$xml) {
            $errors = $this->getXMLErrorsString();
            throw new \Exception(
                'XML does not validate XSD file ' . $this->xsd_file . ' : ' . $errors);
        }

        // Validate the XML with an XSD
        $xml = new DOMDocument();
        $xml->loadXML($this->xml);

        if (!$this->xml)
            throw new \Exception(
                'You must provide a XSD file.');

        if (!$xml->schemaValidate($this->xsd_file)) {

            $errors = $this->getXMLErrorsString();
            throw new \Exception(
                'XML does not validate XSD file: ' . basename($this->xsd_file) .
                PHP_EOL . $errors);
        }

        return true;
    }

    /**
     * @return string
     */
    public function getXMLErrorsString()
    {

        $errors_string = '';
        $errors = libxml_get_errors();

        foreach ($errors as $key => $error) {

            $level = $error->level === LIBXML_ERR_WARNING ?
                'Warning' : $error->level === LIBXML_ERR_ERROR ? 'Error' : 'Fatal';

            $errors_string .= '[' . $level . '] ' . trim($error->message) .
                ' Check XML on line ' . $error->line . ' col ' . $error->column;

        }

        libxml_clear_errors();

        return $errors_string;
    }
}
