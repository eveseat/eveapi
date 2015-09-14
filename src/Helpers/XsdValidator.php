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

use DOMDocument;

/**
 * Class XsdValidator
 * @package Seat\Eveap\Helpers
 */
class XsdValidator
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
                ' Check the source XML on line ' . $error->line . ' col ' . $error->column;

            $errors_string .= PHP_EOL;
        }

        libxml_clear_errors();

        return $errors_string;
    }
}
